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

namespace tool_cloudmetrics;

/**
 * Test metric that generates a random trail of data.
 *
 * @package   tool_cloudmetrics
 * @author    Jason den Dulk <jasondendulk@catalyst-au.net>
 * @copyright 2022, Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class test_metric extends metric_base {

    public $value = 100;
    public $variance = 10;

    public $starttime = 1640955600; // Midnight, 1st Jan 2022.
    public $interval = 86400; // 1 day.

    public $name = 'foobar';

    public function get_name(): string {
        return $this->name;
    }

    public function get_label(): string {
        return 'Test metric'; // Don't use get_string as this is for testing only.
    }

    public function get_metric_item(): metric_item {
        $this->value += rand(-$this->variance, $this->variance);
        return new metric_item($this->get_name(), $this->starttime += $this->interval, $this->value, $this);
    }
}

