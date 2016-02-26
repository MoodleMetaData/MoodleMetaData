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
 * @package mod
 * @subpackage adobeconnect
 * @author Akinsaya Delamarre (adelamarre@remote-learner.net)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    //  It must be included from a Moodle page.
}

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/adobeconnect/locallib.php');

class mod_adobeconnect_mod_form extends moodleform_mod {

    public function definition() {

        global $CFG;
        $mform =& $this->_form;

        // Adding the "general" fieldset, where all the common settings are showed.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text',
            'name', get_string('adobeconnectname',
            'adobeconnect'),
            array('size' => '64',
            'maxlength' => '60'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        // Adding the required "intro" field to hold the description of the instance.
        $this->add_intro_editor(false, get_string('adobeconnectintro', 'adobeconnect'));

        // Adding the rest of adobeconnect settings, spreading all them into this fieldset
        // or adding more fieldsets ('header' elements) if needed for better logic.
        $mform->addElement('header', 'adobeconnectfieldset', get_string('adobeconnectfieldset', 'adobeconnect'));

        // Meeting URL.
        $attributes = array('size' => '20');
        $mform->addElement('text', 'meeturl', get_string('meeturl', 'adobeconnect'), $attributes);
        $mform->setType('meeturl', PARAM_PATH);
        $mform->addHelpButton('meeturl', 'meeturl', 'adobeconnect');
        $mform->disabledIf('meeturl', 'tempenable', 'eq', 0);

        // Public or private meeting.
        $meetingpublic = array(1 => get_string('public', 'adobeconnect'), 0 => get_string('private', 'adobeconnect'));
        $mform->addElement('select', 'meetingpublic', get_string('meetingtype', 'adobeconnect'), $meetingpublic);
        $mform->addHelpButton('meetingpublic', 'meetingtype', 'adobeconnect');

        // Meeting Template.
        $templates = $this->get_templates();
        ksort($templates);
        $mform->addElement('select', 'templatescoid', get_string('meettemplates', 'adobeconnect'), $templates);
        $mform->addHelpButton('templatescoid', 'meettemplates', 'adobeconnect');
        $mform->disabledIf('templatescoid', 'tempenable', 'eq', 0);

        $mform->addElement('hidden', 'tempenable');
        $mform->setType('tempenable', PARAM_INT);

        $mform->addElement('hidden', 'userid');
        $mform->setType('userid', PARAM_INT);

        // Start and end date selectors.
        $mform->addElement('date_time_selector', 'starttime', get_string('starttime', 'adobeconnect'));
        $mform->addElement('date_time_selector', 'endtime', get_string('endtime', 'adobeconnect'));
        $mform->setDefault('endtime', strtotime('+2 hours'));

        // Add standard elements, common to all modules.
        $this->standard_coursemodule_elements(array('groups' => true));

        // Disabled the group mode if the meeting has already been created.
        $mform->disabledIf('groupmode', 'tempenable', 'eq', 0);
        // Add standard buttons, common to all modules.
        $this->add_action_buttons();

    }

    public function data_preprocessing(&$defaultvalues) {
        global $DB;

        if (array_key_exists('update', $defaultvalues)) {

            $params = array('instanceid' => $defaultvalues['id']);
            $sql = "SELECT id FROM {adobeconnect_meeting_groups} WHERE ".
                   "instanceid = :instanceid";

            if ($DB->record_exists_sql($sql, $params)) {
                $defaultvalues['tempenable'] = 0;
            }
        }
    }

    public function validation($data, $files) {
        global $DB, $USER, $COURSE;
        $errors = parent::validation($data, $files);
        $usrobj = clone($USER);
        $name = empty($usrobj->idnumber) ? $usrobj->username : $usrobj->idnumber;
        $usrobj->username = set_username($name, $usrobj->email);
        $aconnect  = aconnect_login();
        $groupprincipalid = aconnect_get_host_group($aconnect);

        // Check if the user exists and if not create the new user.
        if (!($usrprincipal = aconnect_user_exists($aconnect, $usrobj))) {
            if (!($usrprincipal = aconnect_create_user($aconnect, $usrobj))) {
                debugging("error creating user", DEBUG_DEVELOPER);
            }
        }

        // Add the user to the host group if they aren't already.
        aconnect_add_user_group($aconnect, $groupprincipalid, $usrprincipal);
        // Search for a Meeting with the same starting name.  It will cause a duplicate
        // meeting name (and error) when the user begins to add participants to the meeting.
        $meetfldscoid = aconnect_get_folder($aconnect, 'meetings');
        $filter = array('filter-like-name' => $data['name']);
        $namematches = aconnect_meeting_exists($aconnect, $meetfldscoid, $filter);
        // Search the user's adobe connect folder.
        $usrfldscoid = aconnect_get_user_folder_sco_id($aconnect, $usrobj->username);

        if (!empty($usrfldscoid)) {
            $namematches = $namematches + aconnect_meeting_exists($aconnect, $usrfldscoid, $filter);
        }

        if (empty($namematches)) {
            $namematches = array();
        }

        // Now search for existing meeting room URLs.
        $url = $data['meeturl'] = adobeconnect_clean_meet_url($data['meeturl']);

        // Check to see if there are any trailing slashes or additional parts to the url
        // ex. mymeeting/mysecondmeeting/  Only the 'mymeeting' part is valid.
        if ((0 != substr_count($url, '/')) and (false !== strpos($url, '/', 1))) {
            $errors['meeturl'] = get_string('invalidadobemeeturl', 'adobeconnect');
        }

        $filter = array('filter-like-url-path' => $url);
        $urlmatches = aconnect_meeting_exists($aconnect, $meetfldscoid, $filter);

        // Search the user's adobe connect folder.
        if (!empty($usrfldscoid)) {
            $urlmatches = $urlmatches + aconnect_meeting_exists($aconnect, $usrfldscoid, $filter);
        }

        if (empty($urlmatches)) {
            $urlmatches = array();
        } else {
            // Format url for comparison.
            if ((false === strpos($url, '/')) or (0 != strpos($url, '/'))) {
                $url = '/' . $url;
            }
        }

        // Check URL for correct length and format.
        if (!empty($data['meeturl'])) {
            if (strlen($data['meeturl']) > 60) {
                $errors['meeturl'] = get_string('longurl', 'adobeconnect');
            } else if (!preg_match('/^[a-z][a-z\-]*/i', $data['meeturl'])) {
                $errors['meeturl'] = get_string('invalidurl', 'adobeconnect');
            }
        }

        // Check for available groups if groupmode is selected.
        if ($data['groupmode'] > 0) {
            $crsgroups = groups_get_all_groups($COURSE->id);
            if (empty($crsgroups)) {
                $errors['groupmode'] = get_string('missingexpectedgroups', 'adobeconnect');
            }
        }

        // Adding activity.
        if (empty($data['update'])) {

            if ($data['starttime'] == $data['endtime']) {
                $errors['starttime'] = get_string('samemeettime', 'adobeconnect');
                $errors['endtime'] = get_string('samemeettime', 'adobeconnect');
            } else if ($data['endtime'] < $data['starttime']) {
                $errors['starttime'] = get_string('greaterstarttime', 'adobeconnect');
            }

            // Check for local activities with the same name.
            $params = array('name' => $data['name']);
            if ($DB->record_exists('adobeconnect', $params)) {
                $errors['name'] = get_string('duplicatemeetingname', 'adobeconnect');
                return $errors;
            }

            // Check Adobe connect server for duplicated names.
            foreach ($namematches as $match) {
                if (0 == substr_compare($match->name, $data['name'] . '_', 0, strlen($data['name'] . '_'), false)) {
                    $errors['name'] = get_string('duplicatemeetingname', 'adobeconnect');
                }
            }

            foreach ($urlmatches as $match) {
                $matchurl = rtrim($match->url, '/');
                if (0 == substr_compare($matchurl, $url . '_', 0, strlen($url . '_'), false)) {
                    $errors['meeturl'] = get_string('duplicateurl', 'adobeconnect');
                }
            }

        } else {
            // Updating activity
            // Look for existing meeting names, excluding this activity's group meeting(s).
            $grpmeetings = $DB->get_records('adobeconnect_meeting_groups', array('instanceid' => $data['instance']),
                            null, 'meetingscoid, groupid');

            if (empty($grpmeetings)) {
                $grpmeetings = array();
            }

            foreach ($namematches as $match) {
                if (!array_key_exists($match->scoid, $grpmeetings)) {
                    if (0 == substr_compare($match->name, $data['name'] . '_', 0, strlen($data['name'] . '_'), false)) {
                        $errors['name'] = get_string('duplicatemeetingname', 'adobeconnect');
                    }
                }
            }

            foreach ($urlmatches as $match) {
                if (!array_key_exists($match->scoid, $grpmeetings)) {
                    if (0 == substr_compare($match->url, $url . '_', 0, strlen($url . '_'), false)) {
                        $errors['meeturl'] = get_string('duplicateurl', 'adobeconnect');
                    }
                }
            }

            // Validate start and end times.
            if ($data['starttime'] == $data['endtime']) {
                $errors['starttime'] = get_string('samemeettime', 'adobeconnect');
                $errors['endtime'] = get_string('samemeettime', 'adobeconnect');
            } else if ($data['endtime'] < $data['starttime']) {
                $errors['starttime'] = get_string('greaterstarttime', 'adobeconnect');
            }
        }
        aconnect_logout($aconnect);

        if ($aconnect->timeout()) {
               $errors = array('name' => get_string('errortimeout', 'adobeconnect'));
        }

        return $errors;
    }

    public function get_templates() {
        $aconnect = aconnect_login();

        $templatesmeetings = aconnect_get_templates_meetings($aconnect);
        aconnect_logout($aconnect);
        return $templatesmeetings;
    }

}
