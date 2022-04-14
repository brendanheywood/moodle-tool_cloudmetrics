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

namespace cltr_cloudwatch;

/**
 * General library for cloudwatch
 *
 * @package   cltr_cloudwatch
 * @author    Jason den Dulk <jasondendulk@catalyst-au.net>
 * @copyright 2022, Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class lib {
    /**
     * Returns if the plugin can be used.
     *
     * @return bool
     */
    static public function is_plugin_usable() {
        return (class_exists('\local_aws\local\client_factory') &&
                class_exists('\local_aws\admin_settings_aws_region'));
    }

    /**
     * Get the configuration values for the plugin, substituting in defaults where
     * needed.
     *
     * @return false|mixed|object|string
     * @throws \dml_exception
     */
    static public function get_config() {
        global $CFG;
        $config = get_config('cltr_cloudwatch');
        if (empty($config->namespace)) {
            $config->namespace = $CFG->wwwroot;
        }
        return $config;
    }

    /**
     * Get the namespace config value, defaulting to $CFG->wwwroot if not set.
     *
     * @return false|mixed|object|string
     * @throws \dml_exception
     */
    static public function get_namespace() {
        global $CFG;
        $namespace = get_config('cltr_cloudwatch', 'namespace');
        if (empty($namespace)) {
            $namespace = $CFG->wwwroot;
        }
        return $namespace;
    }
}
