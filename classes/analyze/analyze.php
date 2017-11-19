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
        global $DB, $SITE;
        $now = time();
        $expiry = $now + 3600;
        $cache = \cache::make('tool_nla', 'values');

        $coursescache = $cache->get('courses');

        if (!$coursescache|| $ignorecache || $coursescache['expiry'] < $now) {
            $sql = 'SELECT DISTINCT c.id, c.shortname
                    FROM {course} c
                    LEFT JOIN {enrol} e
                    ON c.id = e.courseid
                    LEFT JOIN {user_enrolments} ue
                    ON e.id = ue.enrolid
                    LEFT JOIN {user} u
                    ON u.id = ue.userid
                    WHERE c.id <> ?
                    AND c.visible = 1
                    AND (c.startdate = 0 OR c.startdate < ?)
                    AND (c.enddate = 0 OR c.enddate > ?)
                    AND e.status = 0
                    AND ue.status = 0
                    AND u.suspended = 0';
            $courses = $DB->get_records_sql($sql, array($SITE->id, $now, $now));

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
     * @return object $users List of users.
     */
    public function get_users($ignorecache=false) {
        global $DB, $SITE;
        $now = time();
        $expiry = $now + 3600;
        $cache = \cache::make('tool_nla', 'values');

        $userscache = $cache->get('users');

        if (!$userscache|| $ignorecache || $userscache['expiry'] < $now) {
            $sql = 'SELECT DISTINCT u.id, u.lastlogin, u.timecreated
                    FROM {user} u
                    LEFT JOIN {user_enrolments} ue
                    ON u.id = ue.userid
                    LEFT JOIN {enrol} e
                    ON ue.enrolid = e.id
                    LEFT JOIN {course} c
                    ON e.courseid = c.id
                    WHERE c.id <> ?
                    AND c.visible = 1
                    AND (c.startdate = 0 OR c.startdate < ?)
                    AND (c.enddate = 0 OR c.enddate > ?)
                    AND e.status = 0
                    AND ue.status = 0
                    AND u.suspended = 0';

            $users = $DB->get_records_sql($sql, array($SITE->id, $now, $now));

            $userobj = array(
                    'expiry' => $expiry,
                    'courses' => $users
            );
            $cache->set('users', $userobj);
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
        $mean = round(($total / $count), 3);

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

            // Set lowerq1
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

            // Set upperq1
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
        ksort($frequency);  // Sort array by keys.

        $medianarray = $this->calculate_index($count, 0.5);
        $lowerqarray = $this->calculate_index($count, 0.25);
        $upperarray  = $this->calculate_index($count, 0.75);

        $results = $this->calculate_stats($frequency, $total, $count, $medianarray, $lowerqarray, $upperarray);

        return $results;
    }
}