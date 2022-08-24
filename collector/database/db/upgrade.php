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

/**
 * Upgrade script for databases.
 *
 * @package   cltr_database
 * @author    Jason den Dulk <jasondendulk@catalyst-au.net>
 * @copyright 2022, Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Function to upgrade cltr_database.
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool result
 */
function xmldb_cltr_database_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    // Automatically generated Moodle v3.11.0 release upgrade line.
    // Put any upgrade step following this.

    if ($oldversion < 2022082400) {
        // Add date column.
        $table = new xmldb_table('cltr_database_metrics');
        $field = new xmldb_field('date', XMLDB_TYPE_INTEGER, 11, null, null, null, null, 'name');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Add indexes for date and time.
        $table->add_index('dateindex', XMLDB_INDEX_NOTUNIQUE, ['date']);
        $table->add_index('timeindex', XMLDB_INDEX_NOTUNIQUE, ['time']);

        // Fill in date column.
        $tz = \core_date::get_server_timezone_object();
        $records = $DB->get_recordset('cltr_database_metrics', null, '', 'id, time');
        foreach ($records as $record) {
            $record->date = \cltr_database\lib::get_midnight_of($record->time, $tz)->getTimestamp();
            $DB->update_record('cltr_database_metrics', $record);
        }

        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2022082400, 'cltr', 'database');
    }
    return true;
}
