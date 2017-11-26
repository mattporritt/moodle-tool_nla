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
 * Get course method unit tests
 *
 * @package     tool_nla
 * @category    phpunit
 * @copyright   2017 Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use tool_nla\analyze\analyze;

global $CFG;
require_once($CFG->dirroot . '/course/lib.php');

class tool_nla_get_courses_testcase extends advanced_testcase {

    /**
     * Test get_courses method with no enrollments.
     * Get courses should return site level course
     * and not care about enrolments.
     */
    public function test_get_courses_no_enrolments() {
        global $DB;
        $this->resetAfterTest(true);

        set_config('startend', 0, 'tool_nla');

        // Setup course.
        $course = $this->getDataGenerator()->create_course();

        $analyzer = new analyze();
        $courses = $analyzer->get_courses();

        // Expecting zero courses as there are no enrollments.
        $this->assertEquals(2, count($courses));
    }

    /**
     * Test get_courses method with enrollments.
     * Get courses should return site level course
     * and not care about enrolments.
     */
    public function test_get_courses_enrolments() {
        global $DB;
        $this->resetAfterTest(true);
        $generator = $this->getDataGenerator();

        set_config('startend', 0, 'tool_nla');

        // Setup courses and users.
        $roleids = $DB->get_records_menu('role', null, '', 'shortname, id');
        $student = $generator->create_user();
        $course1 = $generator->create_course();
        $course2 = $generator->create_course();
        $generator->enrol_user($student->id, $course1->id, $roleids['student']);

        $analyzer = new analyze();
        $courses = $analyzer->get_courses();

        $this->assertEquals(3, count($courses));
    }

    /**
     * Test get_courses method with hidden course.
     * Hidden courses should not be displayed if
     * excluded by configuration.
     */
    public function test_get_courses_hidden_course() {
        global $DB;
        $this->resetAfterTest(true);
        $generator = $this->getDataGenerator();

        set_config('startend', 0, 'tool_nla');
        set_config('hiddencourses', 1, 'tool_nla');

        // Setup courses and users.
        $roleids = $DB->get_records_menu('role', null, '', 'shortname, id');
        $student = $generator->create_user();
        $course1 = $generator->create_course(array('visible' => 0));
        $course2 = $generator->create_course(array('visible' => 0));
        $generator->enrol_user($student->id, $course1->id, $roleids['student']);
        $generator->enrol_user($student->id, $course2->id, $roleids['student']);

        $analyzer = new analyze();
        $courses = $analyzer->get_courses();

        $this->assertEquals(1, count($courses));
        $this->assertEquals(1, $courses[1]->id);
    }

    /**
     * Test get_courses method with hidden course.
     * Hidden courses should be displayed if
     * included by configuration.
     */
    public function test_get_courses_hidden_course_config_enabled() {
        global $DB;
        $this->resetAfterTest(true);
        $generator = $this->getDataGenerator();

        set_config('startend', 0, 'tool_nla');
        set_config('hiddencourses', 0, 'tool_nla');

        // Setup courses and users.
        $roleids = $DB->get_records_menu('role', null, '', 'shortname, id');
        $student = $generator->create_user();
        $course1 = $generator->create_course(array('visible' => 0));
        $course2 = $generator->create_course(array('visible' => 0));
        $generator->enrol_user($student->id, $course1->id, $roleids['student']);
        $generator->enrol_user($student->id, $course2->id, $roleids['student']);

        $analyzer = new analyze();
        $courses = $analyzer->get_courses();

        $this->assertEquals(3, count($courses));
    }

    /**
     * Test get_courses method with hidden category.
     */
    public function test_get_courses_hidden_category() {
        global $DB;
        $this->resetAfterTest(true);
        $generator = $this->getDataGenerator();

        set_config('startend', 0, 'tool_nla');
        set_config('hiddencourses', 1, 'tool_nla');

        // Setup courses and users.
        $roleids = $DB->get_records_menu('role', null, '', 'shortname, id');
        $student = $generator->create_user();
        $course1 = $generator->create_course();
        $course2 = $generator->create_course(array('visible' => 0));
        $generator->enrol_user($student->id, $course1->id, $roleids['student']);
        $generator->enrol_user($student->id, $course2->id, $roleids['student']);
        $categoryhidden = $generator->create_category(array('visible' => 0));
        move_courses(array($course1->id, $course2->id), $categoryhidden->id);

        $analyzer = new analyze();
        $courses = $analyzer->get_courses();

        $this->assertEquals(1, count($courses));
        $this->assertEquals(1, $courses[1]->id);
    }

    /**
     * Test get_courses method with hidden category.
     */
    public function test_get_courses_hidden_category_config_enabled() {
        global $DB;
        $this->resetAfterTest(true);
        $generator = $this->getDataGenerator();

        set_config('startend', 0, 'tool_nla');
        set_config('hiddencourses', 0, 'tool_nla');

        // Setup courses and users.
        $roleids = $DB->get_records_menu('role', null, '', 'shortname, id');
        $student = $generator->create_user();
        $course1 = $generator->create_course();
        $course2 = $generator->create_course(array('visible' => 0));
        $generator->enrol_user($student->id, $course1->id, $roleids['student']);
        $generator->enrol_user($student->id, $course2->id, $roleids['student']);
        $categoryhidden = $generator->create_category(array('visible' => 0));
        move_courses(array($course1->id, $course2->id), $categoryhidden->id);

        $analyzer = new analyze();
        $courses = $analyzer->get_courses();

        $this->assertEquals(3, count($courses));

    }

    /**
     * Test get_courses method with course start and end times.
     */
    public function test_get_courses_dates() {
        global $DB;
        $this->resetAfterTest(true);
        $generator = $this->getDataGenerator();
        $now = time();

        // Setup courses and users.
        $roleids = $DB->get_records_menu('role', null, '', 'shortname, id');
        $student1 = $generator->create_user();
        $student2 = $generator->create_user();
        $course1 = $generator->create_course(array('startdate' => ($now + 1000)));
        $course2 = $generator->create_course(array('startdate' => ($now - 1000), 'enddate' => ($now + 1000)));
        $generator->enrol_user($student1->id, $course1->id, $roleids['student']);
        $generator->enrol_user($student2->id, $course2->id, $roleids['student']);

        $analyzer = new analyze();
        $courses = $analyzer->get_courses();

        $this->assertEquals(1, count($courses));
        $this->assertEquals($course2->id, $courses[$course2->id]->id);
    }
}
