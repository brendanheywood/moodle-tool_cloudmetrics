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

use tool_cloudmetrics\metric\active_users_metric;
use tool_cloudmetrics\metric\online_users_metric;
use tool_cloudmetrics\metric\new_users_metric;
use tool_cloudmetrics\metric\manager;
use tool_cloudmetrics\task\collect_metrics_task;


defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . "/metric_testcase.php"); // This is needed. File will not be automatically included.

/**
 * Helper class to test collect_metrics_task.
 */
class mock_collect_metrics_task extends collect_metrics_task {
    public function __construct($mock) {
        $this->mock = $mock;
    }

    public function send_metrics(array $items) {
        $this->mock->send_metrics($items);
    }
}

/**
 * Basic test for collectors.
 */
class tool_cloudmetrics_collect_metrics_test extends \advanced_testcase {

    /**
     * Set up before each test
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
    }

    /**
     * Test the get_frequency_cutoff method
     *
     * @dataProvider cutoff_provider
     * @param $input
     * @param $expected
     */
    public function test_get_frequency_cutoff($input, $expected) {
        $task = new collect_metrics_task();
        $this->assertEquals($expected, $task->get_frequency_cutoff($input));
    }

    /**
     * Provider function for test_get_frequency_cutoff.
     *
     * @return array[]
     */
    public function cutoff_provider() {
        return [
            [ 5, manager::FREQ_5MIN ],
            [ 15, manager::FREQ_15MIN ],
            [ 30, manager::FREQ_30MIN ],
            [ 60, manager::FREQ_HOUR ],
            [ 180, manager::FREQ_3HOUR ],
            [ 720, manager::FREQ_12HOUR ],
            [ 1440, manager::FREQ_DAY ],
            [ 10080, manager::FREQ_WEEK ],
            [ 16, manager::FREQ_MIN ],
            [ 724, manager::FREQ_MIN ],
            [ 2, manager::FREQ_MIN ],
            [ 90, manager::FREQ_30MIN ],
        ];
    }

    /**
     * Test the execute method
     *
     * @param int $minutesaftermidnight
     * @param array $expected
     * @throws \Exception
     */
    public function test_execute() {
        set_config('activeusers_frequency', manager::FREQ_5MIN, 'tool_cloudmetrics');
        set_config('onlineusers_frequency', manager::FREQ_15MIN, 'tool_cloudmetrics');
        set_config('newusers_frequency', manager::FREQ_HOUR, 'tool_cloudmetrics');
        set_config('activeusers_enabled', 1, 'tool_cloudmetrics');
        set_config('onlineusers_enabled', 1, 'tool_cloudmetrics');
        set_config('newusers_enabled', 1, 'tool_cloudmetrics');

        $tz = \core_date::get_server_timezone_object();
        $time = (new \DateTime("midnight +75 minutes", $tz))->getTimestamp();

        $list = [
            (new online_users_metric())->get_metric_item(),
            (new active_users_metric())->get_metric_item(),
        ];

        $mock = $this->createMock(collect_metrics_task::class);
        $mock->expects($this->once())
            ->method('send_metrics')
            ->with($list);
        $task = new mock_collect_metrics_task($mock);
        $task->set_time($time);
        $task->execute();

        $time = (new \DateTime("midnight +60 minutes", $tz))->getTimestamp();

        $list = [
            (new online_users_metric())->get_metric_item(),
            (new active_users_metric())->get_metric_item(),
            (new new_users_metric())->get_metric_item(),
        ];

        $mock = $this->createMock(collect_metrics_task::class);
        $mock->expects($this->once())
            ->method('send_metrics')
            ->with($list);

        $task = new mock_collect_metrics_task($mock);
        $task->set_time($time);
        $task->execute();
    }
}
