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
     * Test time to process method when there are no records in DB.
     * This occurs on initial condition when metric has never ran before.
     */
    public function test_time_to_process_no_record() {
        global $DB;
        $this->resetAfterTest(true);
        $generator = $this->getDataGenerator();

        $course = $generator->create_course();

        $analyzer = new analyze();
        $process = $analyzer->time_to_process('test_metric', $course->id);

        $this->assertEquals(true, $process);

    }

    /**
     * Test time to process method when it is time to process.
     */
    public function test_time_to_process_time() {
        global $DB;
        $this->resetAfterTest(true);
        $generator = $this->getDataGenerator();

        $course = $generator->create_course();
        $record = new \stdClass();
        $record->metricshortname = 'test_metric';
        $record->courseid = $course->id;
        $record->lastrun = 0;

        $DB->insert_record('tool_nla_metrics_course', $record);

        $analyzer = new analyze();
        $process = $analyzer->time_to_process('test_metric', $course->id);

        $this->assertEquals(true, $process);

    }

    /**
     * Test time to process method when it is not time to process.
     */
    public function test_time_to_process_not_time() {
        global $DB;
        $this->resetAfterTest(true);
        $generator = $this->getDataGenerator();

        $course = $generator->create_course();
        $record = new \stdClass();
        $record->metricshortname = 'test_metric';
        $record->courseid = $course->id;
        $record->lastrun = 10000;

        $DB->insert_record('tool_nla_metrics_course', $record);

        $analyzer = new analyze();
        $process = $analyzer->time_to_process('test_metric', $course->id, 1000, 1000);

        $this->assertEquals(false, $process);

    }

    /**
     * Test method to save stats to database.
     */
    public function test_save_stats() {
        global $DB;
        $this->resetAfterTest(true);

        $courseid = 1;
        $stats = array(2, 3, 4, 5, 6, 7, 8);
        $periodstart = 100;
        $periodlength = 604800;

        $analyzer = new analyze();
        $id = $analyzer->save_stats($courseid, $stats, $periodstart, $periodlength);

        $record = $DB->get_record('tool_nla_site', array('id' => $id));

        $this->assertEquals($courseid, $record->courseid);
        $this->assertEquals(2, $record->minimum);
        $this->assertEquals(3, $record->maximum);
        $this->assertEquals(4, $record->mean);
        $this->assertEquals(5, $record->median);
        $this->assertEquals(6, $record->lowerquartile);
        $this->assertEquals(7, $record->upperquartile);
        $this->assertEquals(8, $record->interquartile);
        $this->assertEquals($periodstart, $record->periodstart);
        $this->assertEquals($periodlength, $record->periodlength);

    }
}