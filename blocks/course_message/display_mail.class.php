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
 * This is the display record class for the course message tool.  It contains the basic
 * functionality to display a mail (including the conversation thread) and update its
 * time read.
 *
 * @package    block_course_message
 * @copyright  2012 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class display_mail{
    /** This is the mail that is being viewed */
    public $viewrecord;
    /** This is the mail ID */
    private $mailid;
    /** This is the folder of the mail we are displaying */
    private $folder;
    /** This is the parent ID */
    private $parentid;
    /** This is all of the mails linked to the parent */
    public $threadmails;

    /**
     * This method constructs a display record.  It retrieves the mail itself, along with its parent
     * (if it has one) and the entire threaded conversation.
     *
     * @param int $mailid This is the ID of the mail record to display.
     * @param string $folder This is the folder the mail is in.
     * @param int $courseid This is the course ID
     * @param bool $logging T/F indicating if logging should be performed (turn off for unit tests)
     *
     */
    public function __construct($mailid, $folder, $courseid, $logging = true) {
        global $DB, $USER;

        $this->folder = $folder;
        $this->viewrecord = $DB->get_record('course_message_mails', array('id' => $mailid));
        $this->threadmails = null;

        // Store IDs for convenience.
        $this->mailid = $mailid;
        $this->parentid = $this->viewrecord->parentmessage;

        if ($this->parentid != 0) {
            $this->fetch_thread($courseid);
        }
        if ($this->check_for_children($courseid)) {
            $this->parentid = $this->mailid;
            $this->fetch_thread($courseid);
        }

        if ($logging == true) {
            $params = array(
                'context' => context_course::instance($courseid),
                'objectid' => $mailid,
                'other' => array('folder' => $folder,
                                 'parentid' => $this->parentid),
                'courseid' => $courseid,
                'userid' => $USER->id
            );
            $event = \block_course_message\event\mail_viewed::create($params);
            $event->trigger();

            user_accesstime_log($courseid);
        }
    }

    /**
     * This method checks the user's identification to see if they can view the mail that was chosen.
     *
     * @param string $folder This is the folder the mail is in.
     *
     */
    public function check_user_identification($folder) {
        global $USER, $DB;

        // Check that the user actually has a copy of the mail.
        $params = array($this->mailid, $USER->id, $folder);
        $query = "SELECT * FROM {course_message_mail_map} WHERE mailid = ? AND userid = ? AND folder = ?";
        $map = $DB->get_records_sql($query, $params);
        if (count($map) < 1) {
            return false;
        }

        // When viewing a sent mail, make sure it is from this user.
        if ($folder == 'sent') {
            if ($this->viewrecord->useridfrom != $USER->id) {
                return false;
            }
        }

        return true;
    }

    /**
     * This method updates the read time for the mail.
     *
     */
    public function update_time_read() {
        global $DB, $USER;

        $params = array($this->mailid, $USER->id, $this->folder);
        $query = "SELECT * FROM {course_message_mail_map} WHERE mailid = ? AND userid = ? AND folder = ?";
        $mail = $DB->get_record_sql($query, $params);
        $mail->timeread = time();
        $DB->update_record('course_message_mail_map', $mail);
    }

    /**
     * This method checks to see if the current mail has any children.
     *
     * @param int $courseid This is the course ID.
     * @return bool T/F indicating if the mail has any children.
     *
     */
    private function check_for_children($courseid) {
        global $DB;

        $count = $DB->count_records('course_message_mails', array('courseid' => $courseid, 'parentmessage' => $this->mailid));
        $result = ($count > 0) ? true : false;
        return $result;
    }

    /**
     * This method grabs the threaded conversation from the database.
     *
     * @param int $mailid This is the ID of the mail record to display.
     *
     */
    private function fetch_thread($courseid) {
        global $DB,
        $USER;

        $params = array($courseid, $this->parentid, $this->parentid, $USER->id);
        /* I think this query needs to have all fields typed out explicitly since I've added the distinct
         * This can cause a common error if new fields are added to the mails DB
         * This query also restricts visibility -> the user must have a copy of the mail in their mail map
         * or it will not be retrieved. */
        $query = "SELECT DISTINCT a.id, a.courseid, a.useridfrom, a.recipients, a.subject, a.message, a.timesent,
                  a.parentmessage, a.attachment, a.carboncopy
                  FROM {course_message_mails} a inner join {course_message_mail_map} b ON a.id = b.mailid
                  WHERE a.courseid = ? AND (a.parentmessage = ? OR a.id = ?) AND b.userid = ? ORDER BY timesent ASC";
        $this->threadmails = $DB->get_records_sql($query, $params);
    }

    /**
     * This method returns whether (T) or not (F) the mail is part of a thread.
     *
     * @return bool T/F indicating if the mail is part of a thread.
     *
     */
    public function is_thread() {
        if ($this->parentid == 0) {
            return false;
        } else {
            return true;
        }
    }
}