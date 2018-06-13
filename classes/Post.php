<?php
namespace GoatPen;

use GoatPen\Observers\PostObserver;
use GoatPen\Services\{FacebookService, InstagramService, TwitchService, TwitterService, YouTubeService};
use Illuminate\Database\Eloquent\{Builder, Model};

class Post extends Model
{
    public $casts = [
        'campaign_id'       => 'integer',
        'channel_id'        => 'integer',
        'posted'            => 'datetime',
        'url'               => 'string',
        'link'              => 'string',
        'metrics'           => 'json',
        'error'             => 'string',
        'cached_metrics_at' => 'datetime',
    ];

    public $revisionable = [
        'campaign_id',
        'channel_id',
        'url',
        'link',
        'metrics',
    ];

    public static function boot()
    {
        parent::boot();
        self::observe(new PostObserver);
    }

    public function getUrlAttribute($value): string
    {
        return (strlen($value) > 0 ? 'http://' . $value : '');
    }

    public function setUrlAttribute($value)
    {
        $this->attributes['url'] = preg_replace('#^https?://#', '', $value);
    }

    public function getLinkAttribute($value): string
    {
        return (strlen($value) > 0 ? 'http://' . $value : '');
    }

    public function setLinkAttribute($value)
    {
        $this->attributes['link'] = preg_replace('#^https?://#', '', $value);
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    public function revisions()
    {
        return $this->hasMany(Revision::class, 'revisionable_id', 'id')
            ->where('revisions.revisionable_type', '=', $this->getMorphClass());
    }

    public function scopeWithMetric(Builder $query, Metric $metric): Builder
    {
        return $query->where('posts.metrics', 'LIKE', '%"' . $metric->getKey() . '":');
    }

    public function populateMetrics()
    {
        switch ($this->channel->platform->getKey()) {
            case Platform::FACEBOOK_ID:
                $this->populateMetricsFromFacebook();
                break;
            case Platform::INSTAGRAM_ID:
            case Platform::INSTAGRAM_STORIES_ID:
                $this->populateMetricsFromInstagram();
                break;
            case Platform::TWITCH_ID:
                $this->populateMetricsFromTwitch();
                break;
            case Platform::TWITTER_ID:
                $this->populateMetricsFromTwitter();
                break;
            case Platform::YOUTUBE_ID:
                $this->populateMetricsFromYouTube();
                break;
        }
    }

    private function populateMetricsFromFacebook()
    {
        if (! $this->identifier) {
            if (! $this->channel->identifier) {
                return;
            }

            preg_match('#(/posts/|/videos/|/photos/[a-z\d.]+/|permalink.php\?story_fbid=)(\d+)#i', $this->url, $matches);

            if (! isset($matches[2])) {
                return;
            }

            $this->identifier = $this->channel->identifier . '_' . $matches[2];
        }

        $data = (new FacebookService)->getPostFromId($this->identifier);

        if (! $data) {
            return;
        }

        $metrics = ($this->metrics ?: []);
        $metrics[Metric::POST_REACTIONS_ID] = ($data['reactions']['summary']['total_count'] ?? null);
        $metrics[Metric::POST_COMMENTS_ID]  = ($data['comments']['summary']['total_count'] ?? null);
        $metrics[Metric::SHARES_ID]         = ($data['shares']['count'] ?? null);

        $this->identifier = $data['id'];
        $this->metrics    = array_filter($metrics);
    }

    private function populateMetricsFromInstagram()
    {
        if (! $this->identifier) {
            preg_match('#instagram.com/p/([a-z\d_-]+)#i', $this->url, $matches);

            if (! isset($matches[1])) {
                return;
            }

            $this->identifier = $matches[1];
        }

        $data = (new InstagramService)->getPostFromId($this->identifier);

        if (! $data) {
            return;
        }

        $metrics = ($this->metrics ?: []);
        $metrics[Metric::POST_LIKES_ID]    = $data->graphql->shortcode_media->edge_media_preview_like->count;
        $metrics[Metric::POST_COMMENTS_ID] = $data->graphql->shortcode_media->edge_media_to_comment->count;

        $this->identifier = $data->graphql->shortcode_media->shortcode;
        $this->metrics    = array_filter($metrics);
    }

    private function populateMetricsFromTwitch()
    {
        if (! $this->identifier) {
            preg_match('#twitch.tv/videos/(\d+)#i', $this->url, $matches);

            if (! isset($matches[1])) {
                return;
            }

            $this->identifier = $matches[1];
        }

        $data = (new TwitchService)->getVideoFromId($this->identifier);

        if (! $data) {
            return;
        }

        $metrics = ($this->metrics ?: []);
        $metrics[Metric::VIDEO_VIEWS_ID] = $data->data[0]->view_count;

        $this->identifier = $data->data[0]->id;
        $this->metrics    = array_filter($metrics);
    }

    private function populateMetricsFromTwitter()
    {
        if (! $this->identifier) {
            preg_match('#/status/(\d+)#i', $this->url, $matches);

            if (! isset($matches[1])) {
                return;
            }

            $this->identifier = $matches[1];
        }

        $data = (new TwitterService)->getPostFromId($this->identifier);

        if (! $data || empty($data)) {
            return;
        }

        if (! empty($data->errors)) {
            $this->error = sprintf('Error %d: %s', $data->errors[0]->code, $data->errors[0]->message);
            return;
        }

        $metrics = ($this->metrics ?: []);
        $metrics[Metric::RETWEETS_ID]   = $data[0]->retweet_count;
        $metrics[Metric::POST_LIKES_ID] = $data[0]->favorite_count;

        $this->identifier = $data[0]->id_str;
        $this->metrics    = array_filter($metrics);
    }

    private function populateMetricsFromYouTube()
    {
        if (! $this->identifier) {
            preg_match('#(youtube.com/watch\?v=|youtu.be/)([a-z\d_-]+)#i', $this->url, $matches);

            if (! isset($matches[2])) {
                return;
            }

            $this->identifier = $matches[2];
        }

        $data = (new YouTubeService)->getVideoFromId($this->identifier);

        if (! $data || empty($data->getItems())) {
            return;
        }

        $stats = $data->getItems()[0]->getStatistics();

        $metrics = ($this->metrics ?: []);
        $metrics[Metric::VIDEO_VIEWS_ID]      = (int) $stats->getViewCount();
        $metrics[Metric::POST_COMMENTS_ID] = (int) $stats->getCommentCount();
        $metrics[Metric::POST_LIKES_ID]    = (int) $stats->getLikeCount();

        $this->identifier = $data->getItems()[0]->getId();
        $this->metrics    = array_filter($metrics);
    }
}
