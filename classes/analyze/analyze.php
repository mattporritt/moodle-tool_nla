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
 * Forum sentiment analyzer class.
 *
 * @package     tool_sentiment_forum
 * @copyright   2017 Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_nla\analyze;

defined('MOODLE_INTERNAL') || die();

/**
 * Analytic analyzer class.
 *
 * @package     tool_nla
 * @copyright   2017 Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class analyze {

    private $interval = 60 * 60 * 24 * 7; // One week.

    /**
     * Constructor for analyzer.
     * Makes relevant config available.
     *
     * @return void
     */
    public function __construct() {
        $this->config = get_config('tool_nla');
        $this->metrics = $this->get_metrics();
    }

    private function show_hidden_courses() {
        if ($this->config->hiddencourses == 1) {
            $visible = false;
        } else {
            $visible = true;
        }

        return $visible;
    }

    private function respect_dates() {
        if ($this->config->startend == 1) {
            $dates = true;
        } else {
            $dates = false;
        }

        return $dates;
    }

    /**
     * Get the enabled metrics and their settings from the database.
     *
     * @return array $metrics Array of metrics
     */
    public function get_metrics() {
        global $DB;

        $metrics = $DB->get_records('tool_nla_metrics', array('enabled' => 1), 'shortname ASC');

        return $metrics;
    }

    /**
     *
     * @param unknown $metricname
     * @param unknown $courseid
     * @param number $interval
     * @param number $now
     * @return boolean
     */
    public function time_to_process($metricname, $courseid, $interval=0, $now=0) {
        global $DB;
        $process = false;

        if ($interval == 0) {
            $interval = $this->interval;
        }

        if ($now == 0) {
            $now = time();
        }

        // Get last run time for metric from DB
        $conditions = array('metricshortname' => $metricname, 'courseid' => $courseid);
        $lastrun = $DB->get_field('tool_nla_metrics_course', 'lastrun', $conditions);
        $nextrun = $lastrun + $interval;

        // If now is greater or equal to last run + inteval return true.
        if ($now >= $nextrun) {
            $process = true;
        }

        return $process;
    }

    /**
     * Get available courses to analyze.
     * Results are cached to improve performance.
     *
     * This method returns courses:
     * That aren’t hidden.
     * That aren’t the site course.
     * That aren’t in a hidden category.
     * That have at least one active enrollment.
     * Where enrolled user is also active.
     * Where course start date is less than now.
     * Where course end date is greater than now.
     *
     * @param bool $ignorecache If true don't use caches courses.
     * @return object $courses List of courses.
     */
    public function get_courses($ignorecache=false) {
        global $DB;

        $now = time();
        $expiry = $now + 3600;
        $cache = \cache::make('tool_nla', 'courses');

        $coursescache = $cache->get('courses');

        if (!$coursescache|| $ignorecache || $coursescache['expiry'] < $now) {
            $select = '';
            $params = array();

            if (!$this->show_hidden_courses()) { // Are we showing hidden courses?
                $select = 'visible = :visible';
                $params['visible'] = 1;
            }

            if ($this->respect_dates()) { // Are courses by start and end date?
                if ($select) {
                    $select .= 'AND startdate <= :startdate AND enddate >= :enddate';
                } else {
                    $select = 'startdate <= :startdate AND enddate >= :enddate';
                }

                $params['startdate'] = $now;
                $params['enddate'] = $now;
            }

            $courses = $DB->get_records_select('course', $select, $params, 'id ASC', 'id');

            $courseobj = array(
                    'expiry' => $expiry,
                    'courses' => $courses
            );
            $cache->set('courses', $courseobj);
        } else {
            $courses = $coursescache['courses'];
        }

        return $courses;
    }
    /**
     * Get available users to analyze.
     * Results are cached to improve performance.
     *
     * Get users that aren’t suspended.
     * Get users that aren’t deleted.
     * Get users that have at least one active course enrolment.
     *
     * @param bool $ignorecache If true don't use caches.
     * @return array $users List of users.
     */
    public function get_users($courseid, $ignorecache=false) {
        global $DB, $SITE;
        $now = time();
        $expiry = $now + 3600;
        $cache = \cache::make('tool_nla', 'users');

        $userscache = $cache->get($courseid);

        if (!$userscache|| $ignorecache || $userscache['expiry'] < $now) {

            $coursecontext = \context_course::instance($courseid);
            $users = get_enrolled_users($coursecontext, '', 0, 'u.id, u.lastlogin', null, 0, 0, true);

            $userobj = array(
                    'expiry' => $expiry,
                    'users' => $users
            );
            $cache->set($courseid, $userobj);
        } else {
            $users = $userscache['users'];
        }

        return $users;
    }

    /**
     *
     * @param unknown $count
     * @param unknown $multiplier
     * @return number[]
     */
    private function calculate_index(&$count, $multiplier) {
        $value1 = ($count + 1) * $multiplier;
        $value2 = 0;

        if (!is_int($value1)) {
            // Need two values.
            $value2 = round($value1, 0, PHP_ROUND_HALF_UP);
            $value1 = round($value1, 0, PHP_ROUND_HALF_DOWN);
        }

        return array($value1, $value2);
    }

    /**
     *
     * @param unknown $frequency
     * @param unknown $total
     * @param unknown $count
     * @param unknown $medianarray
     * @param unknown $lowerqarray
     * @param unknown $upperarray
     * @return number[]|mixed[]|unknown[]
     */
    private function calculate_stats(&$frequency, $total, $count, $medianarray, $lowerqarray, $upperarray) {
        $minimum = 0;
        $maximum = 0;
        $mean = 0;
        $median = 0;
        $lowerq = 0;
        $upperq = 0;
        $interquartilerange = 0;

        // Calculate minimum value.
        $minimum = key($frequency);

        // Calculate maximum value.
        end($frequency);
        $maximum = key($frequency);
        reset($frequency);

        // Calculate mean value.
        if ($count != 0 ) {
            $mean = round(($total / $count), 3);
        } else {
            $mean = 0;
        }
        // Get median index values.
        list($medianindex1, $medianindex2) = $medianarray;

        // Get lower quartile index values.
        list($lowerqindex1, $lowerqindex2) = $lowerqarray;

        // Get upper quartile index values.
        list($upperqindex1, $upperqindex2) = $upperarray;
        $ncount = 0;
        $median1 = 0;
        $median2 = 0;
        $lowerq1 = 0;
        $lowerq2 = 0;
        $upperq1 = 0;
        $upperq2 = 0;

        foreach ($frequency as $key => $value) {
            $ncount += $value;

            // Set lowerq1.
            if (($ncount >= $lowerqindex1) && $lowerq1 == 0) {
                $lowerq1 = $key;
            }

            // Set lowerq2.
            if (($ncount >= $lowerqindex2) && $lowerq2 == 0) {
                $lowerq2 = $key;
            }

            // Set median1.
            if (($ncount >= $medianindex1) && $median1 == 0) {
                $median1 = $key;
            }

            // Set median2.
            if (($ncount >= $medianindex2) && $median2 == 0) {
                $median2 = $key;
            }

            // Set upperq1.
            if (($ncount >= $upperqindex1) && $upperq1 == 0) {
                $upperq1 = $key;
            }

            // Set upperq2.
            if (($ncount >= $upperqindex2) && $upperq2 == 0) {
                $upperq2 = $key;
            }

            if (($lowerq1 != 0 && $lowerqindex2 == 0) && $lowerq == 0) {
                $lowerq = $lowerq1;
            } else if (($lowerq1 != 0 && $lowerq2 != 0) && $lowerq == 0) {
                $lowerq = ($lowerq1 + $lowerq2) / 2;

            }

            if (($median1 != 0 && $medianindex2 == 0) && $median == 0) {
                $median = $median1;
            } else if (($median1 != 0 && $median2 != 0) && $median == 0) {
                $median = ($median1 + $median2) / 2;
            }

            // Exit if we have what we need.
            // We make the assumption that if we have the
            // upper quartile we also have the lower quartile
            // and the median.
            if (($upperq1 != 0 && $upperqindex2 == 0) && $upperq == 0) {
                $upperq = $upperq1;
                break;
            } else if (($upperq1 != 0 && $upperq2 != 0) && $upperq == 0) {
                $upperq = ($upperq1 + $upperq2) / 2;
                break;
            }
        }

        $interquartilerange = $upperq - $lowerq;

        $results = array(
                $minimum,
                $maximum,
                $mean,
                $median,
                $lowerq,
                $upperq,
                $interquartilerange
        );

        return $results;
    }

    /**
     *
     * @param iterator $metric Metric values to calculate
     */
    public function get_stats($metric) {

        $count = 0;
        $total = 0;

        $frequency = array();
        foreach ($metric as $value) {

            if (array_key_exists($value, $frequency)) {
                $frequency[$value] = $frequency[$value] + 1;
            } else {
                $frequency[$value] = 1;
            }
            $count++;
            $total += $value;
        }

        if ($frequency) {
            ksort($frequency);  // Sort array by keys.

            $medianarray = $this->calculate_index($count, 0.5);
            $lowerqarray = $this->calculate_index($count, 0.25);
            $upperarray  = $this->calculate_index($count, 0.75);

            $results = $this->calculate_stats($frequency, $total, $count, $medianarray, $lowerqarray, $upperarray);
        } else {
            $results = array(0, 0, 0, 0, 0, 0, 0);
        }

        return $results;
    }

    /**
     *
     * @param unknown $metricname
     * @param number $interval
     */
    public function process_metric($metricname, $courseid) {
        // If it is not time to process this metric for this course return early.
        $processtime = $this->time_to_process($metricname, $courseid);
        if (!$processtime) {
            return false;
        }

        $users = $this->get_users($courseid);

        if (!empty($users)) {
            $classname = "tool_nla\\metrics\\" . $metricname;
            $metric = new $classname($users);
            $stats = $this->get_stats($metric);
        } else { // No users found, so return false.
            $stats = false;
        }

        return $stats;

        // If it is time to process metric.
            // Get metric iterator based on metric shortname.
            // Get stats for metric.
            // Save stats to database.

        // If we are calculating history for metric.
            // Find out where we are up to in history processing.
            // Create an adhoc task to process next lot of history (if required).
    }

    /**
     *
     */
    public function process_metrics() {

        foreach ($metrics as $metricname) {
            $courses = $this->get_courses();
            foreach ($courses as $course) {
                $stats = $this->process_metric($metricname, $course->id);
            }
        }

    }

}