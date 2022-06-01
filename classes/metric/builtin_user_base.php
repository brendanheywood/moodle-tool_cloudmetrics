<?php
// This file is part of Moodle - https://moodle.org/
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

namespace tool_cloudmetrics\metric;

/**
 * Common base for the builtin user metrics.
 *
 * @package   tool_cloudmetrics
 * @author    Jason den Dulk <jasondendulk@catalyst-au.net>
 * @copyright 2022, Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class builtin_user_base extends builtin_base {

    /** @var string The DB field the metric accesses. */
    protected $dbfield = '';

    /**
     * The metric type.
     *
     * @return int
     */
    public function get_type(): int {
        return manager::TYPE_GAUGE;
    }

    /**
     * True if this metric is capable of generating metric items for past times.
     *
     * @return bool
     */
    public function can_generate_past_metric_items(): bool {
        return true;
    }

    /**
     * Generates the metric items from the source data.
     *
     * Starting from $finishtime, this will generate an item for each frequency period (as defined by get_frequency()),
     * going backwards until $startime is reached.
     *
     * If the metric type is unable to generate items from past data, the parameters will be ignored, and a
     * single item from the immediate time will be generated.
     *
     * @param int $starttime
     * @param int $finishtime
     * @return array An array of metric items, in forward chronological order.
     */
    public function generate_metric_items(int $starttime, int $finishtime): array {
        global $DB;

        $window = $this->get_time_window();
        $name = $this->get_name();
        $freq = $this->get_frequency();

        $items = [];

        $selectclause = $this->dbfield . ' > ? and ' . $this->dbfield . ' <= ?';

        // We go backwards because we want to align with finishtime.
        $time = $finishtime;
        do { // In the special case where $starttime = $finishtime, we allow one iteration.
            $users = $DB->count_records_select('user', $selectclause, [$time - $window, $time]);
            $items[] = new metric_item($name, $time, $users, $this);
            $time = \tool_cloudmetrics\lib::get_previous_time($time, $freq);
        } while ($time > $starttime);
        return array_reverse($items);
    }

    /**
     * Generate a single metric item from source data using the immediate time.
     *
     * @return metric_item
     */
    public function generate_metric_item(): metric_item {
        $ts = time();
        return $this->generate_metric_items($ts, $ts)[0];
    }
}
