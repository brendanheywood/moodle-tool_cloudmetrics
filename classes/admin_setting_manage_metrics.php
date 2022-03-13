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
 * A table to manage collector plugins.
 * TODO: This is largely copied from lib/adminlib.php:admin_setting_managedataplugins.
 */
class admin_setting_manage_metrics extends \admin_setting {

    /**
     * Calls parent::__construct with specific arguments
     */
    public function __construct() {
        $this->nosave = true;
        parent::__construct('managemetrics', get_string('manage_metrics', 'tool_cloudmetrics'), '', '');
    }

    /**
     * Always returns true
     *
     * @return true
     */
    public function get_setting() {
        return true;
    }

    /**
     * Always returns true
     *
     * @return true
     */
    public function get_defaultsetting() {
        return true;
    }

    /**
     * Always returns '' and doesn't write anything
     *
     * @param mixed $data string or array, must not be NULL
     * @return string Always returns ''
     */
    public function write_setting($data) {
        // Do not write any setting.
        return '';
    }

    /**
     * Search to find if Query is related to plugin.
     *
     * @param string $query The string to search for
     * @return bool true for related false for not
     */
    public function is_related($query) {
        if (parent::is_related($query)) {
            return true;
        }
        $plugins = \core_plugin_manager::instance()->get_plugins_of_type('metric');
        foreach ($plugins as $plugin) {
            if (strpos($plugin->component, $query) !== false ||
                strpos(\core_text::strtolower($plugin->displayname), $query) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Return XHTML to display control
     *
     * @param mixed $data Unused
     * @param string $query
     * @return string highlight
     */
    public function output_html($data, $query='') {
        global $OUTPUT;
        $return = '';

        $plugins = \core_plugin_manager::instance()->get_plugins_of_type('metric');

        $txt = get_strings(array('settings', 'name', 'enable', 'disable', 'default'));
        $txt->uninstall = get_string('uninstallplugin', 'core_admin');

        $table = new \html_table();
        $table->head  = array($txt->name, $txt->enable, $txt->uninstall, $txt->settings);
        $table->align = array('left', 'center', 'center', 'center', 'center');
        $table->attributes['class'] = 'manageplugintable generaltable admintable';
        $table->data  = array();

        foreach ($plugins as $plugin) {
            $status = $plugin->get_status();
            $url = new \moodle_url('/admin/tool/cloudmetrics/metrics.php',
                array('sesskey' => sesskey(), 'name' => $plugin->name));

            $class = '';
            if ($plugin->is_enabled()) {
                $strpluginname = $plugin->displayname;
                $hideshow = \html_writer::link($url->out(false, array('action' => 'disable')),
                    $OUTPUT->pix_icon('t/hide', $txt->disable, 'moodle', array('class' => 'iconsmall')));
            } else {
                $class = 'dimmed_text';
                $strpluginname = $plugin->displayname;
                $hideshow = \html_writer::link($url->out(false, array('action' => 'enable')),
                    $OUTPUT->pix_icon('t/show', $txt->enable, 'moodle', array('class' => 'iconsmall')));
            }

            $uninstall = '';
            if ($plugin->is_uninstall_allowed()) {
                if ($status === \core_plugin_manager::PLUGIN_STATUS_MISSING) {
                    $uninstall = get_string('status_missing', 'core_plugin');
                } else if ($status === \core_plugin_manager::PLUGIN_STATUS_NEW) {
                    $uninstall = get_string('status_new', 'core_plugin');
                } else if ($uninstallurl =
                    \core_plugin_manager::instance()->get_uninstall_url('metric_' . $plugin->name, 'tool_cloudmetrics')) {
                    $uninstall = \html_writer::link($uninstallurl, $txt->uninstall);
                }
            }

            $settings = '';
            if ($plugin->get_settings_url()) {
                $settings = \html_writer::link($plugin->get_settings_url(), $txt->settings);
            }

            $row = new \html_table_row(array($strpluginname, $hideshow, $uninstall, $settings));
            if ($class) {
                $row->attributes['class'] = $class;
            }
            $table->data[] = $row;
        }
        $return .= \html_writer::table($table);
        return highlight($query, $return);
    }
}
