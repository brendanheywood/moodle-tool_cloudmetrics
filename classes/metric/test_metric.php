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
 * Test metric that generates a random trail of data.
 *
 * @package   tool_cloudmetrics
 * @author    Jason den Dulk <jasondendulk@catalyst-au.net>
 * @copyright 2022, Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class test_metric extends base {

    public $value = 100;
    public $variance = 10;

    public $starttime = 1640955600; // Midnight, 1st Jan 2022.
    public $interval = 86400; // 1 day.

    public $name = 'foobar';

    public $enabled = false;
    public $isready = true;

    /**
     * The metric's name.
     *
     * @return string
     */
    public function get_name(): string {
        return $this->name;
    }

    /**
     * The metric's display name.
     *
     * @return string
     */
    public function get_label(): string {
        return 'Test metric'; // Don't use get_string as this is for testing only.
    }

    /**
     * A short description of the metric.
     *
     * @return string
     */
    public function get_description(): string {
        return 'Test metric';
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
     * The frequency of the metric's sampling.
     *
     * @return int
     */
    public function get_frequency(): int {
        return manager::FREQ_MIN;
    }

    public function get_frequency_default(): int {
        return manager::FREQ_MIN;
    }

    /**
     * The metric type.
     *
     * @return int
     */
    public function get_type(): int {
        return manager::TYPE_GAUGE;
    }

    /**
     * Is the metric switched on?
     *
     * @return bool
     */
    public function is_enabled(): bool {
        return $this->enabled;
    }

    /**
     * Sets the enabled status.
     *
     * @param bool $enabled
     */
    public function set_enabled(bool $enabled) {
        $this->enabled = $enabled;
    }

    public function is_ready(): bool {
        return $this->isready;
    }

    /**
     * Retrieves the metric.
     *
     * @return metric_item
     */
    public function get_metric_item(): metric_item {
        $this->value += rand(-$this->variance, $this->variance);
        return new metric_item($this->get_name(), $this->starttime += $this->interval, $this->value, $this);
    }
}

