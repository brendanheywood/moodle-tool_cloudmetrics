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
 * Language strings
 *
 * @package    tool_cloudmetrics
 * @author     Jason den Dulk <jasondendulk@catalyst-au.net>
 * @copyright  2022, Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Cloudmetrics';

// Privacy.
$string['privacy:metadata'] = 'No personal information is stored';

// Subplugins.
$string['subplugintype_cltr'] = 'Collector for a cloud metric service';
$string['subplugintype_cltr_plural'] = 'Collectors for cloud metric services';
$string['subplugintype_metric'] = 'Metric source';
$string['subplugintype_metric_plural'] = 'Metric sources';

$string['manage_collectors'] = 'Manage Collectors';


// Error messages.
$string['plugin_not_found'] = 'Plugin \'{$a}\' not found';
