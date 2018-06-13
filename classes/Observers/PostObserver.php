<?php
namespace GoatPen\Observers;

use GoatPen\Utilities\ModelDiff;
use GoatPen\{Metric, Post, Revision};

class PostObserver
{
    public function saving(Post $post)
    {
        if ($post->isDirty('url')) {
            $post->error = null;
        }

        $metrics = $post->metrics ?: [];
        $channel = $post->channel;

        if (isset($metrics[Metric::PRICE_PAID_ID], $metrics[Metric::LINK_CLICKS_ID])) {
            $metrics[Metric::COST_PER_CLICK_ID] = round($metrics[Metric::PRICE_PAID_ID] / $metrics[Metric::LINK_CLICKS_ID], 2);
        }

        if (isset($metrics[Metric::PRICE_PAID_ID], $metrics[Metric::TOTAL_ENGAGEMENTS_ID])) {
            $metrics[Metric::COST_PER_ENGAGEMENT_ID] = round($metrics[Metric::PRICE_PAID_ID] / $metrics[Metric::TOTAL_ENGAGEMENTS_ID], 2);
        }

        if (isset($metrics[Metric::PRICE_PAID_ID], $metrics[Metric::IMPRESSIONS_ID])) {
            $metrics[Metric::COST_PER_IMPRESSION_ID] = round($metrics[Metric::PRICE_PAID_ID] / ($metrics[Metric::IMPRESSIONS_ID] / 1000), 2);
        }

        if (isset($metrics[Metric::PRICE_PAID_ID], $metrics[Metric::VIDEO_VIEWS_ID])) {
            $metrics[Metric::COST_PER_VIEW_ID] = round($metrics[Metric::PRICE_PAID_ID] / $metrics[Metric::VIDEO_VIEWS_ID], 2);
        }

        if (isset($metrics[Metric::TOTAL_ENGAGEMENTS_ID], $channel->metrics[Metric::FOLLOWERS_ID])) {
            $metrics[Metric::PROFILE_ENGAGEMENT_RATE_ID] = round($metrics[Metric::TOTAL_ENGAGEMENTS_ID] / $channel->metrics[Metric::FOLLOWERS_ID] * 100, 2);
        }

        if (isset($metrics[Metric::TOTAL_ENGAGEMENTS_ID], $metrics[Metric::IMPRESSIONS_ID])) {
            $metrics[Metric::POST_ENGAGEMENT_RATE_ID] = round($metrics[Metric::TOTAL_ENGAGEMENTS_ID] / $metrics[Metric::IMPRESSIONS_ID] * 100, 2);
        }

        if (isset($metrics[Metric::VIDEO_VIEWS_ID])) {
            if (isset($channel->metrics[Metric::FOLLOWERS_ID])) {
                $metrics[Metric::VIEWTHROUGH_RATE_ID] = round($metrics[Metric::VIDEO_VIEWS_ID] / $channel->metrics[Metric::FOLLOWERS_ID] * 100, 2);
            } elseif (isset($channel->metrics[Metric::SUBSCRIBERS_ID])) {
                $metrics[Metric::VIEWTHROUGH_RATE_ID] = round($metrics[Metric::VIDEO_VIEWS_ID] / $channel->metrics[Metric::SUBSCRIBERS_ID] * 100, 2);
            }
        }

        foreach ($metrics as $id => $value) {
            $metrics[$id] = (string) $value;
        }

        $post->metrics = array_filter($metrics);
    }

    public function created(Post $post)
    {
        Revision::log($post, 'created_at');
    }

    public function updated(Post $post)
    {
        foreach (ModelDiff::asArray($post) as $key => $changes) {
            Revision::log($post, $key, $changes['old'], $changes['new']);
        }
    }

    public function deleted(Post $post)
    {
        Revision::log($post, 'deleted_at');
    }
}
