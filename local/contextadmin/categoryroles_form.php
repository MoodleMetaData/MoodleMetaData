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
 * @package eclass-local-contextadmin
 * @author joshstagg
 * @copyright Josh Stagg
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.'); // It must be included from a Moodle page.
}

require_once($CFG->dirroot.'/course/moodleform_mod.php');

class categoryroles_form extends moodleform {

    // Form definition.
    public function definition() {
        $mform =& $this->_form;
        $category = $this->_customdata['category'];

        $mform->addElement('header', 'rolerenaming', get_string('rolerenaming'));
        $mform->addHelpButton('rolerenaming', 'rolerenaming');

        if ($roles = get_all_roles()) {
            $roles = role_fix_names($roles, null, ROLENAME_ORIGINAL);
            $assignableroles = get_roles_for_contextlevels(CONTEXT_COURSECAT);
            foreach ($roles as $role) {
                $mform->addElement('text', 'role_'.$role->id, get_string('yourwordforx', '', $role->localname));
                $mform->setType('role_'.$role->id, PARAM_TEXT);
                if (!in_array($role->id, $assignableroles)) {
                    $mform->setAdvanced('role_'.$role->id);
                }
            }
        }

        $mform->addElement('hidden', 'id', $category->id);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('html', get_string('roleedit_notice', 'local_contextadmin'));

        $this->add_action_buttons(true, get_string('savechanges'));
    }



    public function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);
        if (!empty($data['idnumber'])) {
            if ($existing = $DB->get_record('course_categories', array('idnumber' => $data['idnumber']))) {
                if (!$data['id'] || $existing->id != $data['id']) {
                    $errors['idnumber'] = get_string('idnumbertaken');
                }
            }
        }

        return $errors;
    }
}
