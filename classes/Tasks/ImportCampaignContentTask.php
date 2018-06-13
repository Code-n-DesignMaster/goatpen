<?php
namespace GoatPen\Tasks;

use Carbon\Carbon;
use GoatPen\Post;

class ImportCampaignContentTask extends ImportCampaignBaseTask
{
    public function processRecord(array $record)
    {
        $record = array_map('trim', $record);

        $channel = $this->findOrCreateChannel(
            preg_replace('/^https?:\/\//', '', $record[1]),
            $record[0],
            $this->findPlatform($record[2]),
            $this->findUser($record[3])
        );

        $metrics = [];

        foreach (array_slice($record, static::METRICS_START_KEY, null, true) as $key => $value) {
            if (isset($this->metrics[$key])) {
                $metrics[$this->metrics[$key]] = filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            }
        }

        $post          = new Post;
        $post->posted  = (! empty($record[6]) ? Carbon::parse($record[6])->toDateTimeString() : null);
        $post->url     = preg_replace('/^https?:\/\//', '', $record[4]);
        $post->link    = preg_replace('/^https?:\/\//', '', $record[5]);
        $post->metrics = array_filter($metrics);
        $post->campaign()->associate($this->campaign);
        $post->channel()->associate($channel);
        $post->save();

        $this->imported++;
    }
}
