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

namespace tool_cloudmetrics\plugininfo;

use tool_cloudmetrics\metric_base;

/**
 * Pluginino class for metrics.
 *
 * @package   tool_cloudmetrics
 * @author    Jason den Dulk <jasondendulk@catalyst-au.net>
 * @copyright 2022, Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class metric extends \core\plugininfo\base {

    const BUILTIN_PLUGINS = [
        'activeusers'
    ];

    /**
     * Finds all enabled plugins, the result may include missing plugins.
     * @return array|null of enabled plugins $pluginname=>$pluginname, null means unknown
     */
    public static function get_enabled_plugins() {
        $plugins = \core_plugin_manager::instance()->get_plugins_of_type('metric');
        foreach ($plugins as $name => $plugin) {
            if (!$plugin->is_enabled()) {
                unset($plugins[$name]);
            }
        }
        return $plugins;
    }

    public static function get_plugin($name) {
        // TODO: is there a better way to get a specific plugin?
        $plugins = \core_plugin_manager::instance()->get_plugins_of_type('metric');
        if (isset($plugins[$name])) {
            return $plugins[$name];
        } else {
            return false;
        }
    }

    /**
     * Returns the information about plugin availability
     *
     * True means that the plugin is enabled. False means that the plugin is
     * disabled. Null means that the information is not available, or the
     * plugin does not support configurable availability or the availability
     * can not be changed.
     *
     * @return null|bool
     */
    public function is_enabled(): bool {
        return !((bool) get_config('metric_' . $this->name, 'disabled'));
    }

    /**
     * Enable/disable the plugin
     *
     * @param bool $enable
     */
    public function set_enabled(bool $enable) {
        if ($this->is_enabled() != $enable) {
            set_config('disabled', (int) !$enable, 'metric_' . $this->name);
            \core_plugin_manager::reset_caches();
        }
    }

    /**
     * Get the metric class for this plugin.
     *
     * @return metric_base
     */
    public function get_metric(): metric_base {
        $classname = '\\metric_' . $this->name . '\metric';
        return new $classname();
    }

    /**
     * Returns the node name used in admin settings menu for this plugin settings (if applicable)
     *
     * @return null|string node name or null if plugin does not create settings node (default)
     */
    public function get_settings_section_name() {
        return 'metric_' . $this->name;
    }

    public function is_uninstall_allowed() {
        return !in_array($this->name, self::BUILTIN_PLUGINS);
    }

    public function load_settings(\part_of_admin_tree $adminroot, $parentnodename, $hassiteconfig) {
        global $CFG, $USER, $DB, $OUTPUT, $PAGE; // In case settings.php wants to refer to them.
        $ADMIN = $adminroot; // May be used in settings.php.
        $plugininfo = $this; // Also can be used inside settings.php.

        if (!$this->is_installed_and_upgraded()) {
            return;
        }

        if (!$hassiteconfig or !file_exists($this->full_path('settings.php'))) {
            return;
        }

        $section = $this->get_settings_section_name();
        $settings = new \admin_settingpage($section, $this->displayname, 'moodle/site:config');
        include($this->full_path('settings.php')); // This may also set $settings to null.

        if ($settings) {
            $ADMIN->add($parentnodename, $settings);
        }
    }
}
