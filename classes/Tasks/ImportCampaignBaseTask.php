<?php
namespace GoatPen\Tasks;

use Exception;
use GoatPen\{Campaign, Channel, Influencer, Metric, Platform, Queue, Task, User};
use League\Csv\{Reader, ResultSet, Statement};

class ImportCampaignBaseTask
{
    const METRICS_START_KEY = 7;

    protected $campaign;
    protected $queue;
    protected $reader;
    protected $metrics;
    protected $records;
    protected $imported = 0;
    protected $skipped = 0;

    public function __construct(string $source, int $campaignId = null, Queue $queue)
    {
        if (! ($this->campaign = Campaign::find($campaignId))) {
            throw new Exception('Error loading campaign: ' . $campaignId);
        }

        $this->queue   = $queue;
        $this->reader  = Reader::createFromPath($source, 'r');
        $this->metrics = $this->validateMetrics(array_slice($this->reader->fetchOne(), static::METRICS_START_KEY, null, true));
        $this->records = (new Statement)->offset(1)->process($this->reader);
    }

    public function getRecords(): ResultSet
    {
        return $this->records;
    }

    public function getCampaign(): Campaign
    {
        return $this->campaign;
    }

    public function getImported(): int
    {
        return $this->imported;
    }

    public function getSkipped(): int
    {
        return $this->skipped;
    }

    private function validateMetrics(array $metrics): array
    {
        $valid = Metric::query()
            ->where('scope', '=', 'Campaign');

        if ($this->queue->task->getKey() !== Task::IMPORT_CAMPAIGN_STATS) {
            $valid->where('stats', '=', false);
        }

        $valid = $valid->get()->pluck('name', 'id')->toArray();

        $unknown = [];
        $ids     = [];

        foreach ($metrics as $key => $name) {
            if (($id = array_search(strtolower($name), array_map('strtolower', $valid))) !== false) {
                $ids[$key] = $id;
            } else {
                $unknown[] = $name;
            }
        }

        if (! empty($unknown)) {
            throw new Exception(sprintf('Unknown metrics: %s', implode(', ', $unknown)));
        }

        return $ids;
    }

    protected function findPlatform(string $name): Platform
    {
        $platforms = Platform::query()->where('name', '=', $name);

        if ($platforms->count() === 0) {
            $this->skipped++;
            throw new Exception(sprintf('Platform not found, skipping row: %s', $name));
        }

        return $platforms->first();
    }

    protected function findUser(string $name): User
    {
        $users = User::query()->where('name', '=', $name);

        if ($users->count() === 0) {
            $this->skipped++;
            throw new Exception(sprintf('User not found, skipping row: %s', $name));
        }

        return $users->first();
    }

    protected function findOrCreateChannel(string $url, string $influencerName, Platform $platform, User $user): Channel
    {
        $channels = Channel::query()
            ->where('url', '=', $url)
            ->where('platform_id', '=', $platform->id);

        if ($channels->count() === 0) {
            $influencer       = new Influencer;
            $influencer->name = $influencerName;
            $influencer->user()->associate($user);
            $influencer->save();

            $channel          = new Channel;
            $channel->name    = $influencerName . ' ' . $platform->name;
            $channel->url     = $url;
            $channel->metrics = [];
            $channel->influencer()->associate($influencer);
            $channel->platform()->associate($platform);
            $channel->save();
        } else {
            $channel = $channels->first();
        }

        return $channel;
    }
}
