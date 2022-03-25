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
 * Controls the running of metrics within a single task. Derive from scheduled_task to enable running from
 * the moodle task system, but it is  intendend to be run separately.
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
        return get_string('task_runner', 'tool_cloudmetrics');
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
     * Determine which level of frequency is desired for the metrics.
     *
     * @param $timediff
     * @return int
     */
    public function get_frequency_cutoff($timediff): int {
        if ($timediff % self::ONEWEEK == 0) {
            return metric\manager::FREQ_WEEK;
        } else if ($timediff % self::ONEDAY == 0) {
            return metric\manager::FREQ_DAY;
        } else if ($timediff % self::TWELVEHOURS == 0) {
            return metric\manager::FREQ_12HOUR;
        } else if ($timediff % self::THREEHOURS == 0) {
            return metric\manager::FREQ_3HOUR;
        } else if ($timediff % self::ONEHOUR == 0) {
            return metric\manager::FREQ_HOUR;
        } else if ($timediff % self::THIRTYMINUTES == 0) {
            return metric\manager::FREQ_30MIN;
        } else if ($timediff % self::FIFTEENMINUTES == 0) {
            return metric\manager::FREQ_15MIN;
        } else if ($timediff % self::FIVEMINUTES == 0) {
            return metric\manager::FREQ_5MIN;
        } else {
            return metric\manager::FREQ_MIN;
        }
    }

    /**
     * Perform the task
     *
     * @throws \Exception
     */
    public function execute() {
        // This algorithm is performance important. Any opportunity to optimize should be welcome.

        $tz = \core_date::get_server_timezone_object();
        $time = $this->time ?? time();
        $reftime = (new \DateTime("midnight last Sunday", $tz))->getTimestamp();
        $timediff = (int) round(($time - $reftime) / 60); // Nearest minute since ref time.

        $cutoff = $this->get_frequency_cutoff($timediff);

        $ismonth = 0;
        if ($cutoff >= metric\manager::FREQ_DAY) {
            $reftime = (new \DateTime("midnight this month", $tz))->getTimestamp();
            // Separate check to see if it is start of a new month, +/- 30sec.
            if (abs($time - $reftime) < 30) {
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
