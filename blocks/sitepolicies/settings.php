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
 * @package sitepolicies
 * @author Asim Aziz
 **/

$settings->add(new admin_setting_configtextarea('block_sitepolicies/config_uofa_policies', get_string('moodle_uofa_policies',
    'block_sitepolicies'), get_string('moodle_uofa_policies_desc', 'block_sitepolicies'), get_string('uofa_policies_defaults',
    'block_sitepolicies'), PARAM_TEXT));

$settings->add(new admin_setting_configtextarea('block_sitepolicies/config_faculty_policies', get_string('moodle_faculty_policies',
    'block_sitepolicies'), get_string('moodle_faculty_policies_desc', 'block_sitepolicies'), get_string('faculty_policies_defaults',
'block_sitepolicies'), PARAM_TEXT));