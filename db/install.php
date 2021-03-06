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
 * Install code for NLA.
 *
 * @package     tool_nla
 * @copyright   2017 Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;


/**
 * Perform the post-install procedures.
 * This adds the metrics to the database
 */
function xmldb_tool_nla_install() {
    global $DB;

    $metric1 = new stdClass();
    $metric1->shortname = 'last_login_interval';
    $metric1->longname = 'Last Login Interval';
    $metric1->description = 'Time since the user last logged into the system';
    $metric1->gethistory = 0;
    $metric1->enabled = 1;
    $metric1->lastrun = 0;

    $metrics = array($metric1);

    $DB->insert_records('tool_nla_metrics', $metrics);

}
