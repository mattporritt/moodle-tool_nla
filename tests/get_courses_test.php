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
 * Class to perform task based data analysis.
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
     */
    public function test_get_courses_no_enrolments() {
        global $DB;
        $this->resetAfterTest(true);

        // Setup course.
        $course = $this->getDataGenerator()->create_course();

        $analyzer = new analyze();
        $courses = $analyzer->get_courses();

        // Expecting zero courses as there are no enrollments.
        $this->assertEquals(0, count($courses));
    }

    /**
     * Test get_courses method with enrollments.
     */
    public function test_get_courses_enrolments() {
        global $DB;
        $this->resetAfterTest(true);
        $generator = $this->getDataGenerator();

        // Setup courses and users.
        $roleids = $DB->get_records_menu('role', null, '', 'shortname, id');
        $student = $generator->create_user();
        $course1 = $generator->create_course();
        $course2 = $generator->create_course();
        $generator->enrol_user($student->id, $course1->id, $roleids['student']);

        $analyzer = new analyze();
        $courses = $analyzer->get_courses();

        $this->assertEquals(1, count($courses));
        $this->assertEquals($course1->id, $courses[$course1->id]->id);
    }

    /**
     * Test get_courses method with hidden course.
     */
    public function test_get_courses_hidden_course() {
        global $DB;
        $this->resetAfterTest(true);
        $generator = $this->getDataGenerator();

        // Setup courses and users.
        $roleids = $DB->get_records_menu('role', null, '', 'shortname, id');
        $student = $generator->create_user();
        $course1 = $generator->create_course();
        $course2 = $generator->create_course(array('visible' => 0));
        $generator->enrol_user($student->id, $course1->id, $roleids['student']);
        $generator->enrol_user($student->id, $course2->id, $roleids['student']);

        $analyzer = new analyze();
        $courses = $analyzer->get_courses();

        $this->assertEquals(1, count($courses));
        $this->assertEquals($course1->id, $courses[$course1->id]->id);
    }

    /**
     * Test get_courses method with hidden category.
     */
    public function test_get_courses_hidden_category() {
        global $DB;
        $this->resetAfterTest(true);
        $generator = $this->getDataGenerator();

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

        $this->assertEquals(0, count($courses));
    }

    /**
     * Test get_courses method with suspended user.
     */
    public function test_get_courses_suspended_user() {
        global $DB;
        $this->resetAfterTest(true);
        $generator = $this->getDataGenerator();

        // Setup courses and users.
        $roleids = $DB->get_records_menu('role', null, '', 'shortname, id');
        $student1 = $generator->create_user();
        $student2 = $generator->create_user(array('suspended' => 1));
        $course1 = $generator->create_course();
        $course2 = $generator->create_course();
        $generator->enrol_user($student1->id, $course1->id, $roleids['student']);
        $generator->enrol_user($student2->id, $course2->id, $roleids['student']);

        $analyzer = new analyze();
        $courses = $analyzer->get_courses();

        $this->assertEquals(1, count($courses));
        $this->assertEquals($course1->id, $courses[$course1->id]->id);
    }

    /**
     * Test get_courses method with deleted user.
     */
    public function test_get_courses_deleted_user() {
        global $DB;
        $this->resetAfterTest(true);
        $generator = $this->getDataGenerator();

        // Setup courses and users.
        $roleids = $DB->get_records_menu('role', null, '', 'shortname, id');
        $student1 = $generator->create_user();
        $student2 = $generator->create_user();
        $course1 = $generator->create_course();
        $course2 = $generator->create_course();
        $generator->enrol_user($student1->id, $course1->id, $roleids['student']);
        $generator->enrol_user($student2->id, $course2->id, $roleids['student']);
        delete_user($student2);

        $analyzer = new analyze();
        $courses = $analyzer->get_courses();

        $this->assertEquals(1, count($courses));
        $this->assertEquals($course1->id, $courses[$course1->id]->id);
    }

    /**
     * Test get_courses method with suspended enrollment.
     */
    public function test_get_courses_suspended_enrolment() {
        global $DB;
        $this->resetAfterTest(true);
        $generator = $this->getDataGenerator();

        // Setup courses and users.
        $roleids = $DB->get_records_menu('role', null, '', 'shortname, id');
        $student1 = $generator->create_user();
        $student2 = $generator->create_user();
        $course1 = $generator->create_course();
        $course2 = $generator->create_course();
        $generator->enrol_user($student1->id, $course1->id, $roleids['student']);
        $generator->enrol_user($student2->id, $course2->id, $roleids['student'], 'manual', 0, 0, 1);

        $analyzer = new analyze();
        $courses = $analyzer->get_courses();

        $this->assertEquals(1, count($courses));
        $this->assertEquals($course1->id, $courses[$course1->id]->id);
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
