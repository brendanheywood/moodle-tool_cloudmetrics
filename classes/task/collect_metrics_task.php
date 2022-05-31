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
     * Perform the task
     *
     * @throws \Exception
     */
    public function execute() {
        // This algorithm is performance important. Any opportunity to optimize should be welcome.

        $tz = \core_date::get_server_timezone_object();

        $nowtimestamp = !is_null($this->time) ? $this->time : time();

        $metrictypes = metric\manager::get_metrics(true);
        $items = [];
        foreach ($metrictypes as $metrictype) {
            if (!$metrictype->is_ready()) {
                continue;
            }

            $freq = $metrictype->get_frequency();

            // When is the next generation due?
            $lasttimestamp = \tool_cloudmetrics\lib::get_last_whole_tick($metrictype->get_last_generate_time(), $freq);
            if ($lasttimestamp === 0) {
                $duetimestamp = $nowtimestamp; // We generate the metric item regardless.
            } else {
                $duetimestamp = \tool_cloudmetrics\lib::get_next_time($lasttimestamp, $freq);
            }

            if ($duetimestamp <= $nowtimestamp) {
                $items = array_merge($items, $metrictype->generate_metric_items($lasttimestamp, $duetimestamp));
                $metrictype->set_last_generate_time($duetimestamp);
            }
        }

        // Performance important part is over, we can relax a little.
        $this->send_metrics($items);
    }
}
