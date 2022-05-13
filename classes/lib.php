<?php
// This file is part of Moodle - https://moodle.org/
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
 * Library for fucntions that don't belong anywhere else.
 *
 * @package   tool_cloudmetrics
 * @author    Jason den Dulk <jasondendulk@catalyst-au.net>
 * @copyright 2022, Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class lib {
    const FREQ_TIMES = [
        metric\manager::FREQ_MIN => MINSECS,
        metric\manager::FREQ_5MIN => MINSECS * 5,
        metric\manager::FREQ_15MIN => MINSECS * 15,
        metric\manager::FREQ_30MIN => MINSECS * 30,
        metric\manager::FREQ_HOUR => MINSECS * 60,
        metric\manager::FREQ_3HOUR => MINSECS * 180,
        metric\manager::FREQ_12HOUR => MINSECS * 720,
        metric\manager::FREQ_DAY => MINSECS * 1440,
        metric\manager::FREQ_WEEK => MINSECS * 10080,
    ];

    /**
     * Get the time which is one 'frequency' unit before the given time.
     *
     * @param int $timestamp
     * @param int $freq FREQ_ constant as given in metric\manager.
     * @return int
     * @throws \Exception
     */
    static function get_previous_time(int $timestamp, int $freq): int {
        if ($freq == metric\manager::FREQ_MONTH) {
            $tz = \core_date::get_server_timezone_object();
            // special handling for months because it is not a consistant value.
            return (new \DateTime('', $tz))
                ->setTimestamp($timestamp)
                ->modify('-1 month')
                ->getTimestamp();
        } else {
            return $timestamp - self::FREQ_TIMES[$freq];
        }
    }

    /**
     * Get the time which is one 'frequency' unit after the given time.
     *
     * @param int $timestamp
     * @param int $freq FREQ_ constant as given in metric\manager.
     * @return int
     * @throws \Exception
     */
    static function get_next_time(int $timestamp, int $freq): int {
        if ($freq == metric\manager::FREQ_MONTH) {
            $tz = \core_date::get_server_timezone_object();
            // special handling for months because it is not a consistant value.
            return (new \DateTime('', $tz))
                ->setTimestamp($timestamp)
                ->modify('+1 month')
                ->getTimestamp();
        } else {
            return $timestamp + self::FREQ_TIMES[$freq];
        }
    }

    /**
     * Get the time before $timestamp which aligns with the 'frequency' unit.
     * For example, with FREQ_5MIN, we want 5, 10, 15, 20, etc past the hour.
     *
     * @param int $timestamp
     * @param int $freq FREQ_ constant as given in metric\manager.
     * @return int
     * @throws \Exception
     */
    static function get_last_whole_tick(int $timestamp, int $freq): int {

        // Use the server's timezone for determining times from strings.
        $tz = \core_date::get_server_timezone_object();
        $dt = new \DateTime('', $tz);
        $dt->setTimestamp($timestamp);

        if ($freq == metric\manager::FREQ_MONTH) {
            // special handling for months because it is not a consistant value.
            $dt = new \DateTime($dt->format('Y-m-01\T00:00:00'), $tz);
            return $dt->getTimestamp();
        } else {
            $dt->modify('midnight last Sunday');
            $reftime = $dt->getTimestamp();
            return $timestamp - ($timestamp - $reftime) % self::FREQ_TIMES[$freq];
        }
    }
}

