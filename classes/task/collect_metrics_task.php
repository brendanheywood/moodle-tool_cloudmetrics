<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace tool_cloudmetrics\task;

use tool_cloudmetrics\metric;
use tool_cloudmetrics\collector;

/**
 * Controls the running of metrics within a single task.
 *
 * Metrics are measured at specific time determined by their frequency. For example, a metric set to FREQ_30MIN
 * is supposed to be measured every thirty minutes.
 *
 * The metrics are required to measured in sync with each other. For example, all FREQ_30MIN are required to
 * be measured at the same time, and those of FREQ_15MIN will also be measured at this time. And so on.
 *
 * This is done by taking a suitable reference time, and measuring the number of minutes that have passed since
 * then, and determining if a whole number of intervals have passed since then.
 *
 * For example if 75 minutes have passed since 'midnight last Sunday', then FREQ_15MIN metrics will be measured,
 * but FREQ_30MIN metrics will not.
 *
 * @package   tool_cloudmetrics
 * @author    Jason den Dulk <jasondendulk@catalyst-au.net>
 * @copyright 2022, Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class collect_metrics_task extends \core\task\scheduled_task {

    const FIVEMINUTES = 5;
    const FIFTEENMINUTES = 15;
    const THIRTYMINUTES = 30;
    const ONEHOUR = 60;
    const THREEHOURS = 180;
    const TWELVEHOURS = 720;
    const ONEDAY = 1440;
    const ONEWEEK = 10080;

    private $time = null;

    /**
     * Display name for the task name.
     *
     * @return \lang_string|string
     * @throws \coding_exception
     */
    public function get_name() {
        return get_string('collect_metrics_task', 'tool_cloudmetrics');
    }

    /**
     * Set a specific time for the use. Defaults to now.
     *
     * @param int $time
     */
    public function set_time(int $time) {
        $this->time = $time;
    }

    /**
     * Will send the metrics to the collectors.
     *
     * @param array $items
     */
    public function send_metrics(array $items) {
        collector\manager::send_metrics($items);
    }

    /**
     * Determine the highest level of frequency to be used.
     *
     * Returns the highest frequency for which a whole number of time intervals fits in the value given.
     *
     * Each interval (except monthly) is a whole number of the next smallest interval.
     * So if (for example), it is determined that all 3 hour metrics are to be measured, all
     * metrics for smaller intervals will also be measured.
     *
     * @param int $timediff The number of minutes since the reference time.
     * @return int
     */
    public function get_frequency_cutoff(int $timediff): int {
        // We test until we don't get a clean division.
        // After that, we know that we wont be measuring any longer interval.
        if ($timediff % self::FIVEMINUTES != 0) {
            return metric\manager::FREQ_MIN;
        } else if ($timediff % self::FIFTEENMINUTES != 0) {
            return metric\manager::FREQ_5MIN;
        } else if ($timediff % self::THIRTYMINUTES != 0) {
            return metric\manager::FREQ_15MIN;
        } else if ($timediff % self::ONEHOUR != 0) {
            return metric\manager::FREQ_30MIN;
        } else if ($timediff % self::THREEHOURS != 0) {
            return metric\manager::FREQ_HOUR;
        } else if ($timediff % self::TWELVEHOURS != 0) {
            return metric\manager::FREQ_3HOUR;
        } else if ($timediff % self::ONEDAY != 0) {
            return metric\manager::FREQ_12HOUR;
        } else if ($timediff % self::ONEWEEK != 0) {
            return metric\manager::FREQ_DAY;
        } else {
            return metric\manager::FREQ_WEEK;
        }
    }

    /**
     * Perform the task
     *
     * @throws \Exception
     */
    public function execute() {
        // This algorithm is performance important. Any opportunity to optimize should be welcome.

        // Use the server's timezone for determining times from strings.
        $tz = \core_date::get_server_timezone_object();

        // Get the time reference. The time string is in the server's timezone.
        // It is then converted to a timestamp, which is UTC.
        $reftime = (new \DateTime('midnight last Sunday', $tz))->getTimestamp();

        // We don't need to use $tz here because timestamps are always UTC.
        $time = $this->time ?? time();

        // Get the number of minutes (rounded) that have passed since the ref time.
        $timediff = (int) round(($time - $reftime) / MINSECS);

        // Get the highest metric frequency to be record.
        $cutoff = $this->get_frequency_cutoff($timediff);

        // We need to determine the monthly metrics separately, since a month is not a whole number of weeks.
        $halfmin = MINSECS / 2;
        $ismonth = 0;
        if ($cutoff >= metric\manager::FREQ_DAY) {
            $reftime = (new \DateTime('midnight this month -'. $halfmin .' seconds', $tz))->getTimestamp();
            // Time difference should be zero if we are indeed at monthly boundary, but we allow for up to +/-30 seconds.
            if (($time - $reftime) < MINSECS) {
                $ismonth = metric\manager::FREQ_MONTH;
            }
        }

        $metrics = metric\manager::get_metrics(true);
        $items = [];
        foreach ($metrics as $metric) {
            if ($metric->get_frequency() <= $cutoff || $metric->get_frequency() == $ismonth) {
                $items[] = $metric->get_metric_item();
            }
        }

        // Performance important part is over, we can relax a little.
        $this->send_metrics($items);
    }
}
