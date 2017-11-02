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

/**
 * Last login interval metric class.
 * Creates an iterator of user last login intervals.
 *
 * @package     tool_nla
 * @copyright   2017 Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class last_login_interval implements \Iterator {

    /**
     * Array containing all user last login timestamps.
     *
     * @var array
     */
    private $array = array();

    /**
     * Pointer of interator.
     *
     * @var integer
     */
    private $position = 0;

    /**
     * Timestamp to use in login interval calculations.
     *
     * @var integer
     */
    private $now = 0;

    /**
     * Constructor for class.
     *
     * @param array $users Array of user records
     * @param integer $timestamp Timestamp to use in login interval calculations.
     * @return void
     */
    public function __construct($users, $timestamp=0) {
        if ($timestamp != 0) {
            $this->now = $timestamp;
        } else {
            $this->now = time();
        }

        $this->array = $this->get_array_from_users($users);

    }

    /**
     * Given an array of user records return list of last
     * login intervals for proivded users.
     *
     * @param array $users Array of user records.
     * @return array $lastlogin List of the last login interval for users.
     */
    private function get_array_from_users($users) {
        $lastlogin = array();

        foreach ($users as $user) {
            if ($user->lastlogin != 0) { // Filter out users who have never logged in.
                $lastlogin[] = ($this->now - $user->lastlogin);
            }
        }

        return $lastlogin;
    }

    /**
     * {@inheritDoc}
     * @see Iterator::rewind()
     */
    public function rewind() {
        $this->position = 0;
    }

    /**
     * {@inheritDoc}
     * @see Iterator::current()
     */
    public function current() {
        return $this->array[$this->position];
    }

    /**
     * {@inheritDoc}
     * @see Iterator::key()
     */
    public function key() {
        return $this->position;
    }

    /**
     * {@inheritDoc}
     * @see Iterator::next()
     */
    public function next() {
        ++$this->position;
    }

    /**
     * {@inheritDoc}
     * @see Iterator::valid()
     */
    public function valid() {
        return isset($this->array[$this->position]);
    }
}