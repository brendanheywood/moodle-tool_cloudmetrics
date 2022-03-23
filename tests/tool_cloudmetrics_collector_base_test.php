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

use tool_cloudmetrics\collector\base;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . "/metric_testcase.php"); // This is needed. File will not be automatically included.

/**
 * Basic test for collectors.
 */
class tool_cloudmetrics_collector_base_test extends metric_testcase {

    /**
     * Tests ability to mock collector_base.
     */
    public function test_basic() {
        $stub = $this->get_metric_stub([1]);
        $item = $stub->get_metric_item();

        $collectormock = $this->createMock(base::class);
        $collectormock->expects($this->once())
            ->method('record_metric')
            ->with($item);

        $collectormock->record_metric($item);
    }
}
