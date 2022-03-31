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

use tool_cloudmetrics\metric\test_metric;
use tool_cloudmetrics\collector;

/**
 * Generate mock metric data to send to collectors.
 *
 * @package   tool_cloudmetrics
 * @author    Jason den Dulk <jasondendulk@catalyst-au.net>
 * @copyright 2022, Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require_once(__DIR__ . '/../../../../config.php');
require_once($CFG->libdir.'/clilib.php');

// Get cli options.
list($options, $unrecognized) = cli_get_params(
    [
        'help' => false,
        'name' => false,
    ], [
        'h' => 'help',
        'n' => 'name',
    ]
);

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    $help =
        "Add metrics.

Generate mock metric data to fill collectors with.
Do not use with production environments.

Options:
-h, --help               Print out this help
-n, --name               The name of the metric being mocked.

Example:
\$sudo -u www-data /usr/bin/php admin/tool/cloudmetrics/cli/add_metrics.php --name=activeusers
";

    echo $help;
    die;
}



$metric = new test_metric();
$metric->name = $options['name'];

for ($i = 0; $i < 100; ++$i) {
    $item = $metric->get_metric_item();
    collector\manager::send_metric($item);
}
