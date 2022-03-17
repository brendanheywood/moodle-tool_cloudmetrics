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

namespace cltr_database;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . "/../../../tests/metric_testcase.php"); // This is needed. File will not be automatically included.


/**
 * Unit test for database collector
 *
 * @package   cltr_database
 * @author    Jason den Dulk <jasondendulk@catalyst-au.net>
 * @copyright 2022, Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cltr_database_test extends \tool_cloudmetrics\metric_testcase {

    /**
     * Set up before each test
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
    }

    public function test_collector() {
        global $DB;

        $stub = $this->get_metric_stub([1, 2, 3]);
        $collector = new collector();

        $rec = $DB->get_records(lib::TABLE);
        $this->assertEquals(0, count($rec));

        $collector->record_metric($stub->get_metric_item());

        // Should have one metric of value 1.
        $rec = array_values($DB->get_records(lib::TABLE));
        $this->assertEquals(1, count($rec));
        $this->assertEquals('mock', $rec[0]->name);
        $this->assertEquals('1', $rec[0]->value);

        $collector->record_metric($stub->get_metric_item());

        // Should have two metrics of values 1 & 2.
        $rec = array_values($DB->get_records(lib::TABLE, null, 'time ASC'));
        $this->assertEquals(2, count($rec));
        $this->assertEquals('mock', $rec[0]->name);
        $this->assertEquals('1', $rec[0]->value);
        $this->assertEquals('mock', $rec[1]->name);
        $this->assertEquals('2', $rec[1]->value);

        $collector->record_metric($stub->get_metric_item());
        $collector->record_metric($stub->get_metric_item());

        // Should have four metrics of values 1, 2, 3 & 1.
        $rec = array_values($DB->get_records(lib::TABLE, null, 'time ASC'));
        $this->assertEquals(4, count($rec));
        $this->assertEquals('1', $rec[0]->value);
        $this->assertEquals('2', $rec[1]->value);
        $this->assertEquals('3', $rec[2]->value);
        $this->assertEquals('1', $rec[3]->value);
    }

    public function test_expiry() {
        global $DB;

        $timeago = time() - (int)(19.5 * DAYSECS); // 19.5 days ago.
        $stub = $this->get_metric_stub([1, 2, 3], $timeago, DAYSECS);
        $collector = new collector();

        for ($i = 0; $i < 20; ++$i) {
            $collector->record_metric($stub->get_metric_item());
        }

        // There should be 20 items in the database.
        $count = $DB->count_records(lib::TABLE);
        $this->assertEquals(20, $count);

        // We want to remove all data recorded as more than 10 days old.
        set_config('metric_expiry', 10 * DAYSECS, 'cltr_database');

        $task = new \cltr_database\task\metrics_cleanup_task();
        $task->execute();

        // There should now be only 10 items in the database.
        $count = $DB->count_records(lib::TABLE);
        $this->assertEquals(10, $count);
    }
}
