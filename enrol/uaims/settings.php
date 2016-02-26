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
 * IMS Enterprise enrolments plugin settings and presets.
 *
 * @package    enrol
 * @subpackage imsenterprise
 * @copyright  2010 Eugene Venter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
global $CFG;

if ($ADMIN->fulltree) {
    require_once($CFG->dirroot . '/enrol/uaims/locallib.php');

    $settings->add(new admin_setting_configcheckbox('enrol_uaims/enableautocourseopenclose',
            new lang_string('enableautocourseopenclose', 'enrol_uaims'),
            new lang_string('enableautocourseopenclosedesc', 'enrol_uaims'), 0));

    $settings->add(new admin_setting_configcheckbox('enrol_uaims/enableqrvisibilitytoggle',
            new lang_string('enableqrvisibilitytoggle', 'enrol_uaims'),
            new lang_string('enableqrvisibilitytoggledesc', 'enrol_uaims'), 1));

    if (!during_initial_install()) {
        $classname = context_helper::get_class_for_level(CONTEXT_COURSE);
        $coursecontext = $classname::instance(SITEID, IGNORE_MISSING);
        $assignableroles = get_assignable_roles($coursecontext);
        $assignableroles = array('0' => get_string('ignore', 'enrol_uaims')) + $assignableroles;
        $imsroles = new uaims_roles();
        foreach ($imsroles->get_imsroles() as $imsrolenum => $imsrolename) {
            $settings->add(new admin_setting_configselect('enrol_uaims/imsrolemap' . $imsrolenum,
                format_string('"' . $imsrolename . '" (' . $imsrolenum.')'),
                '', (int)$imsroles->determine_default_rolemapping($imsrolenum), $assignableroles));
        }
    }
}

