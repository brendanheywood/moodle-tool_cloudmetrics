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
 * Settings for cltr_cloudwatch.
 *
 * @package   cltr_cloudwatch
 * @author    Jason den Dulk <jasondendulk@catalyst-au.net>
 * @copyright 2022, Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {

    if ($ADMIN->fulltree) {

        // Some of the settings cannot be created if the plugin is not fully usable.
        if (\cltr_cloudwatch\lib::is_plugin_usable()) {
            $settings->add(new admin_setting_heading('cltr_cloudwatch_settings', '',
                get_string('pluginnamedesc', 'cltr_cloudwatch')));

            // AWS settings.
            $settings->add(new admin_setting_heading('cltr_cloudwatch_aws',
                get_string('awssettings', 'cltr_cloudwatch'),
                get_string('awssettings_desc', 'cltr_cloudwatch')
            ));

            $settings->add(new \local_aws\admin_settings_aws_region('cltr_cloudwatch/awsregion',
                get_string('awsregion', 'cltr_cloudwatch'),
                get_string('awsregion_desc', 'cltr_cloudwatch'),
                'ap-southeast-2'
            ));

            // General Settings.
            $settings->add(new admin_setting_heading('cltr_cloudwatch_general',
                get_string('generalsettings', 'cltr_cloudwatch'),
                get_string('generalsettings_desc', 'cltr_cloudwatch')
            ));

            // Namespace.
            $settings->add(new admin_setting_configtext('cltr_cloudwatch/namespace',
                get_string('namespace', 'cltr_cloudwatch'),
                get_string('namespace_desc', 'cltr_cloudwatch'),
                '', PARAM_TEXT));

            $envoptions = [
                'Dev' => get_string('Dev', 'cltr_cloudwatch'),
                'Uat' => get_string('Uat', 'cltr_cloudwatch'),
                'Qat' => get_string('Qat', 'cltr_cloudwatch'),
                'Prod' => get_string('Prod', 'cltr_cloudwatch'),
            ];

            // Environment.
            $settings->add(new admin_setting_configselect('cltr_cloudwatch/environment',
                get_string('environment', 'cltr_cloudwatch'),
                get_string('environment_desc', 'cltr_cloudwatch'),
                'Dev', $envoptions));
        } else {
            $plugininfo = $plugins = \core_plugin_manager::instance()->get_plugin_info('local_aws');
            if (is_null($plugininfo)) {
                $text = $OUTPUT->notification(get_string('aws:installneeded', 'cltr_cloudwatch'));
                $settings->add(new \admin_setting_heading('cltr_cloudwatch_aws',
                    get_string('unsatisfied_requirements', 'cltr_cloudwatch'),
                    $text
                ));
            } else if ($plugininfo->versiondisk < \cltr_cloudwatch\lib::LOCAL_AWS_VERSION) {
                $text = $OUTPUT->notification(get_string('aws:upgradeneeded', 'cltr_cloudwatch'));
                $settings->add(new \admin_setting_heading('cltr_cloudwatch_aws',
                    get_string('unsatisfied_requirements', 'cltr_cloudwatch'),
                    $text
                ));
            }
        }
    }
}
