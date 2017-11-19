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
 * NLA users element class.
 *
 * @package     tool_nla
 * @copyright   2017 Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_nla\elements;

defined('MOODLE_INTERNAL') || die();

/**
 * NLA users element class.
 *
 * @package     tool_nla
 * @copyright   2017 Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class users {

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

}