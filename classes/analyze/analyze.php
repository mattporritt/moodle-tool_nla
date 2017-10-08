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
     * @return boolean
     */
    public function get_courses() {
        global $DB, $SITE;
        $now = time();

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

        return $courses;
    }

}