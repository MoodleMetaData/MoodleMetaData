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

defined('MOODLE_INTERNAL') || die();
require_once(dirname(__FILE__).'/../../config.php');
require_once('mail_record.class.php');
require_once('folder_records.class.php');
require_once('display_mail.class.php');
require_once('mail_view.class.php');
global $CFG;
require_once($CFG->dirroot.'/blocks/course_message/locallib.php');

/**
 * This is the main controller class that handles all of the ajax requests.
 *
 * @package    block_course_message
 * @copyright  2013 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ajax_controller {

    /** Hold the courseid */
    private $courseid;

    /**
     * Empty constructor for the time being.
     *
     */
    public function __construct() {
    }

    /**
     * This is function verifies that the user has basic access to this page.  More detailed checks
     * may be performed later depending on the action.
     *
     * @param int $requesttype The type of the ajax request.
     *
     */
    public function verify_access($requesttype) {
        // Whether or not to output JSON depends on the type of request (view mail just outputs directly).
        $outputjson = ($requesttype == 'view') ? false : true;
        $this->courseid = required_param('courseid', PARAM_INT);
        // Require users to be logged in, but do not redirect to login page -> we'll tell the user manually.
        try {
            require_login($this->courseid, false, null, false, true);
        } catch (Exception $e) {
            if ($outputjson === true) {
                echo(json_encode(array('result' => 'false', 'text' => get_string('mailnologin', BLOCK_CM_LANG_TABLE))));
            } else {
                echo '<p>'.get_string('mailnologin', BLOCK_CM_LANG_TABLE).'</p>';
            }
            return false;
        }
        if (!confirm_sesskey(required_param("sesskey", PARAM_TEXT))) {
            if ($outputjson === true) {
                echo(json_encode(array('result' => 'false', 'text' => get_string('mailbadsesskey', BLOCK_CM_LANG_TABLE))));
            } else {
                echo '<p>'.get_string('mailbadsesskey', BLOCK_CM_LANG_TABLE).'</p>';
            }
            return false;
        }
        return true;
    }

    /**
     * This is function dispatches the request based on its type.
     *
     * @param int $requesttype The type of the ajax request.
     *
     */
    public function perform_request($requesttype) {
        switch ($requesttype) {
            case 'delete':
                $this->delete_mail();
                break;
            case 'send':
                $this->send_mail();
                break;
            case 'inbox':
                $this->get_folder('inbox');
                break;
            case 'sent':
                $this->get_folder('sent');
                break;
            case 'check_message':
                $this->check_message();
                break;
            case 'view_settings':
                $this->view_settings();
                break;
            case 'edit_settings':
                $this->edit_settings();
                break;
            case 'view':
                $this->view_mail();
                break;
        }
    }

    /**
     * This is function deletes a mail according to the parameter in the post request.
     * the records to the page.
     *
     */
    private function delete_mail() {
        $msgid = required_param('messageid', PARAM_INT);
        $mailrecord = new mail_record($msgid);
        $mailrecord->delete_mail($this->courseid);
    }

    /**
     * This is function sends mail using the parameters that are in the post request.
     *
     */
    private function send_mail() {

        $context = context_course::instance($this->courseid);
        if (!has_capability('block/course_message:sendmail', $context)) {
            echo(json_encode(array('result' => false, 'text' => get_string('usercannotsendmail', BLOCK_CM_LANG_TABLE))));
            return;
        }

        // Grab the posted paramaters - slows things down, but allows for easier unit testing.
        $params = array(
                "subject" => optional_param('subject', "no subject", PARAM_TEXT),
                "message" => required_param('message', PARAM_CLEANHTML),
                "parentmessage" => optional_param('parent', 0, PARAM_INT),
                "replytype" => optional_param('replytype', 'single', PARAM_TEXT),
                "draftid" => optional_param(BLOCK_CM_FILE_AREA_NAME, 0, PARAM_INT));

        /* If the optional param does not find mailTo (it is not sent on inline replies), then it will
         * already be setup as an array and not need to be JSON decoded. */
        $temp = optional_param('mailTo', array("1"), PARAM_TEXT);
        $mailto = (!is_array($temp)) ? json_decode($temp) : $temp;
        // The carboncopy field is actually optional: json decode it if it exists, set to null otherwise.
        $temp = optional_param('cc', null, PARAM_TEXT);
        $carboncopy = ($temp === null) ? $temp : json_decode($temp);

        $mailrecord = new mail_record(0);
        $mailrecord->set_send_params($params, $mailto, $carboncopy);
        $mailrecord->send_mail($this->courseid);
    }

    /**
     * This is function grabs the records from a particular folder, encodes them in json, and writes
     * the records to the page.
     *
     */
    private function get_folder($folder) {
        global $USER;

        $fr = new folder_records($folder, $this->courseid);

        $tablerows = array();
        // Changed call here because call-time pass-by-reference is deprecated in php 5.4.
        $fr->get_table_rows($tablerows, 0);
        $table = array('rows' => $tablerows);
        echo json_encode($table);
    }

    /**
     * This function checks to see if the user has new messages and echos the number of unread
     * records accordingly.  See locallib.php for the block_course_message_has_unread_mail() function.
     *
     */
    private function check_message() {
        $totalrecords = block_course_message_has_unread_mail($this->courseid);
        if ($totalrecords > 0) {
            echo "{$totalrecords}";
        } else {
            echo "0";
        }
    }

    /**
     * This is function displays all user settings.
     *
     * @return int This function returns 0 always.
     *
     */
    private function view_settings() {
        global $USER;

        $inboxresult = block_course_message_get_mail_preference('inbox', $USER->id);
        $sentresult = block_course_message_get_mail_preference('sent', $USER->id);
        $displaypreference = block_course_message_get_display_preference($USER->id);
        echo json_encode(array('inbox' => $inboxresult, 'sent' => $sentresult, 'displaypreference' => $displaypreference));
        // TODO: remove the return value of 0 - no longer used.
        return 0;
    }

    /**
     * This function edits the user's settings -> see locallib.php for the logic.
     *
     */
    private function edit_settings() {
        global $USER;

        $inboxsetting = required_param('inboxsetting', PARAM_TEXT);
        $sentsetting = required_param('sentsetting', PARAM_TEXT);
        $displaypreference = required_param('displayonpagesetting', PARAM_TEXT);
        $olddisplaypreference = block_course_message_get_display_preference($USER->id);
        $displaypreference = ($displaypreference == "new_page") ? BLOCK_CM_ON_PAGE : BLOCK_CM_IN_IFRAME;
        $result = true;
        $result = block_course_message_update_mail_preference('inbox', $inboxsetting);
        $result = block_course_message_update_mail_preference('sent', $sentsetting);
        $result = block_course_message_update_display_preference($displaypreference);

        if ($result) {
            echo json_encode(array('result' => true, 'text' => get_string('updatesettings', BLOCK_CM_LANG_TABLE)));
        } else {
            echo json_encode(array('result' => false, 'text' => get_string('updatesettingserror', BLOCK_CM_LANG_TABLE)));
        }
    }

    private function view_mail() {

        $context = context_course::instance($this->courseid);
        if (!has_capability('block/course_message:viewmail', $context)) {
            echo get_string('usercannotviewmail', BLOCK_CM_LANG_TABLE);
            return;
        }

        $mailid = required_param('id', PARAM_INT);
        $folder = required_param('folder', PARAM_TEXT);

        $mail = new display_mail($mailid, $folder, $this->courseid);
        if (!$mail->check_user_identification($folder)) {
            echo get_string('baduseridentification', BLOCK_CM_LANG_TABLE);
            return 0;
        }

        $view = new mail_view($mail, $mailid, $folder);
        $view->display_mail();
    }
}