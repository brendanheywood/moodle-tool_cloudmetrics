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

use tool_cloudmetrics\metric\manager;
use tool_cloudmetrics\task\collect_metrics_task;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . "/metric_testcase.php"); // This is needed. File will not be automatically included.

/**
 * A class to help test the collect metrics task. This class is mocked to be able to
 * test against the names of the metrics that have been selected for measurement.
 */
class mock_receiver {
    public function receive(array $names) {
    }
}

/**
 * A class to help test the collect metrics task. This class overrides collect_metrics_task
 * so that instead of sending th emetric items to the colletors, it passes it to a
 * mock receiver class instead.
 */
class helper_collect_metrics_task extends collect_metrics_task {
    public function __construct(mock_receiver $mock) {
        $this->mock = $mock;
    }

    /**
     * In this test, we are not interested in the times or values of the items, only the names. So we take out
     * the names and pass them on the mock class which checks them.
     *
     * @param array $items
     */
    public function send_metrics(array $items) {
        $names = [];
        foreach ($items as $item) {
            $names[] = $item->name;
        }
        $this->mock->receive($names);
    }
}

/**
 * Test for collect_metrics_task.
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
     * Test the execute method.
     *
     * @dataProvider execute_provider
     * @param int $time
     * @param array $expected
     * @throws \Exception
     */
    public function test_execute($timestr, $freqs, $expected) {
        set_config('activeusers_frequency', $freqs[0], 'tool_cloudmetrics');
        set_config('newusers_frequency', $freqs[1], 'tool_cloudmetrics');
        set_config('onlineusers_frequency', $freqs[2], 'tool_cloudmetrics');
        set_config('activeusers_enabled', 1, 'tool_cloudmetrics');
        set_config('newusers_enabled', 1, 'tool_cloudmetrics');
        set_config('onlineusers_enabled', 1, 'tool_cloudmetrics');

        $tz = \core_date::get_server_timezone_object();
        $time = (new \DateTime($timestr, $tz))->getTimestamp();

        $mock = $this->createMock(mock_receiver::class);
        $mock->expects($this->once())
            ->method('receive')
            ->with($expected);
        $task = new helper_collect_metrics_task($mock);
        $task->set_time($time);
        $task->execute();
    }

    /**
     * Provider function for test_execute.
     *
     * @return array[] Each element contains a time string, a list of frequency settings,
     *                 and a list of metrics that should be measured.
     */
    public function execute_provider(): array {
        return [
            [
                'midnight +75 minutes',
                [manager::FREQ_15MIN, manager::FREQ_5MIN, manager::FREQ_HOUR],
                ['activeusers', 'newusers']
            ],
            [
                'midnight +60 minutes',
                [manager::FREQ_15MIN, manager::FREQ_5MIN, manager::FREQ_HOUR],
                ['activeusers', 'newusers', 'onlineusers']
            ],
            [
                'midnight this month',
                [manager::FREQ_MONTH, manager::FREQ_15MIN, manager::FREQ_HOUR],
                ['activeusers', 'newusers', 'onlineusers']
            ],
            [
                'midnight this month -10 seconds',
                [manager::FREQ_MONTH, manager::FREQ_15MIN, manager::FREQ_HOUR],
                ['activeusers', 'newusers', 'onlineusers']
            ],
            [
                'midnight this month +15 minutes',
                [manager::FREQ_MONTH, manager::FREQ_15MIN, manager::FREQ_HOUR],
                ['newusers']
            ],
        ];
    }
}
