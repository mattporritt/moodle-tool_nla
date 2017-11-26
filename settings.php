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
 * Plugin administration pages are defined here.
 *
 * @package     tool_nla
 * @category    admin
 * @copyright   2017 Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $PAGE;

if ($hassiteconfig) {
    $settings = new admin_settingpage('tool_nla', get_string('pluginname', 'tool_nla'));
    $ADMIN->add('tools', $settings);

    $settings->add(new admin_setting_heading(
            'tool_nla_settings',
            '',
            get_string('pluginnamedesc', 'tool_nla')
            ));

    if (! during_initial_install ()) {
        // General Settings.
        $settings->add(new admin_setting_configcheckbox('tool_nla/hiddencourses', 
                get_string('hiddencourses', 'tool_nla'),
                get_string('hiddencourses_desc', 'tool_nla'),
                1));

        $settings->add(new admin_setting_configcheckbox('tool_nla/startend',
                get_string('startend', 'tool_nla'),
                get_string('startend_desc', 'tool_nla'),
                1));
    }
}
