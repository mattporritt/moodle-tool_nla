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

namespace tool_nla\metrics;

defined('MOODLE_INTERNAL') || die();

use tool_nla\analyze\analyze;

/**
 * Last login interval metric class.
 *
 * @package     tool_nla
 * @copyright   2017 Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class last_login_interval {

    private $array = array();

    public function __construct() {
        $this->array = $this->get_array_from_users();
    }

    /**
     *
     * @return array $lastlogin List of the last login interval for users.
     */
    private function get_array_from_users(){
        $analyzer = new analyze();
        $users = $analyzer->get_users();
        $now = time();

        $lastlogin = array();

        foreach ($users as $user) {
            if ($user->lastlogin != 0) { // Filter out users who have never logged in.
                $lastlogin[] = ($now - $user->lastlogin);
            }
        }

        return $lastlogin;
    }


    public function get_data() {
        return $this->array;

    }
}