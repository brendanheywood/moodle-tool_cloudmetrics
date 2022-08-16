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

namespace tool_cloudmetrics\metric;

/**
 * Base class for builtin metrics.
 *
 * @package    tool_cloudmetrics
 * @author     Jason den Dulk <jasondendulk@catalyst-au.net>
 * @copyright  2022, Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class builtin_base extends base {
    /**
     * The metric's display name.
     *
     * @return string
     */
    public function get_label(): string {
        return get_string($this->get_name(), 'tool_cloudmetrics');
    }

    /**
     * The metric's description.
     *
     * @return string
     */
    public function get_description(): string {
        return get_string($this->get_name() . '_desc', 'tool_cloudmetrics');
    }

    /**
     * The plugin that defines the metric.
     *
     * @return string
     */
    public function get_plugin_name(): string {
        return 'tool_cloudmetrics';
    }

    /**
     * The metric's default frequency
     *
     * @return int
     */
    public function get_frequency_default(): int {
        return manager::FREQ_DEFAULTS[$this->get_name()];
    }
}
