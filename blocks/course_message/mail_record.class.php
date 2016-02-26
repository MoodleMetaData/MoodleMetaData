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
global $CFG;
require_once($CFG->dirroot.'/blocks/course_message/locallib.php');

/**
 * This is the mail record class for the course message tool.  Currently mails can be sent using
 * its functionality, but I plan to move the viewing functionality here too.
 *
 * @package    block_course_message
 * @copyright  2012 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mail_record {

    const BASE_HEX = 16;
    const BASE_DECIMAL = 10;
    const ITEMID_LENGTH = 9;

    /** This is the message ID */
    public $mailid;
    /** List of recipients in the "to" field*/
    public $mailto;
    /** List of recipients in the "cc" field */
    public $carboncopy;
    /** User IDs for aggregate list of recipients -> {to, cc} */
    public $recipientuserids;
    /** This is the subject of the mail */
    public $subject;
    /** This is the body of the mail */
    public $message;
    /** This is used for conversations, i.e., a reply to another email */
    public $parentmessage;
    /** The type of reply ('single'|'all') based on which button the user clicked */
    public $replytype;
    /** This is the draft id of the attachment in Moodle, the actual attachment ID will be generated when the mail is sent */
    public $draftid;
    /** This is the time when the mail was sent */
    public $timesent;
    /** This indicates whether we should log events */
    public $logging;

    /**
     * This function constructs a mail record.  The record is filled out from the passed parameters.
     *
     * @param int $mailid This is the ID of the mail record (for viewing/deleting), sent mails
     * will have a messaged id generated after inserted into the DB.
     * @param bool $logging T/F indicating if logging should be performed (turn off for unit testing).
     *
     */
    public function __construct($mailid, $logging = true) {
        if (!(is_int($mailid))) {
            return;
        }

        $this->mailid = $mailid;
        $this->logging = $logging;
    }

    /**
     * This function sets up a mail record for sending.
     *
     * @param bool $params This should contain the posted params [subject, message, parentmessage, draftid]
     * @param int|array $mailto This is the list of recipients for the mail (can be int or array of ints)
     * @param array $carboncopy List of users to carboncopy (array|null)
     *
     */
    public function set_send_params($params, $mailto, $carboncopy = null) {
        if (!(is_array($params))) {
            return;
        }
        // Note: null valued mailto is OK, that is how inline reply is setup.
        if (isset($mailto) && !(is_array($mailto))) {
            return;
        }

        $this->mailto = $mailto;
        $this->carboncopy = $carboncopy;
        $this->subject = $params['subject'];
        $this->message = $params['message'];
        $this->parentmessage = $params['parentmessage'];
        $this->replytype = $params['replytype'];
        $this->draftid = $params['draftid'];
        $this->update_no_subject();
        $this->append_moodle_footer();
        $this->timesent = time();
    }

    /**
     * This function checks for an empty subject and replaces it with "No Subject" if found.
     *
     */
    private function update_no_subject() {
        if ($this->subject == "") {
            $this->subject = "No Subject";
        }
    }

    /**
     * This function appends some information to the message and returns it as the emailmessage.  This helps
     * the user realize that it is an automated message sent from Moodle.
     *
     */
    private function append_moodle_footer() {
        global $CFG;

        $this->emailmessage = $this->message;
        $this->emailmessage .= "<br/><br/>----------------------<br/>".get_string('emailnotification', BLOCK_CM_LANG_TABLE);
        $this->emailmessage .= "<br/>".get_string('emaillogin', BLOCK_CM_LANG_TABLE)."$CFG->wwwroot".".";
    }

    /**
     * If an inline reply is sent, no mailto information is posted.  This function is supposed to put
     * the proper recipient in the mailto field and update the subject field.
     *
     * @param int $courseid This is the course id number
     *
     */
    private function update_for_parent($courseid) {
        global $USER, $DB;

        $parentmail = $DB->get_record('course_message_mails', array('id' => $this->parentmessage, 'courseid' => $courseid));

        // If our parent had a parent, set to the initial parent (ie: this mail is a grandchild!).
        if ($parentmail->parentmessage != 0) {
            $this->parentmessage = $parentmail->parentmessage;
        }

        $this->subject = "$parentmail->subject";
        $this->mailto = array($parentmail->useridfrom);

        // Add additional recipients for reply to all.
        if ($this->replytype == 'all') {
            $otherrecipients = json_decode($parentmail->recipients);

            for ($i = 0; $i < count($otherrecipients); $i++) {
                if (intval($otherrecipients[$i]) != $USER->id) {
                    $this->mailto[] = $otherrecipients[$i];
                }
            }
            // Two cases: 1) no new cc -> set to old cc, 2) new cc -> merge with old cc (but remove self).
            if ($this->carboncopy === null) {
                $this->carboncopy = json_decode($parentmail->carboncopy);
            } else {
                $oldcarboncopy = json_decode($parentmail->carboncopy);

                for ($i = 0; $i < count($oldcarboncopy); $i++) {
                    if (intval($oldcarboncopy[$i]) != $USER->id) {
                        $this->carboncopy[] = $oldcarboncopy[$i];
                    }
                }
            }
        }
    }

    /**
     * This function is used to send the mail off (write it to the DB).  It checks for attachments and process
     * them if they exist.
     *
     * @param int $courseid This is the course id number
     *
     */
    public function send_mail($courseid) {
        global $USER;

        if ($this->parentmessage != 0) {
            $this->update_for_parent($courseid);
        }

        if (!(is_array($this->mailto))) {
            echo(json_encode(array('result' => 'false', 'text' => get_string('mailnotsent', BLOCK_CM_LANG_TABLE))));
            return;
        }

        if ($this->parentmessage == 0 && $this->replytype == 'all') {
            echo(json_encode(array('result' => 'false', 'text' => get_string('mailnoparent', BLOCK_CM_LANG_TABLE))));
            return;
        }

        $attachmentid = $this->generate_attachment_id($courseid);

        // Note: assume the mail send was successful.
        $result = true;

        if (!($this->update_mails_db($courseid, $attachmentid))) {
            echo(json_encode(array('result' => 'false', 'text' => get_string('maildberror', BLOCK_CM_LANG_TABLE))));
            return 0;
        }

        // Join all recipients together since they'll all receive a copy.
        $encodedrecipients = ($this->carboncopy) ? array_merge($this->mailto, $this->carboncopy) : $this->mailto;
        $this->check_for_groups($courseid, $encodedrecipients);

        $result = $this->update_mail_map_db($courseid, $USER->id, 'sent');
        foreach ($this->recipientuserids as $m) {
            $result = $this->update_mail_map_db($courseid, $m, 'inbox');
        }

        if ($this->logging == true) {
            $params = array(
                    'context' => context_course::instance($courseid),
                    'objectid' => $this->mailid,
                    'other' => array('attachmentid' => $attachmentid),
                    'courseid' => $courseid,
                    'userid' => $USER->id
            );
            $event = \block_course_message\event\mail_sent::create($params);
            $event->trigger();

            user_accesstime_log($courseid);
        }

        echo(json_encode(array('result' => $result, 'text' => get_string('mailsuccess', BLOCK_CM_LANG_TABLE))));
    }

    /**
     * This function adds the file to Moodle and returns its ID so that it can be stored with the mail.
     * Since I've only sent down the draft ID, an ID in the files table (field: itemid) needs to be generated
     * to store the file(s).  This ID has to be one that does not currently exist in the table, so there's a
     * loop to generate one.
     *
     * @param int $courseid This is the course id number
     * @return int The ID corresponding to the file is returned
     *
     */
    public function generate_attachment_id($courseid) {
        global $DB;

        $context = context_course::instance($courseid);

        // Nonzero draft id means there is an attachment to save.
        if ($this->draftid != 0) {
            // Generate potential attachment IDs until we find one that is unused in the {files} table already.
            // This step must be performed since I did not send down the entry->id.
            // This is how the original author generated a random number, so I've left it as is.
            do {
                // 1) Use uniqid to generate random string based on time.
                // 2) Compute its md5 hash (returned as 32 character hex).
                // 3) Convert to decimal (base 36 -> base 10).
                // 4) Select the first 9 digits (itemids are 9 digits long).
                $attachmentid = substr(base_convert(md5(uniqid('', true)), self::BASE_HEX, self::BASE_DECIMAL),
                                0, self::ITEMID_LENGTH);
            } while ($DB->count_records('files', array('itemid' => $attachmentid)) > 0);
            file_save_draft_area_files($this->draftid, $context->id, BLOCK_CM_COMPONENT_NAME, BLOCK_CM_FILE_AREA_NAME,
                                       $attachmentid, array('maxfiles' => BLOCK_CM_MAX_FILES));
        } else {
            $attachmentid = 0;
        }

        return $attachmentid;
    }

    /**
     * This function checks for any groups in the encoded recipients list.  When a group is found, the users of that
     * group are queried, exploded, and added to the list.
     *
     * @param int $courseid The ID of the course for which the instructor/student lists should be populated.
     * @param array $encodedrecipients The list of recipients, formatted as it is when it comes from javascript.
     *
     */
    private function check_for_groups($courseid, $encodedrecipients) {
        global $DB, $USER;

        $newmailto = array();
        $i = 0;

        foreach ($encodedrecipients as $m) {
            $temp = strval($m);

            if (substr($temp, 0, 1) == 'g') {
                $groupid = substr($temp, 1);
                $groupint = intval($groupid);
                $params = array($groupint, $USER->id);
                $query = "SELECT * FROM {groups_members} WHERE groupid = ? AND userid <> ?";
                $members = $DB->get_records_sql($query, $params);

                foreach ($members as $mem) {
                    $newmailto[$i] = $mem->userid;
                    $i++;
                }
            } else if (substr($temp, 0, 1) == 'i') {
                $context = context_course::instance($courseid);
                $members = get_users_by_capability($context, 'block/course_message:receiveallinstructorcoursemail');

                foreach ($members as $mem) {
                    $newmailto[$i] = $mem->id;
                    $i++;
                }
            } else if (substr($temp, 0, 1) == 's') {
                $context = context_course::instance($courseid);
                $members = get_users_by_capability($context, 'block/course_message:receiveallstudentcoursemail');

                foreach ($members as $mem) {
                    $newmailto[$i] = $mem->id;
                    $i++;
                }
            } else {
                $newmailto[$i] = $m;
                $i++;
            }
        }

        $this->recipientuserids = array_unique($newmailto);
        // Strip out the keys so they are not stored in the DB.
        $this->recipientuserids = array_values($this->recipientuserids);
    }

    /**
     * This is function is responsible for updating the mails DB.  Even with multiple recipients
     * only one copy of the mail is stored.  This stores the master copy of the mail.
     *
     * @param int $courseid This is the course id number
     * @param int $uniqueid This is the id number of the attachment
     * @return bool T/F indicating whether the DB insert was successful or not
     */
    private function update_mails_db($courseid, $uniqueid) {
        global $USER, $DB;

        $dataobject = new stdClass();
        $dataobject->courseid = $courseid;
        $dataobject->useridfrom = $USER->id;
        $dataobject->recipients = json_encode($this->mailto);
        $dataobject->carboncopy = ($this->carboncopy === null) ? null : json_encode($this->carboncopy);
        $dataobject->subject = $this->subject;
        // Fix up any <br> tags - not well formed.
        $dataobject->message = str_replace("<br>", "<br />", $this->message);
        $dataobject->timesent = $this->timesent;
        $dataobject->parentmessage = $this->parentmessage;
        $dataobject->attachment = $uniqueid;

        $this->mailid = $DB->insert_record('course_message_mails', $dataobject, true);

        if ($this->mailid > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * This is function is responsible for updating the mail map DB.  The function
     * sets up a single copy of the mail for the recipient in the desired folder.  To
     * handle the sent messages copy, set the folder to 'sent' and the recipient to be
     * the current user (the one sending the mail).
     *
     * Additionally, if the recipient wants to receive email copies, then an email is
     * sent out.
     *
     * @param int $courseid This is the course id number
     * @param int $recipient This is the recipient
     * @param string $folder This is the folder to place the mail in
     * @return bool T/F indicating whether the DB insert was successful or not
     */
    private function update_mail_map_db($courseid, $recipient, $folder) {
        global $USER, $DB, $CFG;

        $dataobject = new stdClass();
        $dataobject->mailid = $this->mailid;
        $dataobject->userid = intval($recipient);
        $dataobject->folder = $folder;
        $dataobject->timeread = 0;

        $newid = $DB->insert_record('course_message_mail_map', $dataobject, true);

        $cname = $DB->get_record('course', array('id' => $courseid));

        if (block_course_message_get_mail_preference($folder, $dataobject->userid) && !PHPUNIT_TEST) {
            $subjectemail = "[$cname->fullname] - $this->subject";
            $userto = $DB->get_record('user', array('id' => $recipient));
            // USERID -> $userfrom = $DB->get_record('user', array('id' => $USER->id));.
            $userfrom = 'noreply@ualberta.ca';
            // If $usertrueaddress is true, make sure that $userfrom becomes a valid $USER object -> see commented line above.
            email_to_user($userto, $userfrom, $subjectemail, $this->emailmessage, $this->emailmessage, $attachment = '',
                          $attachname = '', $usetrueaddress = false, 'noreply@ualberta.ca', 'noreply', $wordwrapwidth = 79);
        }

        if ($newid > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * This function is used to delete the current mail, based on the ID field that it contains.
     *
     * @param int $courseid This is the course id number (used only for logging)
     *
     */
    public function delete_mail($courseid) {
        global $DB, $USER;

        // Check here to ensure the record actually exists and the mail ID was not spoofed.
        if ($DB->count_records('course_message_mail_map', array('mailid' => $this->mailid, 'userid' => "$USER->id")) == 0) {
            // No message to the user, no need to give would-be hackers more information.
            return;
        }

        $DB->delete_records('course_message_mail_map', array('mailid' => $this->mailid, 'userid' => "$USER->id"));

        // Was this the last reference in the map to that mail ID?
        if ($DB->count_records('course_message_mail_map', array('mailid' => $this->mailid)) == 0) {
            // Grab the attachment ID before it is deleted.
            $record = $DB->get_record('course_message_mails', array('id' => $this->mailid));
            $attachmentid = $record->attachment;
            // Mail in mails table has no references, so remove it.
            $DB->delete_records('course_message_mails', array('id' => $this->mailid));
            // Now check for attachments.
            if ($attachmentid != 0) {
                // Are there records to delete?
                if ($DB->count_records('files', array('itemid' => $attachmentid, 'component' => BLOCK_CM_COMPONENT_NAME)) > 0) {
                    $DB->delete_records('files', array('itemid' => $attachmentid, 'component' => BLOCK_CM_COMPONENT_NAME));
                }
            }
        }

        if ($this->logging == true) {
            $params = array(
                'context' => context_course::instance($courseid),
                'objectid' => $this->mailid,
                'other' => array('attachmentid' => $attachmentid),
                'courseid' => $courseid,
                'userid' => $USER->id
            );
            $event = \block_course_message\event\mail_deleted::create($params);
            $event->trigger();

            user_accesstime_log($courseid);
        }
    }
}