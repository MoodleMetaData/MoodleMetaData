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
 *
 *
 * @package usingmoodle
 * @author Anthony Radziszewski radzisze@ualberta.ca
 **/

$settings->add(new admin_setting_configtextarea('block_usingmoodle/config_tip_sheets', get_string('moodle_tip_sheets',
    'block_usingmoodle'), get_string('moodle_tip_sheets_desc', 'block_usingmoodle'), get_string('tip_sheets_defaults',
    'block_usingmoodle'), PARAM_TEXT));
$settings->add(new admin_setting_configtextarea('block_usingmoodle/config_screencasts', get_string('moodle_screencasts',
    'block_usingmoodle'), get_string('moodle_screencasts_desc', 'block_usingmoodle'), get_string('screencasts_defaults',
    'block_usingmoodle'), PARAM_TEXT));
$settings->add(new admin_setting_configtextarea('block_usingmoodle/config_getting_help', get_string('moodle_getting_help',
    'block_usingmoodle'), get_string('moodle_getting_help_desc', 'block_usingmoodle'), get_string('getting_help_defaults',
    'block_usingmoodle'), PARAM_TEXT));
$settings->add(new admin_setting_configtextarea('block_usingmoodle/config_for_instructors', get_string('moodle_for_instructors',
    'block_usingmoodle'), get_string('moodle_for_instructors_desc', 'block_usingmoodle'), get_string('for_instructors_defaults',
    'block_usingmoodle'), PARAM_TEXT));