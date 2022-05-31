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

use tool_cloudmetrics\metric\metric_item;
use tool_cloudmetrics\metric\new_users_metric;
use tool_cloudmetrics\metric\active_users_metric;

/**
 * <insertdescription>
 *
 * @package   <insert>
 * @author    Jason den Dulk <jasondendulk@catalyst-au.net>
 * @copyright 2022, Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_cloudmetrics_users_test extends \advanced_testcase {

    /**
     * Set up before each test
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
    }

    /** @var array[] Sample user DB data to be used in tests. */
    public const USER_DATA = [
        ['username' => 'a', 'firstaccess' => 1000, 'lastaccess' => 2000, 'lastlogin' => 2560],
        ['username' => 'b', 'firstaccess' => 500, 'lastaccess' => 2000, 'lastlogin' => 4200],
        ['username' => 'c', 'firstaccess' => 1100, 'lastaccess' => 2000, 'lastlogin' => 2871],
        ['username' => 'd', 'firstaccess' => 1150, 'lastaccess' => 2000, 'lastlogin' => 1401],
        ['username' => 'e', 'firstaccess' => 1500, 'lastaccess' => 2000, 'lastlogin' => 2940],
        ['username' => 'f', 'firstaccess' => 2450, 'lastaccess' => 2000, 'lastlogin' => 2790],
        ['username' => 'g', 'firstaccess' => 2500, 'lastaccess' => 2000, 'lastlogin' => 2999],
        ['username' => 'h', 'firstaccess' => 2600, 'lastaccess' => 2000, 'lastlogin' => 2777],
        ['username' => 'i', 'firstaccess' => 2800, 'lastaccess' => 2000, 'lastlogin' => 2882],
        ['username' => 'j', 'firstaccess' => 3000, 'lastaccess' => 2000, 'lastlogin' => 2500],
    ];

    /**
     * Tests generate_metric_items() for the builtin user metrics.
     *
     * @dataProvider data_for_users_metric_test
     * @param string $metricname The name of the metric to be tested.
     * @param int $frequency The frequency setting as a metric\manager::FREQ_ value.
     * @param array $expected List of metric items that expect to be generated.
     * @throws \dml_exception
     */
    public function test_users_metric(string $metricname, int $frequency, array $expected) {
        global $DB;

        foreach (self::USER_DATA as $row) {
            $DB->insert_record('user', (object) $row);
        }

        $metrictypes = metric\manager::get_metrics(false);
        $metric = $metrictypes[$metricname];
        $this->assertEquals($metricname, $metric->get_name());
        $metric->set_frequency($frequency);
        set_config($metricname . '_time_window', MINSECS * 5, 'tool_cloudmetrics');

        $finishtime = 3000;
        $items = $metric->generate_metric_items($finishtime - (5 * lib::FREQ_TIMES[$metric->get_frequency()]), $finishtime);
        $this->assertEquals($expected, $items);
    }

    public function data_for_users_metric_test(): array {
        $newusersmetric = new new_users_metric();
        $activeusersmetric = new active_users_metric();
        return [
            [ 'newusers', metric\manager::FREQ_5MIN, [
                    new metric_item('newusers', 1800, 0, $newusersmetric),
                    new metric_item('newusers', 2100, 0, $newusersmetric),
                    new metric_item('newusers', 2400, 0, $newusersmetric),
                    new metric_item('newusers', 2700, 3, $newusersmetric),
                    new metric_item('newusers', 3000, 2, $newusersmetric),
                ]
            ],
            [ 'activeusers', metric\manager::FREQ_MIN, [
                    new metric_item('activeusers', 2760, 2, $activeusersmetric),
                    new metric_item('activeusers', 2820, 3, $activeusersmetric),
                    new metric_item('activeusers', 2880, 3, $activeusersmetric),
                    new metric_item('activeusers', 2940, 5, $activeusersmetric),
                    new metric_item('activeusers', 3000, 6, $activeusersmetric),
                ]
            ],
        ];
    }
}
