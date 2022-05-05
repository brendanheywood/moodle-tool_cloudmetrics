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
 * Collector failure tests for Check API
 *
 * @package tool_cloudmetrics
 * @author Mike Macgirvin (mikemacgirvin@catalyst-au.net)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright Catalyst IT
 */

namespace tool_cloudmetrics\check;

use core\check\check;
use core\check\result;
use tool_cloudmetrics\collector\manager;
use tool_cloudmetrics\plugininfo\cltr;

class collectorcheck extends check {

    /**
     * Constructor
     */
    public function __construct() {
        $this->id = 'checkcollectorcheck';
        $this->name = get_string('checkcollectorcheck', 'tool_cloudmetrics');
    }

    /**
     * A link to a place to action this
     *
     * @return action_link|null
     */
    public function get_action_link(): ?\action_link {
        return new \action_link(
            new \moodle_url('/admin/category.php?category=tool_cloudmetrics_reports'),
            get_string('managelink', 'tool_cloudmetrics'));
    }

    /**
     * Return result
     * @return result
     */
    public function get_result() : result {
        global $CFG;

        $failures = false;
        $warnings = false;
        $messages = [];

        $names = cltr::get_ready_plugin_names();
        foreach ($names as $name) {
            $result = get_config('tool_cloudmetrics', manager::STATUS_PREFIX . $name);
            if (! $result) {
                $warnings = true;
                $messages[] = get_string('collector_never', 'tool_cloudmetrics', $name);
                continue;
            }
            if ($result === 'pass') {
                $messages[] = get_string('collector_passed', 'tool_cloudmetrics', $name);
                continue;
            }
            $failures = true;
            $summarytemplate = get_string('collector_failed', 'tool_cloudmetrics',
                ['name' => $name, 'time' => userdate((int) $result)]);
        }

        if ($messages) {
            $failuretype = result::OK;
            if ($warnings) {
                $failuretype = result::WARNING;
            }
            if ($failures) {
                $failuretype = result::ERROR;
            }
            // This result contains the enumerated detail of each test.
            return new result($failuretype, implode('<br>', $messages));
        }

        return new result(result::INFO, get_string('no_collectors', 'tool_cloudmetrics'), '');
    }


}
