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

/**
 * A task to obtain the active users.
 *
 * @package   metric_activeusers
 * @author    Jason den Dulk <jasondendulk@catalyst-au.net>
 * @copyright 2022, Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace metric_activeusers\task;


class get_metric_task extends \core\task\scheduled_task {

    public function get_name() {
        return get_string('pluginname', 'metric_activeusers');
    }

    public function execute() {
        $metric = new \metric_activeusers\metric();
        \tool_cloudmetrics\collector_base::send_metric($metric->get_metric_item());
    }
}
