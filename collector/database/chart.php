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

use tool_cloudmetrics\metric;

/**
 * Shows a chart of recorded metrics.
 *
 * @package   cltr_database
 * @author    Jason den Dulk <jasondendulk@catalyst-au.net>
 * @copyright 2022, Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__.'/../../../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('cltr_database_chart');

$context = context_system::instance();

$url = new moodle_url('/admin/tool/cloudmetrics/collector/database/chart.php');

$PAGE->set_context($context);
$PAGE->set_url($url);

$metricname = optional_param('metric', 'activeusers', PARAM_ALPHANUMEXT);

if (intval($_REQUEST['graphperiod'])) {
    set_config($metricname . '_chart_period', intval($_REQUEST['graphperiod']), 'tool_cloudmetrics');
    \core_plugin_manager::reset_caches();
    $defaultperiod = intval($_REQUEST['graphperiod']);
} else {
    $defaultperiod = get_config('tool_cloudmetrics', $metricname . '_chart_period');
    if (!$defaultperiod) {
        $defaultperiod = metric\lib::period_from_interval($metricname);
    }
}

$metrics = metric\manager::get_metrics(true);
$metriclabels = [];
foreach ($metrics as $m) {
    $metriclabels[$m->get_name()] = $m->get_label();
    if ($m->get_name() == $metricname) {
        $metric = $m;
    }
}

$select = new \single_select(
    $url,
    'metric',
    $metriclabels,
    $metricname
);
$select->set_label(get_string('select_metric_for_display', 'cltr_database'));

// Prepare time window selector.

$periods = [
    3600 => get_string('one_hour', 'tool_cloudmetrics'),
    DAYSECS => get_string('one_day', 'tool_cloudmetrics'),
    DAYSECS * 7 => get_string('one_week', 'tool_cloudmetrics'),
    DAYSECS * 30 => get_string('one_month', 'tool_cloudmetrics'),
    DAYSECS * 120 => get_string('four_month', 'tool_cloudmetrics'),
    DAYSECS * 365 => get_string('twelve_month', 'tool_cloudmetrics')
];

$periodselect = new \single_select(
    $url,
    'graphperiod',
    $periods,
    $defaultperiod
);
$periodselect->set_label(get_string('select_graph_period', 'cltr_database'));

$collector = new \cltr_database\collector();
$records = $collector->get_metrics($metricname, $defaultperiod);

$values = [];
$labels = [];

foreach ($records as $record) {
    $values[] = (float) $record->value;
    $labels[] = userdate($record->time, get_string('strftimedatetime', 'cltr_database'), $CFG->timezone);
}

$chartseries = new \core\chart_series($metriclabels[$metricname], $values);

$chart = new \core\chart_line();
$chart->add_series($chartseries);
$chart->set_labels($labels);

echo $OUTPUT->header();
echo $OUTPUT->render($select);
echo html_writer::empty_tag('br');
echo $OUTPUT->render($periodselect);
if (isset($metric)) {
    echo html_writer::tag('h3', $metric->get_label());
    echo html_writer::tag('p', $metric->get_description());
}
echo $OUTPUT->render($chart);
echo $OUTPUT->footer();
