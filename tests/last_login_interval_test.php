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
 * Get user method unit tests
 *
 * @package     tool_nla
 * @category    phpunit
 * @copyright   2017 Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use tool_nla\metrics\last_login_interval;


class tool_nla_last_login_interval_testcase extends advanced_testcase {

    /**
     * Test get_users method with no enrollments.
     */
    public function test_get_users() {
        global $DB;
        $this->resetAfterTest(true);
        $generator = $this->getDataGenerator();

        // Setup courses and users.
        $roleids = $DB->get_records_menu('role', null, '', 'shortname, id');
        $course1 = $generator->create_course();
        $student1 = $generator->create_user();
        $student2 = $generator->create_user();
        $generator->enrol_user($student1->id, $course1->id, $roleids['student']);

        $last_login_interval= new last_login_interval();
        $data = $last_login_interval->get_data();
        
        mtrace(print_r($data, true));

        // Expecting one user.
        //$this->assertEquals(1, count($users));
    }
}