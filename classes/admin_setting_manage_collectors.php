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
 * TODO: This is largely copied from lib/adminlib.php:admin_setting_managedataformats.
 */
class admin_setting_manage_collectors extends \admin_setting {

    /**
     * Calls parent::__construct with specific arguments
     */
    public function __construct() {
        $this->nosave = true;
        parent::__construct('managecollectors', get_string('manage_collectors', 'tool_cloudmetrics'), '', '');
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
     * Search to find if Query is related to format plugin
     *
     * @param string $query The string to search for
     * @return bool true for related false for not
     */
    public function is_related($query) {
        if (parent::is_related($query)) {
            return true;
        }
        $formats = \core_plugin_manager::instance()->get_plugins_of_type('cltr');
        foreach ($formats as $format) {
            if (strpos($format->component, $query) !== false ||
                strpos(\core_text::strtolower($format->displayname), $query) !== false) {
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

        $formats = \core_plugin_manager::instance()->get_plugins_of_type('cltr');

        $txt = get_strings(array('settings', 'name', 'enable', 'disable', 'default'));
        $txt->uninstall = get_string('uninstallplugin', 'core_admin');

        $table = new \html_table();
        $table->head  = array($txt->name, $txt->enable, $txt->uninstall, $txt->settings);
        $table->align = array('left', 'center', 'center', 'center', 'center');
        $table->attributes['class'] = 'manageformattable generaltable admintable';
        $table->data  = array();

        foreach ($formats as $format) {
            $status = $format->get_status();
            $url = new \moodle_url('/admin/tool/cloudmetrics/collectors.php',
                array('sesskey' => sesskey(), 'name' => $format->name));

            $class = '';
            if ($format->is_enabled()) {
                $strformatname = $format->displayname;
                $hideshow = \html_writer::link($url->out(false, array('action' => 'disable')),
                    $OUTPUT->pix_icon('t/hide', $txt->disable, 'moodle', array('class' => 'iconsmall')));
            } else {
                $class = 'dimmed_text';
                $strformatname = $format->displayname;
                $hideshow = \html_writer::link($url->out(false, array('action' => 'enable')),
                    $OUTPUT->pix_icon('t/show', $txt->enable, 'moodle', array('class' => 'iconsmall')));
            }

            $uninstall = '';
            if ($format->is_uninstall_allowed()) {
                if ($status === \core_plugin_manager::PLUGIN_STATUS_MISSING) {
                    $uninstall = get_string('status_missing', 'core_plugin');
                } else if ($status === \core_plugin_manager::PLUGIN_STATUS_NEW) {
                    $uninstall = get_string('status_new', 'core_plugin');
                } else if ($uninstallurl =
                    \core_plugin_manager::instance()->get_uninstall_url('cltr_' . $format->name, 'tool_cloudmetrics')) {
                    $uninstall = \html_writer::link($uninstallurl, $txt->uninstall);
                }
            }

            $settings = '';
            if ($format->get_settings_url()) {
                $settings = \html_writer::link($format->get_settings_url(), $txt->settings);
            }

            $row = new \html_table_row(array($strformatname, $hideshow, $uninstall, $settings));
            if ($class) {
                $row->attributes['class'] = $class;
            }
            $table->data[] = $row;
        }
        $return .= \html_writer::table($table);
        return highlight($query, $return);
    }
}
