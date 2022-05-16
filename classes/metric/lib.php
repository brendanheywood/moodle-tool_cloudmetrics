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
 * General functions used by plugin
 *
 * @package   tool_cloudmetrics
 * @author    Mike Macgirvin <mikemacgirvin@catalyst-au.net>
 * @copyright 2022, Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class lib {

    /**
     * Returns a calculated default chart period based on the sample interval of $metricname.
     *
     * @param string $metricname
     * @return int
     */
    public static function period_from_interval($metricname) {
        $interval = (int) get_config('tool_excimer', $metricname . '_frequency');

        if ($interval <= 2) {
            $period = DAYSECS * 7;
        } else if ($interval <= 120) {
            $period = DAYSECS * 30;
        } else if ($interval <= 2880) {
            $period = DAYSECS * 120;
        } else {
            $period = DAYSECS * 365;
        }
        return $period;
    }
}
