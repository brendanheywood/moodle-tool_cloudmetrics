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

/**
 * General functions used by plugin
 *
 * @package   cltr_database
 * @author    Jason den Dulk <jasondendulk@catalyst-au.net>
 * @copyright 2022, Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class lib {

    // TODO: Is there a better way to get this?
    /** @var string Root directory of the plugin. */
    const PLUGIN_DIR = '/admin/tool/cloudmetrics/collector/database/';

    /** @var string Name of database table. */
    const TABLE = 'cltr_database_metrics';

    /** @var int Number of seconds in one day. */
    const SECS_IN_DAY = 86400;

    /**
     * Returns the expiry time for metric data in seconds. Enforces a minimum of one day.
     *
     * @return int
     * @throws \dml_exception
     */
    public static function get_metric_expiry(): int {
        $expiry = (int)get_config('cltr_database', 'metric_expiry') * self::SECS_IN_DAY;
        if ($expiry <= self::SECS_IN_DAY) {
            return self::SECS_IN_DAY;
        }
        return $expiry;
    }
}

