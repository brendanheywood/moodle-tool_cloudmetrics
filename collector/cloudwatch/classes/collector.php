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

use tool_cloudmetrics\metric\metric_item;
use tool_cloudmetrics\collector\base;
use tool_cloudmetrics\metric;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/aws/sdk/aws-autoloader.php');
use Aws\CloudWatch\CloudWatchClient;
use local_aws\local\client_factory;

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
    protected static $clientconfig;
    protected static $pluginconfig;

    public function __construct() {
        global $CFG;

        if (is_null(self::$client)) {
            $clientconfig = [
                'region' => get_config('cltr_cloudwatch', 'awsregion'),
                'version' => get_config('cltr_cloudwatch', 'awsversion'),
            ];

            // Add AWS credentials if specified in CFG. IAM role and environment would apply if they are not set.
            if (isset($CFG->cltr_cloudwatch['credentials'])) {
                $clientconfig['credentials'] = $CFG->cltr_cloudwatch['credentials'];
            }

            self::$client = client_factory::get_client('Aws\CloudWatch\CloudWatchClient', $clientconfig);
            self::$pluginconfig = get_config('cltr_cloudwatch');
        }
    }

    public function record_metric(metric_item $item) {
        $namespace = self::$pluginconfig->namespace;
        $environment = self::$pluginconfig->environment;

        self::$client->putMetricData([
            'Namespace' => $namespace,
            'MetricData' => [
                [
                    'MetricName' => $item->name,
                    'Value' => $item->value,
                    'Unit' => $item->unit ?? 'Count',
                    'Timestamp' => $item->time,
                    'Dimensions' => [
                        [
                            'Name' => 'Environment',
                            'Value' => $environment,
                        ],
                    ],
                ],
            ],
        ]);
    }

    public function is_ready(): bool {
        return !is_null(self::$client);
    }

    /**
     * Retrieve metric data from the collector.
     *
     * @param mixed $metrics The metrics to be retrieved. Either a single string, or an
     *         array of strings. If null, then all available metrics will be retrieved.
     * @return array|bool The metric records. Returns false if it cannot return metric data.
     */
    public function get_metrics($metricnames = null) {
        $metriccandidates = metric\manager::get_metrics(true);
        if (empty($metricnames)) {
            $metrics = $metriccandidates;
        } else {
            $metrics = [];
            if (is_string($metricnames)) {
                $metricnames = [$metricnames];
            }
            foreach ($metriccandidates as $metric) {
                if (in_array($metricnames, $metric->get_name())) {
                    $metrics[] = $metric;
                }
            }
        }

        $dataqueries = [];
        foreach ($metrics as $metric) {
            $dataqueries[] = $this->get_metric_data_query($metric);
        }
        $parameters = [
            'MetricDataQueries' => $dataqueries,
            'StartTime' => strtotime('-5 days'),
            'EndTime' => strtotime('today'),
        ];
        var_dump($parameters);
        return self::$client->getMetricData($parameters);
    }

    private function get_metric_data_query(metric\base $metric) {
        $namespace = self::$pluginconfig->namespace;
        $environment = self::$pluginconfig->environment;
        return [
            'Id' => $metric->get_name(),
            'MetricStat' => [
                'Metric' => [
                    'Dimensions' => [
                        [
                            'Name' => 'Environment',
                            'Value' => $environment,
                        ],
                    ],
                    'MetricName' => $metric->get_name(),
                    'Namespace' => $namespace,
                ],
                'Period' => 30,
                'Stat' => 'Average',
                'Unit' => $metric->unit ?? 'Count',
            ],
            'ReturnData' => true
        ];
    }
}
