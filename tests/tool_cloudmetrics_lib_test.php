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
 * Unit tests for lib class.
 *
 * @package   tool_cloudmetrics
 * @author    Jason den Dulk <jasondendulk@catalyst-au.net>
 * @copyright 2022, Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_cloudmetrics_lib_test  extends \advanced_testcase {

    /**
     * Tests lib::get_previous_time()
     *
     * @dataProvider data_for_get_previous_time
     * @param string $ref
     * @param int $freq
     * @param string $expected
     * @throws \Exception
     */
    public function test_get_previous_time(string $ref, int $freq, string $expected) {
        $tz = \core_date::get_server_timezone_object();

        $this->assertEquals(
            (new \DateTime($expected, $tz))->getTimestamp(),
            lib::get_previous_time((new \DateTime($ref, $tz))->getTimestamp(), $freq)
        );
    }

    /**
     * Data for test_get_previous_time()
     *
     * @return array[]
     */
    public function data_for_get_previous_time(): array {
        return [
            ['10:05',  metric\manager::FREQ_MIN, '10:04'],
            ['10:07',  metric\manager::FREQ_5MIN, '10:02'],
            ['10:27',  metric\manager::FREQ_15MIN, '10:12'],
            ['10:07',  metric\manager::FREQ_30MIN, '09:37'],
            ['10:12',  metric\manager::FREQ_HOUR, '09:12'],
            ['10:07',  metric\manager::FREQ_3HOUR, '07:07'],
            ['2020-01-02T10:12:00',  metric\manager::FREQ_12HOUR, '2020-01-01T22:12:00'],
            ['2020-01-02T10:12:00',  metric\manager::FREQ_DAY, '2020-01-01T10:12:00'],
            ['2020-01-02T10:12:00',  metric\manager::FREQ_WEEK, '2019-12-26T10:12:00'],
            ['2020-01-01T10:12:00',  metric\manager::FREQ_MONTH, '2019-12-01T10:12:00'],
        ];
    }

    /**
     * Tests lib::get_next_time()
     *
     * @dataProvider data_for_get_next_time
     * @param string $ref
     * @param int $freq
     * @param string $expected
     * @throws \Exception
     */
    public function test_get_next_time(string $ref, int $freq, string $expected) {
        $tz = \core_date::get_server_timezone_object();

        $this->assertEquals(
            (new \DateTime($expected, $tz))->getTimestamp(),
            lib::get_next_time((new \DateTime($ref, $tz))->getTimestamp(), $freq)
        );
    }

    /**
     * Data for test_get_next_time()
     *
     * @return array[]
     */
    public function data_for_get_next_time(): array {
        return [
            ['10:05',  metric\manager::FREQ_MIN, '10:06'],
            ['10:07',  metric\manager::FREQ_5MIN, '10:12'],
            ['10:27',  metric\manager::FREQ_15MIN, '10:42'],
            ['10:07',  metric\manager::FREQ_30MIN, '10:37'],
            ['10:12',  metric\manager::FREQ_HOUR, '11:12'],
            ['10:07',  metric\manager::FREQ_3HOUR, '13:07'],
            ['2020-01-02T10:12:00',  metric\manager::FREQ_12HOUR, '2020-01-02T22:12:00'],
            ['2020-01-02T10:12:00',  metric\manager::FREQ_DAY, '2020-01-03T10:12:00'],
            ['2020-01-02T10:12:00',  metric\manager::FREQ_WEEK, '2020-01-09T10:12:00'],
            ['2020-01-01T10:12:00',  metric\manager::FREQ_MONTH, '2020-02-01T10:12:00'],
        ];
    }

    /**
     * Tests lib::get_next_time()
     *
     * @dataProvider data_for_get_last_whole_tick
     * @param string $ref
     * @param int $freq
     * @param string $expected
     * @throws \Exception
     */
    public function test_get_last_whole_tick(string $ref, int $freq, string $expected) {
        $tz = \core_date::get_server_timezone_object();

        $this->assertEquals(
            (new \DateTime($expected, $tz))->getTimestamp(),
            lib::get_last_whole_tick((new \DateTime($ref, $tz))->getTimestamp(), $freq)
        );
    }

    /**
     * Data for test_get_next_time()
     *
     * @return array[]
     */
    public function data_for_get_last_whole_tick(): array {
        return [
            ['10:06',  metric\manager::FREQ_MIN, '10:06'],
            ['10:07',  metric\manager::FREQ_5MIN, '10:05'],
            ['10:27',  metric\manager::FREQ_15MIN, '10:15'],
            ['10:07',  metric\manager::FREQ_30MIN, '10:00'],
            ['10:55',  metric\manager::FREQ_30MIN, '10:30'],
            ['10:12',  metric\manager::FREQ_HOUR, '10:00'],
            ['10:07',  metric\manager::FREQ_3HOUR, '09:00'],
            ['2020-01-02T10:12:00',  metric\manager::FREQ_12HOUR, '2020-01-02T00:00:00'],
            ['2020-01-02T23:12:00',  metric\manager::FREQ_12HOUR, '2020-01-02T12:00:00'],
            ['2020-01-02T10:12:00',  metric\manager::FREQ_DAY, '2020-01-02T00:00:00'],
            ['2020-01-02T10:12:00',  metric\manager::FREQ_WEEK, '2019-12-29T00:00:00'],
            ['2020-01-04T10:12:00',  metric\manager::FREQ_WEEK, '2019-12-29T00:00:00'],
            ['2020-01-16T10:12:00',  metric\manager::FREQ_MONTH, '2020-01-01T00:00:00'],
        ];
    }
}
