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
 * A test script to demonstrate basic metric->collector functionality.
 *
 * @package   tool_cloudmetrics
 * @author     Jason den Dulk <jasondendulk@catalyst-au.net>
 * @copyright  2022, Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_cloudmetrics;

use tool_cloudmetrics\metric\test_metric;
use tool_cloudmetrics\collector\test_collector;

define('CLI_SCRIPT', true);

require_once(__DIR__ . '/../../../../config.php');

$collector = new test_collector();
$metric = new test_metric();
for ($x = 0; $x <= 100; ++$x) {
    $collector->record_metric($metric->generate_metric_item(0, $x));
}
