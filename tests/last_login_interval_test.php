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
        // Setup the test user array.
        $user1 = new stdClass();
        $user1->lastlogin = 0;
        $user2 = new stdClass();
        $user2->lastlogin = 1509584381;
        $users = array ($user1, $user2);

        $now = 1509584382;

        $lastlogininterval= new last_login_interval($users, $now);
        $count = 0;

        foreach ($lastlogininterval as $value) {
            $count++;
        }
        $lastlogininterval->rewind();
        $lastlogin = $lastlogininterval->current();

        // Expecting one result.
        $this->assertEquals(1, $count);
        $this->assertEquals(1, $lastlogin);
    }
}