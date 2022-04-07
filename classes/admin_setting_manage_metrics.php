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

use tool_cloudmetrics\metric\manager;

/**
 * Admin setting object for managing metrics
 *
 * @package   tool_cloudmetrics
 * @author    Jason den Dulk <jasondendulk@catalyst-au.net>
 * @copyright 2022, Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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

    // TODO is_related().

    /**
     * Return XHTML to display control
     *
     * @param mixed $data Unused
     * @param string $query
     * @return string highlight
     */
    public function output_html($data, $query='') {
        global $OUTPUT;

        $metrics = manager::get_metrics(false);

        $txt = get_strings(array('settings', 'name', 'enable', 'disable', 'default'));

        $table = new \html_table();
        $table->head  = array($txt->name, $txt->enable, $txt->settings);
        $table->align = array('left', 'center', 'center');
        $table->attributes['class'] = 'manageformattable generaltable admintable w-auto';
        $table->data  = array();

        foreach ($metrics as $metric) {
            $url = new \moodle_url('/admin/tool/cloudmetrics/metrics.php',
                array('sesskey' => sesskey(), 'name' => $metric->get_name()));
            $displayname = $metric->get_label();
            if ($metric->is_enabled()) {
                $class = '';
                $hideshow = \html_writer::link($url->out(false, array('action' => 'disable')),
                    $OUTPUT->pix_icon('t/hide', $txt->disable, 'moodle', array('class' => 'iconsmall')));
            } else {
                $class = 'dimmed_text';
                $hideshow = \html_writer::link($url->out(false, array('action' => 'enable')),
                    $OUTPUT->pix_icon('t/show', $txt->enable, 'moodle', array('class' => 'iconsmall')));
            }

            // TODO: settings link.

            $row = new \html_table_row([$displayname, $hideshow, '']);
            if ($class) {
                $row->attributes['class'] = $class;
            }
            $table->data[] = $row;
        }
        $return = \html_writer::table($table);
        return highlight($query, $return);
    }
}
