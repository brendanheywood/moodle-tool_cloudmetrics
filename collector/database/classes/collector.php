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

use tool_cloudmetrics\metric\metric_item;
use tool_cloudmetrics\collector\base;
use tool_cloudmetrics\metric;

/**
 * Collector class for the internal database.
 *
 * @package   cltr_database
 * @author    Jason den Dulk <jasondendulk@catalyst-au.net>
 * @copyright 2022, Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class collector extends base {
    public function record_metric(metric_item $item) {
        global $DB;

        $DB->insert_record(
            lib::TABLE,
            ['name' => $item->name, 'value' => $item->value, 'time' => $item->time]
        );
    }

    /**
     * Returns stored metrics for the collector.
     *
     * @param mixed $metricnames The metrics to be retrieved. Either a single string, or an
     *         array of strings. If empty, then all available metrics will be retrieved.
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function get_metrics($metricnames = null): array {
        global $DB;

        if (is_null($metricnames)) {
            $metricnames = [];
            $metrics = metric\manager::get_metrics(true);
            foreach ($metrics as $metric) {
                $metricnames[] = $metric->get_name();
            }
        } else if (is_string($metricnames)) {
            $metricnames = [$metricnames];
        }
        list ($clause, $params) = $DB->get_in_or_equal($metricnames);
        $sql = "SELECT id, name, time, value
                  FROM {cltr_database_metrics}
                 WHERE name $clause
               ORDER BY time asc";
        return $DB->get_records_sql($sql, $params);
    }
}
