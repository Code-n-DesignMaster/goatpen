<?php
namespace GoatPen\Observers;

use GoatPen\Utilities\ModelDiff;
use GoatPen\{Metric, Revision, Stat};

class StatObserver
{
    public function saving(Stat $stat)
    {
        $metrics = $stat->metrics ?: [];

        if (isset($metrics[Metric::IMPRESSIONS_ID])) {
            $metrics[Metric::COST_PER_IMPRESSION_ID] = round($stat->total_cost / ($metrics[Metric::IMPRESSIONS_ID] / 1000), 2);
        }

        if (isset($metrics[Metric::TOTAL_ENGAGEMENTS_ID])) {
            $metrics[Metric::COST_PER_ENGAGEMENT_ID] = round($stat->total_cost / $metrics[Metric::TOTAL_ENGAGEMENTS_ID], 2);
        }

        if (isset($metrics[Metric::VIDEO_VIEWS_ID])) {
            $metrics[Metric::COST_PER_VIEW_ID] = round($stat->total_cost / $metrics[Metric::VIDEO_VIEWS_ID], 2);
        }

        if (isset($metrics[Metric::LINK_CLICKS_ID])) {
            $metrics[Metric::COST_PER_CLICK_ID] = round($stat->total_cost / $metrics[Metric::LINK_CLICKS_ID], 2);
        }

        if (isset($metrics[Metric::DEPOSITORS_ID])) {
            $metrics[Metric::COST_PER_DEPOSITOR_ID] = round($stat->total_cost / $metrics[Metric::DEPOSITORS_ID], 2);
        }

        if (isset($metrics[Metric::REGISTRATIONS_ID])) {
            $metrics[Metric::COST_PER_REGISTRATION_ID] = round($stat->total_cost / $metrics[Metric::REGISTRATIONS_ID], 2);
        }

        if (isset($metrics[Metric::INSTALLS_ID])) {
            $metrics[Metric::COST_PER_INSTALL_ID] = round($stat->total_cost / $metrics[Metric::INSTALLS_ID], 2);
        }

        if (isset($metrics[Metric::TOTAL_ENGAGEMENTS_ID], $metrics[Metric::CUMULATIVE_FOLLOWING_ID])) {
            $metrics[Metric::PROFILE_ENGAGEMENT_RATE_ID] = round($metrics[Metric::TOTAL_ENGAGEMENTS_ID] / $metrics[Metric::CUMULATIVE_FOLLOWING_ID] * 100, 2);
        }

        if (isset($metrics[Metric::TOTAL_ENGAGEMENTS_ID], $metrics[Metric::IMPRESSIONS_ID])) {
            $metrics[Metric::POST_ENGAGEMENT_RATE_ID] = round($metrics[Metric::TOTAL_ENGAGEMENTS_ID] / $metrics[Metric::IMPRESSIONS_ID] * 100, 2);
        }

        if (isset($metrics[Metric::VIDEO_VIEWS_ID])) {
            if (isset($metrics[Metric::CUMULATIVE_FOLLOWING_ID])) {
                $metrics[Metric::VIEWTHROUGH_RATE_ID] = round($metrics[Metric::VIDEO_VIEWS_ID] / $metrics[Metric::CUMULATIVE_FOLLOWING_ID] * 100, 2);
            } elseif (isset($metrics[Metric::CUMULATIVE_SUBSCRIBERS_ID])) {
                $metrics[Metric::VIEWTHROUGH_RATE_ID] = round($metrics[Metric::VIDEO_VIEWS_ID] / $metrics[Metric::CUMULATIVE_SUBSCRIBERS_ID] * 100, 2);
            }
        }

        foreach ($metrics as $id => $value) {
            $metrics[$id] = (string) $value;
        }

        $stat->metrics = array_filter($metrics);
    }

    public function created(Stat $stat)
    {
        Revision::log($stat, 'created_at');
    }

    public function updated(Stat $stat)
    {
        foreach (ModelDiff::asArray($stat) as $key => $changes) {
            Revision::log($stat, $key, $changes['old'], $changes['new']);
        }
    }

    public function deleted(Stat $stat)
    {
        Revision::log($stat, 'deleted_at');
    }
}
