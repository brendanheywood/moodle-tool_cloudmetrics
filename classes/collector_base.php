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

namespace tool_cloudwatch;

/**
 * Base class for collectors.
 *
 * @package   tool_cloudwatch
 * @author    Jason den Dulk <jasondendulk@catalyst-au.net>
 * @copyright 2022, Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class collector_base {
    public static function get_collector_classes() {
        return \core_component::get_plugin_list_with_class('cwcltr', 'collector');
    }

    /**
     * Returns a list of available collectors for the settings.
     *
     * @return array
     */
    public static function get_collectors_for_settings(): array {
        $choices = [];
        $classes = self::get_collector_classes();
        foreach ($classes as $class) {
            $choices[$class] = $class::get_label();
        }
        return $choices;
    }

    const DEFAULT_COLLECTOR = '\cwcltr_database\collector';
    public static function get_instance(): collector_base {
        $class = get_config('tool_cloudwatch', 'setting:destination');
        if ($class === false) {
            $class = self::DEFAULT_COLLECTOR;
        }
        return new $class();
    }

    /**
     * Records a single value of a single metric.
     * TODO: This function is expected to change a lot, but use a primitive interface for now.
     *
     * @param string $name The metric name
     * @param int $time Time the metric was recorded.
     * @param mixed $value The metric value
     */
    abstract public function record_metric(string $name, int $time, $value);

    /**
     * Returns the label describing the collector.
     * @return string
     */
    abstract public static function get_label(): string;
}
