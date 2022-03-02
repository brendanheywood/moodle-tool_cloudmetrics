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
 * Settings
 *
 * @package   tool_cloudwatch
 * @author    Jason den Dulk <jasondendulk@catalyst-au.net>
 * @copyright 2022, Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $ADMIN->add('reports', new admin_category('tool_cloudwatch_reports', 'Cloudwatch'));

    $settings = new admin_settingpage(
        'tool_cloudwatch',
        get_string('pluginname', 'tool_cloudwatch')
    );

    $ADMIN->add('tool_cloudwatch_reports', $settings);

    if ($ADMIN->fulltree) {

        $settings->add(
            new admin_setting_configselect(
                'tool_cloudwatch/destinaton',
                get_string('setting:destination', 'tool_cloudwatch'),
                get_string('setting:destination_desc', 'tool_cloudwatch'),
                'database',
                '\tool_cloudwatch\collector_base::get_collectors_for_settings'
            )
        );
    }
}

