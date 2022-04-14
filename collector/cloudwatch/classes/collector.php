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

namespace cltr_cloudwatch;

use local_aws\local\client_factory;
use tool_cloudmetrics\collector\base;
use tool_cloudmetrics\metric\metric_item;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/aws/sdk/aws-autoloader.php');


/**
 * Collector class for AWS Cloudwatch.
 *
 * @package   cltr_cloudwatch
 * @author    Jason den Dulk <jasondendulk@catalyst-au.net>
 * @copyright 2022, Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class collector extends base {
    protected static $client = null;
    protected static $pluginconfig;

    public function __construct() {
        global $CFG;

        if (is_null(self::$client)) {
            $clientconfig = [
                'region' => get_config('cltr_cloudwatch', 'awsregion'),
                'version' => get_config('cltr_cloudwatch', 'awsversion'),
            ];

            // Add AWS credentials if specified in CFG. IAM role and environment would apply if they are not set.
            if (isset($CFG->forced_plugin_settings['cltr_cloudwatch']['credentials'])) {
                $clientconfig['credentials'] = $CFG->forced_plugin_settings['cltr_cloudwatch']['credentials'];
            }

            self::$client = client_factory::get_client('Aws\CloudWatch\CloudWatchClient', $clientconfig);
            self::$pluginconfig = lib::get_config();
        }
    }

    public function record_metric(metric_item $item) {
        self::$client->putMetricData([
            'Namespace' => self::$pluginconfig->namespace,
            'MetricData' => [
                [
                    'MetricName' => $item->name,
                    'Value' => $item->value,
                    'Unit' => $item->unit ?? 'Count',
                    'Timestamp' => $item->time,
                    'Dimensions' => [
                        [
                            'Name' => 'Environment',
                            'Value' => self::$pluginconfig->environment,
                        ],
                    ],
                ],
            ],
        ]);
    }

    public function is_ready(): bool {
        $plugininfo = \core_plugin_manager::instance()->get_plugin_info('cltr_database');
        if (!$plugininfo->is_enabled()) {
            return false;
        }

        return lib::is_plugin_usable() && !is_null(self::$client);
    }
}
