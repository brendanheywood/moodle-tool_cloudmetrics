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

use tool_cloudmetrics\plugininfo\cltr;

/**
 * Base class for collectors.
 *
 * @package   tool_cloudmetrics
 * @author    Jason den Dulk <jasondendulk@catalyst-au.net>
 * @copyright 2022, Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class collector_base {
    /**
     * Get all the collector classes.
     *
     * @return array
     */
    public static function get_collector_classes(): array {
        return \core_component::get_plugin_list_with_class('cltr', 'collector');
    }

    /**
     * Sends a metric to all enabled collectors to be recorded.
     *
     * @param metric_item $metric_item
     */
    public static function send_metric(metric_item $metricitem) {
        $plugins = cltr::get_enabled_plugins();
        foreach ($plugins as $plugin) {
            $collector = $plugin->get_collector();
            if ($collector->is_ready()) {
                $collector->record_metric($metricitem);
            }
        }
    }

    /**
     * Records a single metric.
     *
     * @param metric_item $metric
     * @return mixed
     */
    abstract public function record_metric(metric_item $metric);

    /**
     * Returns true if the backend service is able to receive requests.
     *
     * @return bool
     */
    abstract public function is_ready(): bool;
}
