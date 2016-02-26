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

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    //  It must be included from a Moodle page.
}

require_once($CFG->libdir.'/formslib.php');

class uag_export_form extends moodleform {
    public function definition() {
        global $CFG, $COURSE, $USER, $DB;
        $mform =& $this->_form;
        if (isset($this->_customdata)) {  // Hardcoding plugin names here is hacky.
            $features = $this->_customdata;
        } else {
            $features = array();
        }

        if (!$DB->get_fieldset_sql("SELECT mc.idnumber FROM mdl_enrol me,mdl_cohort mc where me.customint1=mc.id and me.courseid=".
            $COURSE->id)) {
            $mform->addElement('html', get_string('onlycreditsection', 'gradeexport_uag'));
            return;
        }

        $mform->addElement('header', 'options', get_string('options', 'grades'), array('style' => 'display:none'));
        $mform->addElement('html', get_string('exportoption_desc', 'gradeexport_uag'));

        if (!empty($features['updategradesonly'])) {
            $mform->addElement('advcheckbox', 'updatedgradesonly', get_string('updatedgradesonly', 'grades'));
        }
        $mform->setDefault('display', $CFG->grade_export_displaytype);

        if (!empty($CFG->gradepublishing) and !empty($features['publishing'])) {
            $mform->addElement('header', 'publishing', get_string('publishing', 'grades'));
            $options = array(get_string('nopublish', 'grades'), get_string('createnewkey', 'userkey'));
            $keys = $DB->get_records_select('user_private_key', "script='grade/export' AND instance=? AND userid=?",
                array($COURSE->id, $USER->id));
            if ($keys) {
                foreach ($keys as $key) {
                    $options[$key->value] = $key->value; // TODO: add more details - ip restriction, valid until ??
                }
            }
            $mform->addElement('select', 'key', get_string('userkey', 'userkey'), $options);
            $mform->addHelpButton('key', 'userkey', 'userkey');
            $mform->addElement('static', 'keymanagerlink',
                get_string('keymanager', 'userkey'),
                '<a href="'.$CFG->wwwroot.'/grade/export/keymanager.php?id='.$COURSE->id.'">'
                    .get_string('keymanager', 'userkey').'</a>');

            $mform->addElement('text', 'iprestriction', get_string('keyiprestriction', 'userkey'), array('size' => 80));
            $mform->addHelpButton('iprestriction', 'keyiprestriction', 'userkey');
            // Own IP - just in case somebody does not know what user key is.
            $mform->setDefault('iprestriction', getremoteaddr());

            $mform->addElement('date_time_selector', 'validuntil', get_string('keyvaliduntil', 'userkey'),
                array('optional' => true));
            $mform->addHelpButton('validuntil', 'keyvaliduntil', 'userkey');
            // Only 1 week default duration - just in case somebody does not know what user key is.
            $mform->setDefault('validuntil', time() + 3600 * 24 * 7);

            $mform->disabledIf('iprestriction', 'key', 'noteq', 1);
            $mform->disabledIf('validuntil', 'key', 'noteq', 1);
        }

        $switch = grade_get_setting($COURSE->id, 'aggregationposition', $CFG->grade_aggregationposition);

        // Grab the grade_seq for this course.
        $gseq = new grade_seq($COURSE->id, $switch);
        if ($gradeitems = $gseq->items) {
            $needsmultiselect = false;
            foreach ($gradeitems as $gradeitem) {
                $clms[$gradeitem->id] = $gradeitem->get_name();
                if ($gradeitem->itemtype == 'course') {
                    $selecteditm = $gradeitem->id;
                }
                if (!empty($features['idnumberrequired']) and empty($gradeitem->idnumber)) {
                    $mform->addElement('advcheckbox', 'itemids['.$gradeitem->id.']',
                        $gradeitem->get_name(), get_string('noidnumber', 'grades'));
                    $mform->hardFreeze('itemids['.$gradeitem->id.']');
                } else {
                    $mform->addElement('advcheckbox',
                        'itemids['.$gradeitem->id.']', $gradeitem->get_name(), null, array('group' => 1));

                    $mform->setDefault('itemids['.$gradeitem->id.']', 0);

                    $needsmultiselect = true;
                }
            }

            if ($needsmultiselect) {
                // 1st argument is group name, 2nd is link text, 3rd is attributes and 4th is original value.
                $this->add_checkbox_controller(1, null, null, 0);
            }
        }
        $selclms = $mform->addElement('select', 'whattograde', get_string('whattograde', 'gradeexport_uag'), $clms);
        $selclms->setSelected($selecteditm);
        $mform->addElement('hidden', 'id', $COURSE->id);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('html', get_string('verify_percentbounds', 'gradeexport_uag',
            context_course::instance($COURSE->id)->id));

        $mform->addElement('advcheckbox', 'gradeboundaryreview', get_string('gradeboundaryreview', 'gradeexport_uag'));
        $this->add_action_buttons(false, 'Download');

    }

}

