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
 * General metric unit tests.
 *
 * @package     tool_nla
 * @category    phpunit
 * @copyright   2017 Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use tool_nla\analyze\analyze;

class tool_nla_analyze_testcase extends advanced_testcase {

    /**
     * Test get_metrics methods.
     */
    public function test_get_metrics() {
        $analyzer = new analyze();
        $stats = $analyzer->get_metrics();

        $this->assertEquals(1, count($stats));
        $this->assertEquals('last_login_interval', $stats[1]->shortname);

    }

    /**
     * Test process metric for course with no enrolled users.
     */
    public function test_process_metric_no_users() {
        $this->resetAfterTest(true);
        $generator = $this->getDataGenerator();

        // Create course with no enrolled users.
        $course = $generator->create_course();

        $analyzer = new analyze();
        $stats = $analyzer->process_metric('test_metric', $course->id);

        $this->assertEquals(false, $stats);

    }

    /**
     * Test process metric for course with enrolled users.
     */
    public function test_process_metric_users() {
        global $DB;
        $this->resetAfterTest(true);
        $generator = $this->getDataGenerator();

        // Create course with no enrolled users.
        $roleids = $DB->get_records_menu('role', null, '', 'shortname, id');
        $student = $generator->create_user();
        $course = $generator->create_course();
        $generator->enrol_user($student->id, $course->id, $roleids['student']);

        $analyzer = new analyze();
        $stats = $analyzer->process_metric('test_metric', $course->id);

        $this->assertEquals(7, count($stats));

    }

    /**
     * Test process metric for course with enrolled users.
     */
    public function test_time_to_processs() {
        global $DB;
        $this->resetAfterTest(true);
        $generator = $this->getDataGenerator();

        $analyzer = new analyze();
        $stats = $analyzer->process_metric('test_metric', $course->id);

        $this->assertEquals(7, count($stats));

    }
}