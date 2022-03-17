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

namespace metric_activeusers;

use tool_cloudmetrics\metric_item;

use block_online_users\fetcher;

/**
 * Metric class for active users.
 *
 * @package    metric_activeusers
 * @author     Jason den Dulk <jasondendulk@catalyst-au.net>
 * @copyright  2022, Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class metric extends \tool_cloudmetrics\metric_base {

    public function get_name(): string {
        return 'activeusers';
    }

    public function get_label(): string {
        return get_string('pluginname', 'metric_activeusers');
    }

    public function get_metric_item(): metric_item {
        $currentgroup = null;
        $now = time();
        $context = \context_system::instance();
        $timetoshowusers = $this->get_time_to_show_users();
        $activeusers = new fetcher($currentgroup, $now, $timetoshowusers, $context);

        return new metric_item($this->get_name(), $now, $activeusers->count_users(), $this);
    }

    /**
     * @return int Time as seconds.
     */
    public function get_time_to_show_users(): int {
        $value = (int) get_config('metric_activeusers', 'time_to_show_users');
        if ($value < 100) {
            $value = 100; // Fetcher will round to nearest 100.
        }
        return $value;
    }
}
