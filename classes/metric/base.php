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
 * Base class for metrics.
 *
 * @package    tool_cloudmetrics
 * @author     Jason den Dulk <jasondendulk@catalyst-au.net>
 * @copyright  2022, Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class base {
    /**
     * The metric's name.
     *
     * @return string
     */
    abstract public function get_name(): string;

    /**
     * The metric's display name.
     *
     * @return string
     */
    abstract public function get_label(): string;

    /**
     * The frequency of the metric's sampling.
     *
     * @return int
     */
    public function get_frequency(): int {
        return (int) get_config('tool_cloudmetrics', $this->get_name() . '_frequency');
    }

    /**
     * Set frequency of the metric's sampling.
     *
     * @return int
     */
    public function set_frequency(int $freq) {
        set_config($this->get_name() . '_frequency', $freq, 'tool_cloudmetrics');
    }

    /**
     * The metric type.
     *
     * @return int
     */
    abstract public function get_type(): int;

    /**
     * Is the metric switched on?
     *
     * @return bool
     */
    public function is_enabled(): bool {
        return (bool) get_config('tool_cloudmetrics', $this->get_name() . '_enabled');
    }

    /**
     * Sets the enabled status.
     *
     * @param bool $enabled
     */
    public function set_enabled(bool $enabled) {
        set_config($this->get_name() . '_enabled', (int) $enabled, 'tool_cloudmetrics');
    }

    /**
     * Retrieves the metric.
     *
     * @return metric_item
     */
    abstract public function get_metric_item(): metric_item;
}
