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
 * Shows a chart of recorded metrics.
 *
 * @package   cltr_database
 * @author    Jason den Dulk <jasondendulk@catalyst-au.net>
 * @copyright 2022, Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\chart_line;
use core\chart_series;
use tool_cloudmetrics\lib;
use tool_cloudmetrics\metric;
use tool_cloudmetrics\metric\manager;

require_once(__DIR__.'/../../../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('cltr_database_chart');

$context = context_system::instance();

$url = new moodle_url('/admin/tool/cloudmetrics/collector/database/chart.php');

$PAGE->set_context($context);
$PAGE->set_url($url);

$metricname = optional_param('metric', 'activeusers', PARAM_ALPHANUMEXT);

$defaultperiod = optional_param('graphperiod', -1, PARAM_INT);
if ($defaultperiod === -1) {
    $defaultperiod = get_config('tool_cloudmetrics', $metricname . '_chart_period');
    if (!$defaultperiod) {
        $defaultperiod = metric\lib::period_from_interval($metricname);
    }
} else {
    set_config($metricname . '_chart_period', $defaultperiod, 'tool_cloudmetrics');
    \core_plugin_manager::reset_caches();
}

$metrics = metric\manager::get_metrics(true);
// Error management if metric is not enabled.
if (!isset($metrics[$metricname])) {
    throw new moodle_exception('metric_not_enabled', 'tool_cloudmetrics', '', $metricname);
}
$metriclabels = [];
foreach ($metrics as $m) {
    $metriclabels[$m->get_name()] = $m->get_label();
    if ($m->get_name() == $metricname) {
        $metric = $m;
    }
}

$select = new single_select(
    $url,
    'metric',
    $metriclabels,
    $metricname
);
$select->set_label(get_string('select_metric_for_display', 'cltr_database'));
$context = [];

// Prepare time window selector.
$periods = [
    HOURSECS      => get_string('one_hour', 'tool_cloudmetrics'),
    DAYSECS       => get_string('one_day', 'tool_cloudmetrics'),
    WEEKSECS      => get_string('one_week', 'tool_cloudmetrics'),
    WEEKSECS * 2  => get_string('two_week', 'tool_cloudmetrics'),
    DAYSECS * 30  => get_string('one_month', 'tool_cloudmetrics'),
    DAYSECS * 61  => get_string('two_month', 'tool_cloudmetrics'),
    DAYSECS * 122 => get_string('four_month', 'tool_cloudmetrics'),
    DAYSECS * 183 => get_string('six_month', 'tool_cloudmetrics'),
    YEARSECS      => get_string('twelve_month', 'tool_cloudmetrics'),
    YEARSECS * 2  => get_string('two_year', 'tool_cloudmetrics'),
];

$collector = new \cltr_database\collector();

$configfrequency = $metrics[$metricname]->get_frequency();
$selectedfrequency = optional_param('graphfrequency', $configfrequency ?? 1, PARAM_INT);

// Create a new URL object to avoid poisoning the existing one.
$url = clone $url;
$url->param('metric', $metricname);
$url->param('graphfrequency', $selectedfrequency);
$periodselect = new \single_select(
    $url,
    'graphperiod',
    $periods,
    $defaultperiod
);

$freqoptions = manager::get_frequency_labels();
$freqselect = new \single_select(
    $url,
    'graphfrequency',
    $freqoptions,
    $selectedfrequency
);

$backfillurl = new moodle_url('/admin/tool/cloudmetrics/collector/database/backfill.php', ['metric' => $metricname]);

$periodselect->set_label(get_string('select_graph_period', 'cltr_database'));

$freqselect->set_label(get_string('select_graph_freq', 'cltr_database'));

$aggregatefreqtimes = lib::FREQ_TIMES;

// TODO Handle a month properly currently aggregated over last 30 days.
$aggregatefreqtimes[4096] = 30 * 24 * 60 * 60;

$maxrecords = 1000;

$values = [];
$labels = [];
$mins = [];
$maxs = [];
$count = 0;

$records = $collector->get_metrics_aggregated($metricname, $defaultperiod, $maxrecords, $aggregatefreqtimes[$selectedfrequency]);
foreach ($records as $record) {
    $values[] = round($record->avg, 1);

    // Convert back from floored time.
    $datelabel = ($record->increment_start * $aggregatefreqtimes[$selectedfrequency]);

    // If freq 12hr or greater set to UTC.
    $timezone = $CFG->timezone;
    if ($selectedfrequency >= 128) {
        $timezone = 'UTC';
    }
    // If time increment is month display data at start of month.
    if ($selectedfrequency == 4096) {
        $datelabel += $aggregatefreqtimes[$selectedfrequency];
        $labels[] = userdate($datelabel, get_string('strftimemonth', 'cltr_database'), $timezone);
    } else {
        $labels[] = userdate($datelabel, get_string('strftimedatetime', 'cltr_database'), $timezone);
    }
    $mins[] = (float) $record->min;
    $maxs[] = (float) $record->max;
    $count++;
}
$chartseries = new chart_series($metriclabels[$metricname], $values);
$chartseries->set_color($metric->get_colour());
$chart = new chart_line();
$chart->add_series($chartseries);

$minseries = new chart_series('Minimum '.$metriclabels[$metricname], $mins);
$minseries->set_color($metric->get_colour());
$maxseries = new chart_series('Maximum '.$metriclabels[$metricname], $maxs);
$maxseries->set_color($metric->get_colour());
$chart->add_series($minseries);
$chart->add_series($maxseries);

$chart->set_labels($labels);

$context['chart'] = $OUTPUT->render($chart);
$context['selector'] = $OUTPUT->render($select);
$context['periodselect'] = $OUTPUT->render($periodselect);
$context['freqselect'] = $OUTPUT->render($freqselect);
$context['backfillurl'] = $backfillurl;
$context['backfillable'] = $metrics[$metricname]->is_backfillable();
$context['metriclabel'] = $metric->get_label();
$context['metricdescription'] = $metrics[$metricname]->get_description();
$context['metriclabeltolower'] = strtolower($metric->get_label());

$renderer = $PAGE->get_renderer('tool_cloudmetrics');

echo $OUTPUT->header();
if ($count == 0) {
    echo $OUTPUT->notification(get_string('norecords', 'cltr_database', $maxrecords), 'notifyproblem');
} else {
    if ($count === $maxrecords) {
        echo $OUTPUT->notification(get_string('maxrecords', 'cltr_database', $maxrecords), 'notifyproblem');
    }
    if ($selectedfrequency != $configfrequency) {
        echo $OUTPUT->notification(get_string('aggregated', 'cltr_database', $freqoptions[$selectedfrequency]), 'notifysuccess');
    }
}
echo $renderer->render_chart_page($context);
echo $OUTPUT->footer();
