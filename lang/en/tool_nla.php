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
 * Plugin strings are defined here.
 *
 * @package     tool_nla
 * @category    string
 * @copyright   2017 Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Native Learning Analytics';
$string['pluginnamedesc'] = 'Native Learning Analytics configuration settings.';

$string['cachedef_course'] = 'Stores retrieved courses use by native learning analytics.';
$string['hiddencourses'] = 'Exclude hidden courses';
$string['hiddencourses_desc'] = 'When enabled hidden courses will be excluded from analytics, this includes courses in hidden categories.';
$string['processtask'] = 'Collect and process analytic data';
$string['startend'] = 'Respect course start and end dates';
$string['startend_desc'] = 'When enabled courses will be excluded from analytics calculations if their start date has in the future or their end date is in the past.';
