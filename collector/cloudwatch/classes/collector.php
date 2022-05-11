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
                'version' => lib::AWS_VERSION,
            ];

            // Add AWS credentials if specified in CFG. IAM role and environment would apply if they are not set.
            $awskey = get_config('cltr_cloudwatch', 'aws_key');
            if (!empty($awskey)) {
                $clientconfig['credentials'] = [
                    'key' => $awskey,
                    'secret' => get_config('cltr_cloudwatch', 'aws_secret')
                ];
            }

            self::$client = client_factory::get_client('Aws\CloudWatch\CloudWatchClient', $clientconfig);
            self::$pluginconfig = lib::get_config();
        }
    }

    public function record_metric(metric_item $item) {
        self::$client->putMetricData([
            'Namespace' => self::$pluginconfig->namespace,
            'MetricData' => [ $this->make_metric_data_entry($item) ],
        ]);
    }

    public function record_metrics(array $items) {
        $metricdata = [];
        foreach ($items as $item) {
            $metricdata[] = $this->make_metric_data_entry($item);
        }
        self::$client->putMetricData([
            'Namespace' => self::$pluginconfig->namespace,
            'MetricData' => $metricdata,
        ]);
    }

    /**
     * Creates a metric data array from an item for use with the Cloudwatch API.
     *
     * @param metric_item $item
     * @return array
     */
    private function make_metric_data_entry(metric_item $item): array {
        return [
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
        ];
    }

    public function is_ready(): bool {
        $plugininfo = \core_plugin_manager::instance()->get_plugin_info('cltr_cloudwatch');
        if (!$plugininfo->is_enabled()) {
            return false;
        }

        return lib::is_plugin_usable() && !is_null(self::$client);
    }
}
