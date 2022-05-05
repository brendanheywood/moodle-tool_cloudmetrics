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

namespace tool_cloudmetrics\collector;

use tool_cloudmetrics\metric\metric_item;
use tool_cloudmetrics\plugininfo\cltr;

/**
 * Manager class for collectors.
 *
 * @package   tool_cloudmetrics
 * @author    Jason den Dulk <jasondendulk@catalyst-au.net>
 * @copyright 2022, Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manager {
    /**
     * Get all the collector classes.
     *
     * @return array
     */
    public static function get_collector_classes(): array {
        return \core_component::get_plugin_list_with_class('cltr', 'collector');
    }

    /**
     * Sends an array of metrics to all enabled collectors.
     *
     * @param array $items
     */
    public static function send_metrics(array $items) {
        $plugins = cltr::get_enabled_plugins();
        foreach ($plugins as $plugin) {
            $collector = $plugin->get_collector();
            try {
                if ($collector->is_ready()) {
                    $collector->record_metrics($items);
                }
            } catch (\Exception $e) {
                debugging('Collector ' . $plugin->name . ' failed. "' . $e->getMessage() . '"');
            }
        }
    }
}
