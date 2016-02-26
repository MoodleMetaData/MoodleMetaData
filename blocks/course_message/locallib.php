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
 * This is the locallib.php file for the project.  Any functions that are
 * used across several different modules are here.
 *
 * @package    block_course_message
 * @category   block
 * @copyright  2012 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once(dirname(__FILE__).'/../../config.php');
require_once('lib.php');
require_once('attachment_form.class.php');

/** name of the inbox email setting in the user_preferences table */
define('BLOCK_CM_SEND_EMAIL_ON_INBOX', 'block_course_message_email_inbox');
/** name of the sent email setting in the user_preferences table */
define('BLOCK_CM_SEND_EMAIL_ON_SENT', 'block_course_message_email_sent');
/** name of the display preference setting in the user_preferences table */
define('BLOCK_CM_DISPLAY_PREFERENCE', 'block_course_message_display_preference');
/** user wishes to receive email */
define('BLOCK_CM_SEND_EMAIL', 1);
/** user does not wish to receive email */
define('BLOCK_CM_NO_EMAIL', 0);
/** user wants to display mail client on new page */
define('BLOCK_CM_ON_PAGE', 'new_page');
/** user wants to display mail client in iframe */
define('BLOCK_CM_IN_IFRAME', 'iframe');
/** max number of files that can be choosen */
define('BLOCK_CM_MAX_FILES', 20);
/** name of the plugin */
define('BLOCK_CM_LANG_TABLE', 'block_course_message');
/** default max attachment */
define('DEFAULT_MAX_BYTES', 10485760);

/**
 * This is method checks the users mail database and determines whether there is at least one mail
 * message that has been unread.  Unread messages are found by searching for a mail where timeread = 0.
 *
 * @param int $courseid This is the course ID of the course to check for unread mails in.
 * @return int Number of unread mails (0 = none).
 *
 */
function block_course_message_has_unread_mail($courseid) {
    global $USER,
    $DB;

    $context = context_course::instance($courseid);
    $params = array($courseid, $USER->id);
    $query = "SELECT * FROM {course_message_mails} a inner join {course_message_mail_map} b ON a.id = b.mailid
              WHERE a.courseid = ? AND b.userid = ?  AND b.timeread = 0 AND folder='inbox'";
    $records = $DB->get_records_sql($query, $params);

    return count($records);
}

/**
 * This function changes the user's preference as to whether they would like to receive emails or not.
 *
 * @param string $folder Preference to update -> inbox or sent
 * @param bool $shouldemail T/F indicating whether the user would like to receive emails
 * @return bool T/F indicating success/failure
 *
 */
function block_course_message_update_mail_preference($folder, $shouldemail) {
    global $USER;

    $shouldemail = ($shouldemail == "true") ? BLOCK_CM_SEND_EMAIL : BLOCK_CM_NO_EMAIL;
    $result = true;
    $preference = ($folder == 'sent') ? BLOCK_CM_SEND_EMAIL_ON_SENT : BLOCK_CM_SEND_EMAIL_ON_INBOX;

    // Note: set_user_preference() only returns true and throws exceptions if there was trouble.
    try {
        set_user_preference($preference, $shouldemail, $USER->id);
    } catch (Exception $e) {
        $result = false;
    }

    return $result;
}

/**
 * This function gets the user's email setting preference.
 *
 * @param string $folder Preference to update -> inbox or sent
 * @param int $userid the ID of the user to get the mail preference for
 * @return bool T/F indicating email (T) or do not email (F)
 *
 */
function block_course_message_get_mail_preference($folder, $userid) {
    $preference = ($folder == 'sent') ? BLOCK_CM_SEND_EMAIL_ON_SENT : BLOCK_CM_SEND_EMAIL_ON_INBOX;

    $shouldemail = get_user_preferences($preference, 0, $userid);
    if ($shouldemail == BLOCK_CM_SEND_EMAIL) {
        return true;
    } else {
        return false;
    }
}

/**
 * This function changes the user's display preference as to whether the inbox should load in
 * an iframe or on a new page.
 *
 * @param string $displaypreference New display preference ('new_page' | 'iframe')
 * @return bool T/F indicating success/failure
 *
 */
function block_course_message_update_display_preference($displaypreference) {
    global $USER;

    $result = true;
    try {
        set_user_preference(BLOCK_CM_DISPLAY_PREFERENCE, $displaypreference, $USER->id);
    } catch (Exception $e) {
        $result = false;
    }
    return $result;
}

/**
 * This function gets the user's display setting preference.
 *
 * @param int $userid the ID of the user to get the mail preference for
 * @return string User's display preference ('new_page'|'iframe'), defaulting to iframe
 *
 */
function block_course_message_get_display_preference($userid) {
    return BLOCK_CM_ON_PAGE;
}

/**
 * This function gets the user's html editor preference.
 *
 * @param int $userid the ID of the user to get the mail preference for
 * @return string User's preference for html editor ('atto'|'tinymce')
 *
 */
function block_course_message_get_editor_preference($userid) {

    $editor = get_user_preferences('htmleditor', 'atto', $userid);
    if ($editor == null) {
        return 'atto';
    } else {
        return $editor;
    }

}

/**
 * This method takes the JSON encoded list of recipients (as IDS) and returns the actual names of the
 * users and groups in a comma-seperated format.  Sent messages can have multiple recipients, while
 * messages in the inbox are from a single user.
 *
 * @param string $ids This is the JSON encoded set of IDS
 * @param string $folder This ids for the message are in this folder -> sent - get recipients string, inbox - get user from
 * @return string Returns the comma separated list of names belonging to those IDS
 *
 */
function block_course_message_map_ids_to_names($ids, $folder) {
    global $DB;

    $names = '';
    $decodedids = json_decode($ids);

    if ($folder == 'sent') {
        // Note: $firstrecord flag is used to prepend a comma to every record but the first.
        $firstrecord = true;

        foreach ($decodedids as $did) {
            if (!$firstrecord) {
                $names .= ', ';
            }
            switch (substr($did, 0, 1)) {
                case 'g':
                    $groupid = intval(substr($did, 1));
                    $namerecord = $DB->get_record('groups', array('id' => $groupid));
                    $names .= $namerecord->name;
                    break;
                case 'i':
                    $names .= get_string('allinstructors', BLOCK_CM_LANG_TABLE);
                    break;
                case 's':
                    $names .= get_string('allstudents', BLOCK_CM_LANG_TABLE);
                    break;
                default:
                    $namerecord = $DB->get_record('user', array('id' => $did));
                    $names .= "$namerecord->firstname $namerecord->lastname";
            }
            // Processed one record, so we are no longer on the first.
            $firstrecord = false;
        }
    } else {
        /* inbox messages should only ever be from a single user: otherwise there's an error
         * check for !is_int() is probably better, but it may require that calls further up
         * that are pulled from the DB have to use intval(), so I have not made the change */
        if (is_array($decodedids)) {
            return $names;
        }

        $namerecord = $DB->get_record('user', array('id' => $decodedids));
        $names .= "$namerecord->firstname $namerecord->lastname";
    }
    return $names;
}