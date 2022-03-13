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

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . "/metric_testcase.php"); // This is needed. File will not be automatically included.

/**
 * Basic test for collectors.
 */
class tool_cloudmetrics_metric_stub_test extends metric_testcase {

    public function test_get_stub() {
        $stub = $this->get_metric_stub([1, 2, 3]);

        $i = $stub->get_metric_item();
        $this->assertEquals(1, $i->value);
        $this->assertEquals(1, $i->time);

        $i = $stub->get_metric_item();
        $this->assertEquals(2, $i->value);
        $this->assertEquals(2, $i->time);

        $i = $stub->get_metric_item();
        $this->assertEquals(3, $i->value);
        $this->assertEquals(3, $i->time);

        $i = $stub->get_metric_item();
        $this->assertEquals(1, $i->value);
        $this->assertEquals(4, $i->time);
    }
}
