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

$defaultperiod = optional_param('graphperiod', -1, PARAM_INT);

$metrics = metric\manager::get_metrics(true);
if (empty($metrics)) {
    // Error management if no metrics enabled.
    throw new moodle_exception('no_metrics_enabled', 'cltr_database');
}

$metriclabels = [];
$checkboxes = [];
$displayedmetrics = [];
$notifications = [];

foreach ($metrics as $m) {
    $metriclabels[$m->get_name()] = $m->get_label();

    $metricparam = optional_param($m->get_name(), 0, PARAM_INT);
    if ($metricparam) {
        $displayedmetrics[] = $m->get_name();
    }
    $checkboxes[] = ['checkbox' => html_writer::checkbox($m->get_name(), 1, $metricparam, $m->get_label(),
        ['onchange' => 'this.form.submit()'])];
}

if (empty($displayedmetrics)) {
    $displayedmetrics[] = reset($metrics)->get_name();
    $checkboxes[0] = ['checkbox' => html_writer::checkbox(reset($metrics)->get_name(), 1, true, reset($metrics)->get_label(),
        ['onchange' => 'this.form.submit()'])];
}

if ($defaultperiod === -1) {
    $defaultperiod = get_config('cltr_database', 'chart_period');
    if (!$defaultperiod) {
        $defaultperiod = \cltr_database\lib::period_from_interval($metrics[$displayedmetrics[0]]);
    }
} else {
    set_config('chart_period', $defaultperiod, 'cltr_database');
    \core_plugin_manager::reset_caches();
}

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

$configfrequency = $metrics[$displayedmetrics[0]]->get_frequency();
$selectedfrequency = optional_param('graphfrequency', $configfrequency ?? 1, PARAM_INT);

// Create a new URL object to avoid poisoning the existing one.
$url = clone $url;

foreach ($displayedmetrics as $displayedmetric) {
    $url->param($displayedmetric, 1);
}

$periodurl = clone $url;
$periodurl->param('graphfrequency', $selectedfrequency);

$periodselect = new \single_select(
    $periodurl,
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

$backfillurl = new moodle_url('/admin/tool/cloudmetrics/collector/database/backfill.php', ['metric' => $displayedmetrics[0]]);

$periodselect->set_label(get_string('select_graph_period', 'cltr_database'));

$freqselect->set_label(get_string('select_graph_freq', 'cltr_database'));

$aggregatefreqtimes = lib::FREQ_TIMES;

// TODO Handle a month properly currently aggregated over last 30 days.
$aggregatefreqtimes[4096] = 30 * 24 * 60 * 60;
$aggregatefreqtime = $aggregatefreqtimes[$selectedfrequency];

$maxrecords = 1000;

$values = [];
$labels = [];
$mins = [];
$maxs = [];
$count = 0;
$times = [];
$chart = new chart_line();

$records = $collector->get_metrics_aggregated($displayedmetrics, $defaultperiod, $maxrecords, $aggregatefreqtime);
$lastvaluearr = [];
foreach ($records as $record) {
    foreach ($displayedmetrics as $displayedmetric) {
        $value = !$record->{$displayedmetric} ? null : round($record->{$displayedmetric}, 1);
        $values[$displayedmetric][] = $value;
    }

    $times[] = (int) $record->increment_start;

    if (count($displayedmetrics) == 1) {
        $mins[] = (float)$record->min;
        $maxs[] = (float)$record->max;
    }
    $count++;
}

if ($count) {
    // Insert padding at the end to get the chart to display the full time period.
    $latesttime = time();
    $currenttime = end($times) + $aggregatefreqtime;
    while ($currenttime <= $latesttime && $count < $maxrecords) {
        $times[] = $currenttime;
        foreach ($displayedmetrics as $displayedmetric) {
            $values[$displayedmetric][] = null;
        }
        if (count($displayedmetrics) == 1) {
            $mins[] = null;
            $maxs[] = null;
        }
        $currenttime += $aggregatefreqtime;
        ++$count;
    }

    // Insert padding at the beginning to get the chart to display the full time period.
    $earliesttime = time() - $defaultperiod;
    $currenttime = $times[0] - $aggregatefreqtime;
    while ($currenttime >= $earliesttime && $count < $maxrecords) {
        array_unshift($times, $currenttime);
        foreach ($displayedmetrics as $displayedmetric) {
            array_unshift($values[$displayedmetric], null);
        }
        if (count($displayedmetrics) == 1) {
            array_unshift($mins, null);
            array_unshift($maxs, null);
        }
        $currenttime -= $aggregatefreqtime;
        ++$count;
    }

    // Make human readable labels for the times.

    // If freq 12hr or greater set to UTC.
    $timezone = $CFG->timezone;
    if ($selectedfrequency >= 128) {
        $timezone = 'UTC';
    }

    foreach ($times as $time) {
        if ($selectedfrequency == 4096) {
            // If time increment is month display data at start of month.
            $labels[] = userdate($time + $aggregatefreqtime, get_string('strftimemonth', 'cltr_database'), $timezone);
        } else {
            $labels[] = userdate($time, get_string('strftimedatetime', 'cltr_database'), $timezone);
        }
    }
}

foreach ($displayedmetrics as $displayedmetric) {
    $chartseries = new chart_series($metriclabels[$displayedmetric], $values[$displayedmetric] ?? null);
    $chartseries->set_color($metrics[$displayedmetric]->get_colour());
    $chart->add_series($chartseries);
}

$chart->set_labels($labels);

if (count($displayedmetrics) == 1) {
    $minseries = new chart_series('Minimum '.$metriclabels[$displayedmetrics[0]], $mins);
    $color = $metrics[$displayedmetrics[0]]->get_colour();
    $minseries->set_color($metrics[$displayedmetrics[0]]->get_colour());
    $maxseries = new chart_series('Maximum '.$metriclabels[$displayedmetrics[0]], $maxs);
    $maxseries->set_color($metrics[$displayedmetrics[0]]->get_colour());
    $chart->add_series($minseries);
    $chart->add_series($maxseries);
    $context['backfillable'] = $metrics[$displayedmetrics[0]]->is_backfillable();
    $context['metriclabel'] = $metrics[$displayedmetrics[0]]->get_label();
    $context['metricdescription'] = $metrics[$displayedmetrics[0]]->get_description();
    $context['metriclabeltolower'] = strtolower( $metrics[$displayedmetrics[0]]->get_label());
}

$context['chart'] = $OUTPUT->render($chart);
$context['periodselect'] = $OUTPUT->render($periodselect);
$context['freqselect'] = $OUTPUT->render($freqselect);
$context['backfillurl'] = $backfillurl;
$context['checkboxes'] = $checkboxes;
$context['metriclabel'] = $context['metriclabel'] ?? get_string('multiplemetrics', 'cltr_database');
$context['frequency'] = html_writer::empty_tag('input',
    array('type' => 'hidden', 'name' => 'graphfrequency', 'value' => $selectedfrequency));
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
