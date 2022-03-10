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

/**
 * Intermediary class to provide metric stubs for use in testing.
 *
 * @package    tool_cloudmetrics
 * @author     Jason den Dulk <jasondendulk@catalyst-au.net>
 * @copyright  2022, Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class metric_testcase extends \advanced_testcase {

    /**
     * Returns a test stub for a metric that gives items, cycling through
     * the array of values, repeating when it gets to the end.
     *
     * @param array $cycle
     * @return mixed|\PHPUnit\Framework\MockObject\MockObject|metric_base
     */
    protected function get_metric_stub(array $cycle) {
        $infinate = new \InfiniteIterator(new \ArrayIterator($cycle));
        $infinate->rewind();

        $time = 1;

        $stub = $this->getMockBuilder(metric_base::class)
            ->disableOriginalConstructor()
            ->getMock();

        $stub->method('get_name')
            ->willReturn('mock');

        $stub->method('get_label')
            ->willReturn('Mock');

        $stub->method('get_metric_item')
            ->willReturnCallback(function() use ($stub, $infinate, &$time) {
                $value = $infinate->current();
                $infinate->next();
                return new metric_item('mock', $time++, $value, $stub);
            });

        return $stub;
    }
}
