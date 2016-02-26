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
 * @author : Patrick Thibaudeau @version $Id: version.php,v 2.0 2010/07/13 18:10:20 @package tab
 * @author Joey Andres <jandres@ualberta.ca>
 **/

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->libdir.'/filelib.php');
require_once(dirname(__FILE__).'/locallib.php');

class mod_tab_mod_form extends moodleform_mod {

    public function definition() {
        global $CFG, $DB, $COURSE;

        $mform  = $this->_form;

        $config = get_config('tab');

        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('text', 'name', get_string('name', 'tab'), array('size' => '45'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');

        // Have to use this option for postgresqgl to work.
        $instance = $this->current->instance;
        if (empty($instance)) {
                $instance = 0;
        }

        // Following code used to create tabcontent order numbers.
        if (isset($_POST['optionid'])) {
            $repeatnum = count($_POST['optionid']);
        } else {
            $repeatnum = 0;
        }
        if ($repeatnum == 0) {
            $repeatnum = $DB->count_records('tab_content', array('tabid' => $instance));
        }
        $taborder = 1; // Initialize to prevent warnings.
        for ($i = 1; $i <= $repeatnum + 1; $i++) {
            if ($i == 1) {
                $taborder = 1;
            } else {
                $taborder = $taborder.','.$i;
            }

        }
        $context = $this->context;

        $editoroptions = array('subdirs' => 1, 'maxbytes' => $CFG->maxbytes, 'maxfiles' => -1, 'changeformat' => 1,
                                'context' => $context, 'noclean' => 1, 'trusttext' => 1, 'accepted_types' => '*');
        $taborderarray = explode(',', $taborder);

        // For adding tabs.
        $repeatarray = array();

        $repeatarray[] = $mform->createElement('header', 'tabs', get_string('tab', 'tab').' {no}');
        $repeatarray[] = $mform->createElement('text', 'tabname', get_string('tabname', 'tab'), array('size' => '65'));
        $mform->setType('tabname', PARAM_TEXT);
        $repeatarray[] = $mform->createElement('editor', 'content', get_string('tabcontent', 'tab'), null, $editoroptions);

        // DR: per "http://docs.moodle.org/dev/Using_the_File_API_in_Moodle_forms".
        $repeatarray[] = $mform->createElement('hidden', 'revision', 1);
        $mform->setType('revision', PARAM_INT);
        $repeatarray[] = $mform->createElement('select', 'tabcontentorder', get_string('order', 'tab'), $taborderarray);
        $mform->setType('tabcontentorder', PARAM_INT);
        $repeatarray[] = $mform->createElement('hidden', 'optionid', 0);
        $mform->setType('optionid', PARAM_INT);

        if ($this->_instance) {
            $repeatno = $DB->count_records('tab_content', array('tabid' => $instance));
            $repeatno += 1;
        } else {
            $repeatno = 1;
        }

        $repeateloptions = array();

        if (!isset($repeateloptions['tabcontentorder'])) {
            $repeateloptions['tabcontentorder']['default'] = $repeatnum;
        }
        if (!isset($repeateloptions['tabs'])) {
            $repeateloptions['tabs']['expanded'] = true;
        }

        $repeateloptions['content']['helpbutton'] = array('content', 'tab');

        // The content should be getting cleaned to prevent XSS vulverabilities.
        // But for now we'll bring Tab behaviour in line with Page.

        $this->repeat_elements($repeatarray, $repeatno, $repeateloptions, 'option_repeats', 'option_add_fields', 1,
            get_string('addtab', 'tab'));

        // Display menu checkbox and name.

        $mform->addElement('header', 'menu', get_string('displaymenu', 'tab'));
        $mform->addElement('advcheckbox', 'displaymenu', get_string('displaymenuagree', 'tab'), null,
            array('group' => 1), array('0', '1'));
        $mform->setType('displaymenu', PARAM_INT);
        $mform->addElement('text', 'taborder', get_string('taborder', 'tab'), array('size' => '15'));
        $mform->setType('taborder', PARAM_INT);

        // Link to datalist element below for course group names suggestion. Makes it way easier.
        $mform->addElement('text', 'menuname', get_string('menuname', 'tab'),
            array('size' => '45', 'list' => 'mod_tab_menu_names'));
        $mform->setType('menuname', PARAM_TEXT);

        $coursetabmenunames = get_tab_menu_names_in_course($COURSE->id);
        $datalisthtml = "<datalist id='mod_tab_menu_names'>";
        foreach ($coursetabmenunames as $tabmenuname) {
            $datalisthtml .= "<option value='$tabmenuname'>";
        }
        $datalisthtml .= '</datalist>';
        $mform->addElement('html', $datalisthtml);

        $features = array('groups' => false, 'groupings' => false, 'groupmembersonly' => true,
                'outcomes' => false, 'gradecat' => false, 'idnumber' => false);
        $this->standard_coursemodule_elements($features);

        // Buttons.
        $this->add_action_buttons();
    }

    public function data_preprocessing(&$defaultvalues) {
        global $CFG, $DB;

        if ($this->current->instance) {
            $options = $DB->get_records('tab_content', array('tabid' => $this->current->instance), 'tabcontentorder');
            $tabids = array_keys($options);
            $options = array_values($options);
            $context = $this->context;
            $editoroptions = array('subdirs' => 1, 'maxbytes' => $CFG->maxbytes, 'maxfiles' => -1, 'changeformat' => 1,
                'context' => $context, 'noclean' => 1, 'trusttext' => 1);
            foreach (array_keys($options) as $key) {

                $defaultvalues['tabname['.$key.']'] = $options[$key]->tabname;

                $draftitemid = file_get_submitted_draft_itemid('content['.$key.']');

                $defaultvalues['content['.$key.']']['format'] = $options[$key]->contentformat;
                $defaultvalues['content['.$key.']']['text']   = file_prepare_draft_area($draftitemid, $this->context->id,
                    'mod_tab', 'content', $options[$key]->id, $editoroptions, $options[$key]->tabcontent);
                $defaultvalues['content['.$key.']']['itemid'] = $draftitemid;

                $defaultvalues['externalurl['.$key.']'] = $options[$key]->externalurl;

                // DR: process fileattachments.
                $draftitemid = file_get_submitted_draft_itemid('fileattachment['.$key.']');
                file_prepare_draft_area($draftitemid, $this->context->id, 'mod_tab', 'fileattachment', $options[$key]->id);
                $defaultvalues['fileattachment['.$key.']'] = $draftitemid;

                $defaultvalues['tabcontentorder['.$key.']'] = $options[$key]->tabcontentorder;
                $defaultvalues['optionid['.$key.']'] = $tabids[$key];
            }
        }

    }
}
