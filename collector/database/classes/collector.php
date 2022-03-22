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

namespace cltr_database;

use tool_cloudmetrics\metric\metric_item;
use tool_cloudmetrics\collector\base;

/**
 * Collector class for the internal database.
 *
 * @package   cltr_database
 * @author    Jason den Dulk <jasondendulk@catalyst-au.net>
 * @copyright 2022, Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class collector extends base {
    public function record_metric(metric_item $item) {
        global $DB;

        $DB->insert_record(
            lib::TABLE,
            ['name' => $item->name, 'value' => $item->value, 'time' => $item->time]
        );
    }

    public function is_ready(): bool {
        return true;
    }
}
