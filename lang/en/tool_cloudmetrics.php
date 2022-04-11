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

// Settings.
$string['manage_collectors'] = 'Manage Collectors';
$string['manage_metrics'] = 'Manage Metrics';
$string['enable_disable_collectors'] = 'Enable or disable collectors.';
$string['enable_disable_metrics'] = 'Enable or disable metrics.';
$string['builtin_metrics_settings'] = 'Built in metric settings';
$string['activeusers_frequency'] = 'Active users frequency';
$string['activeusers_frequency_desc'] = 'Frequency of taking the active users metric.';
$string['onlineusers_frequency'] = 'Online users frequency';
$string['onlineusers_frequency_desc'] = 'Frequency of taking the online users metric.';
$string['onlineusers_time_window'] = 'Online user time window';
$string['onlineusers_time_window_desc'] = 'Metric will include users who were active in this time period.';
$string['activeusers_time_window'] = 'Active user time window';
$string['activeusers_time_window_desc'] = 'Metric will include users who have been active in this time period.';
$string['newusers_time_window'] = 'Online user time window';
$string['newusers_time_window_desc'] = 'Metric will include users who first started in this time period.';
$string['newusers_frequency'] = 'New users frequency';
$string['newusers_frequency_desc'] = 'Frequency of taking the new users metric.';

$string['frequency'] = 'Frequency';
$string['change_frequency'] = 'Change frequency';

// Built in metrics.
$string['activeusers'] = 'Active users';
$string['activeusers_desc'] = 'Users that have been active in the recent past.';
$string['onlineusers'] = 'Online users';
$string['onlineusers_desc'] = 'Users that are currently online.';
$string['newusers'] = 'New users';
$string['newusers_desc'] = 'Users who have signed up recently.';

// Frequency labels.
$string['one_minute'] = '1 minute';
$string['five_minutes'] = '5 minutes';
$string['fifteen_minutes'] = '15 minutes';
$string['thirty_minutes'] = '30 minutes';
$string['one_hour'] = '1 hour';
$string['three_hour'] = '3 hours';
$string['twelve_hour'] = '12 hours';
$string['one_day'] = '1 day';
$string['one_week'] = '1 week';
$string['one_fortnight'] = '1 fortnight';
$string['one_month'] = '1 month';

// Error messages.
$string['metric_not_found'] = 'Metric \'{$a}\' not found';

