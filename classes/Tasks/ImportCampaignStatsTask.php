<?php
namespace GoatPen\Tasks;

use GoatPen\Stat;

class ImportCampaignStatsTask extends ImportCampaignBaseTask
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

        $stat              = new Stat;
        $stat->link        = preg_replace('/^https?:\/\//', '', $record[4]);
        $stat->total_posts = filter_var($record[5], FILTER_SANITIZE_NUMBER_INT);
        $stat->total_cost  = filter_var($record[6], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $stat->metrics     = array_filter($metrics);
        $stat->campaign()->associate($this->campaign);
        $stat->channel()->associate($channel);
        $stat->save();

        $this->imported++;
    }
}
