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
global $CFG;
require_once($CFG->dirroot.'/blocks/course_message/mail_record.class.php');
require_once($CFG->dirroot.'/blocks/course_message/tests/mailunittest.php');

/**
 * This is the unittest class for mail_record.class.php.
 *
 * The following functions are checked:
 * 1) constructor
 * 2) set_send_params()
 * 3) update_no_subject()
 * 4) append_moodle_footer()
 * 5) send_mail() -> standard send + inline reply (cc included) + reply-all
 * 6) check_for_groups() -> in both to and cc fields
 * 7) generate_attachment_id()
 * 8) delete_mail()
 * 
 * I also check the mass mail send for "all students" and "all instructors".
 *
 * @package    block_course_message
 * @group      block_course_message_tests
 * @copyright  2012 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class test_mailrecord extends mail_unit_test {
    /** Printed message for successful mail delivery */
    const MAILSUCCESS = '{"result":true,"text":"Your mail was sent successfully."}';
    /** Printed message for invalid reply */
    const MAILBADREPLY = '{"result":"false","text":"Cannot find mail you are replying to."}';

    /**
     * This function tests the constructor to ensure that the message ID is set.
     *
     */
    public function test_constructor() {
        $mailrecord = new mail_record(5);

        $this->assertEquals($mailrecord->mailid, 5);
    }

    /**
     * This function tests the set_send_params() method to ensure the values are
     * getting set properly.
     *
     */
    public function test_set_send_params() {
        $params = array(
                "subject" => 'This is a subject',
                "message" => 'This is a <b>message</b>',
                "parentmessage" => 0,
                "replytype" => 'single',
                "draftid" => 0);

        $mailto = array("1");

        $mailrecord = new mail_record(0);
        $mailrecord->set_send_params($params, $mailto);

        $this->assertEquals($mailrecord->subject, $params["subject"]);
        $this->assertEquals($mailrecord->message, $params["message"]);
        $this->assertEquals($mailrecord->parentmessage, $params["parentmessage"]);
        $this->assertEquals($mailrecord->draftid, $params["draftid"]);
        $this->assertEquals($mailrecord->mailto, $mailto);
    }

    /**
     * This function tests the no subject checker to see if it is corrected
     * properly.
     *
     */
    public function test_no_subject() {
        $params = array(
                "subject" => '',
                "message" => 'This is a message',
                "parentmessage" => 0,
                "replytype" => 'single',
                "draftid" => 0);

        $mailto = array("1");

        $mailrecord = new mail_record(0);
        $mailrecord->set_send_params($params, $mailto);

        // Verify that subject was changed.
        $this->assertEquals($mailrecord->subject, 'No Subject');
    }

    /**
     * This function tests to ensure that the email footer is being properly
     * added to the emailmessage field.
     *
     */
    public function test_append_moodle_footer() {
        global $CFG;

        $params = array(
                "subject" => 'This is a subject',
                "message" => 'This is a message',
                "parentmessage" => 0,
                "replytype" => 'single',
                "draftid" => 0);

        $mailto = array("1");

        $mailrecord = new mail_record(0);
        $mailrecord->set_send_params($params, $mailto);

        $this->assertEquals($mailrecord->emailmessage, $params["message"]."<br/><br/>----------------------<br/>".
            get_string('emailnotification', BLOCK_CM_LANG_TABLE)."<br/>".
            get_string('emaillogin', BLOCK_CM_LANG_TABLE).
            "$CFG->wwwroot".".");
    }

    /**
     * This function tests sending a mail without an attachment, then checks the
     * DB to ensure that it exists.
     *
     */
    public function test_send_mail() {

        $params = array(
                "subject" => 'Test Subject',
                "message" => 'single user send',
                "parentmessage" => 0,
                "replytype" => 'single',
                "draftid" => 0);
        $mailto = array("{$this->friend->id}");
        $this->setUser($this->craig);
        $mailrecord = new mail_record(0, false);
        $mailrecord->set_send_params($params, $mailto);
        $this->send_mail($mailrecord, $this->testcourseid);

        $mailid = $this->check_mails_db_by_message($params['message'], $this->testcourseid, $this->craig->id,
                json_encode($mailto), $params['subject']);
        $this->check_multiple_mail_map($mailid, 2, array($this->craig->id, $this->friend->id), array('sent', 'inbox'));

        // Now try to carbon copy to another user.
        $carboncopy = array("{$this->martha->id}");
        $params["message"] = 'CC user send';
        $mailrecord = new mail_record(0, false);
        $mailrecord->set_send_params($params, $mailto, $carboncopy);
        $this->send_mail($mailrecord, $this->testcourseid);

        $mailid = $this->check_mails_db_by_message($params['message'], $this->testcourseid, $this->craig->id,
                json_encode($mailto), $params['subject'], 0, 0, json_encode($carboncopy));
        $this->check_multiple_mail_map($mailid, 3, array($this->craig->id, $this->friend->id, $this->martha->id),
                                       array('sent', 'inbox', 'inbox'));
    }

    /**
     * This function tests the inline mail response to ensure that mails are
     * sent properly.  After sending, I check the DB to ensure that the mail
     * exists.
     *
     */
    public function test_send_inline_mail() {

        $params = array(
                "subject" => 'This will get replaced',
                "message" => 'Use this text to find the message',
                // I have chosen message 1, but any could be used: just update the from field below.
                "parentmessage" => $this->mailids[0],
                "replytype" => 'single',
                "draftid" => 0);

        // This is the default mailto value for empty mails.
        $mailto = array("1");
        $this->setUser($this->friend);

        $mailrecord = new mail_record(0, false);
        $mailrecord->set_send_params($params, $mailto);
        $this->send_mail($mailrecord, $this->testcourseid);

        $mailid = $this->check_mails_db_by_message($params['message'], $this->testcourseid, $this->friend->id,
                json_encode(array("{$this->craig->id}")), 'Test Mail 1', $this->mailids[0]);
        $this->check_multiple_mail_map($mailid, 2, array($this->friend->id, $this->craig->id), array('sent', 'inbox'));

        // Now try to carbon copy to another user.
        $carboncopy = array("{$this->martha->id}");
        $params["message"] = 'new CC on inline reply';
        $mailrecord = new mail_record(0, false);
        $mailrecord->set_send_params($params, $mailto, $carboncopy);
        $this->send_mail($mailrecord, $this->testcourseid);
        $mailid = $this->check_mails_db_by_message($params['message'], $this->testcourseid, $this->friend->id,
                json_encode(array("{$this->craig->id}")), 'Test Mail 1', $this->mailids[0], 0, json_encode($carboncopy));
        $this->check_multiple_mail_map($mailid, 3, array($this->friend->id, $this->craig->id, $this->martha->id),
                array('sent', 'inbox', 'inbox'));

        // Now reply to mail with CC -> should only go back to friend, but not to the CC (Martha).
        $this->setUser($this->craig);
        $params["parentmessage"] = $mailid;
        $params["message"] = 'reply to mail with CC';
        $mailrecord = new mail_record(0, false);
        $mailrecord->set_send_params($params, $mailto);
        $this->send_mail($mailrecord, $this->testcourseid);
        // Note that the parent will still be the first mail in the chain.
        $mailid = $this->check_mails_db_by_message($params['message'], $this->testcourseid, $this->craig->id,
                json_encode(array("{$this->friend->id}")), 'Test Mail 1', $this->mailids[0]);
        $this->check_multiple_mail_map($mailid, 2, array($this->craig->id, $this->friend->id), array('sent', 'inbox'));
    }

    /**
     * This function tests the group mailing features.  It does not test the
     * mail funciton itself, since that was tested above.  Two mails are sent:
     * one to just a group and another to a group and a separate user.  After
     * the mails are sent, the DB is queried to ensure that the records were
     * added.
     * 
     * TODO: it might be a good idea to add a reply with a CC to both a user
     * and a group.
     *
     */
    public function test_group_send_mail() {

        $params = array(
                "subject" => 'Group Test Subject',
                "message" => 'Group Test Message: 3->4',
                "parentmessage" => 0,
                "replytype" => 'single',
                "draftid" => 0);

        $this->setUser($this->craig);
        $mailrecord = new mail_record(0, false);
        $mailrecord->set_send_params($params, array("g{$this->testgroupid}"));
        $mailid = $this->send_mail($mailrecord, $this->testcourseid);
        $this->check_group_mail_map_count($mailid);

        // Now change the message, subject, add friend as recipient and send another mail.
        $params["subject"] = 'Group Test Subject 2';
        $params["message"] = 'Group Test Message 2: to->group + friend';
        $mailrecord = new mail_record(0, false);
        $mailrecord->set_send_params($params, array("g{$this->testgroupid}", "{$this->friend->id}"));
        $mailid = $this->send_mail($mailrecord, $this->testcourseid);
        $this->check_group_mail_map_count($mailid);
        // Check that it was sent to {friend} as well.
        $this->check_inbox_mail_map_count($mailid, $this->friend->id, 1);

        // Now change the message, subject, send to single user, but copy to group and another user.
        $params["subject"] = 'Group Test Subject 3';
        $params["message"] = 'Group Test Message 3: to->martha, cc->group + friend';
        $mailrecord = new mail_record(0, false);
        $mailrecord->set_send_params($params, array("{$this->martha->id}"), array("g{$this->testgroupid}", "{$this->friend->id}"));
        $mailid = $this->send_mail($mailrecord, $this->testcourseid);
        $this->check_group_mail_map_count($mailid);
        // Check that it was sent to {friend} as well (via the CC).
        $this->check_inbox_mail_map_count($mailid, $this->friend->id, 1);
    }

    /**
     * This function tests send to all instructors feature.  I send a test mail
     * and then check that the instructors received the mail, but the admin user
     * and students have not.
     *
     */
    public function test_send_all_instructors() {

        $params = array(
                "subject" => 'All Instructor Test Subject',
                "message" => 'Mail sent to all instructors',
                "parentmessage" => 0,
                "replytype" => 'single',
                "draftid" => 0);

        $this->setUser($this->friend);
        $mailrecord = new mail_record(0, false);
        $mailrecord->set_send_params($params, array("i1"));
        $mailid = $this->send_mail($mailrecord, $this->testcourseid);
        $this->check_instructor_mail_map_count($mailid);
    }

    /**
     * This function tests send to all students feature.  I send a test mail
     * and then check that the students received the mail and that the admin
     * user has not.  All of my users are students, one probably should be
     * made just an instructor to improve the test a bit.
     *
     */
    public function test_send_all_students() {

        $params = array(
                "subject" => 'All Student Test Subject',
                "message" => 'Mail sent to all students',
                "parentmessage" => 0,
                "replytype" => 'single',
                "draftid" => 0);

        $this->setUser($this->friend);
        $mailrecord = new mail_record(0, false);
        $mailrecord->set_send_params($params, array("s1"));
        $mailid = $this->send_mail($mailrecord, $this->testcourseid);
        $this->check_student_mail_map_count($mailid);
    }

    /**
     * This function tests the reply to all feature.  Most of the logic that is
     * tested is in the update_for_parent() routine.  But it also relies upon the
     * check_for_groups() routine in mail_record.class.php as well.
     *
     */
    public function test_reply_to_all() {

        // Step 1) send mail from Craig to group + Friend.
        $params = array(
                "subject" => 'Reply-to-all Test Subject',
                "message" => 'This message will be used to test reply-to-all.',
                "parentmessage" => 0,
                "replytype" => 'single',
                "draftid" => 0);
        $this->setUser($this->craig);
        $mailrecord = new mail_record(0, false);
        $mailrecord->set_send_params($params, array("g{$this->testgroupid}", "{$this->friend->id}"));
        $mailid = $this->send_mail($mailrecord, $this->testcourseid);

        /* Step 2) reply to all from "friend" (goes to "craig", "martha", "wade"
         * -> but only one copy for "craig", no inbox copy to "friend".*/
        $this->setUser($this->friend);
        $params = array(
                "subject" => 'This will get replaced',
                "message" => 'Reply to all from friend.',
                "parentmessage" => $mailid,
                "replytype" => 'all',
                "draftid" => 0);
        $mailrecord = new mail_record(0, false);
        $mailrecord->set_send_params($params, array("1"));
        $replyid = $this->send_mail($mailrecord, $this->testcourseid);

        $this->check_inbox_mail_map_count($replyid, $this->craig->id, 1);
        $this->check_inbox_mail_map_count($replyid, $this->friend->id, 0);
        $this->check_inbox_mail_map_count($replyid, $this->martha->id, 1);
        $this->check_inbox_mail_map_count($replyid, $this->wade->id, 1);

        /* Step 3) reply to all from "martha" (goes to "craig", "friend", "wade"
         * -> but only one copy for "craig", no inbox copy to "martha". */
        $this->setUser($this->martha);
        // Make sure Martha doesn't get emails.
        block_course_message_update_mail_preference('sent', 'false');
        block_course_message_update_mail_preference('inbox', 'false');

        $params = array(
                "subject" => 'This will get replaced',
                "message" => 'Reply to all from Martha.',
                "parentmessage" => $mailid,
                "replytype" => 'all',
                "draftid" => 0);
        $mailrecord = new mail_record(0, false);
        $mailrecord->set_send_params($params, array("1"));
        $replyid = $this->send_mail($mailrecord, $this->testcourseid);

        $this->check_inbox_mail_map_count($replyid, $this->craig->id, 1);
        $this->check_inbox_mail_map_count($replyid, $this->friend->id, 1);
        $this->check_inbox_mail_map_count($replyid, $this->martha->id, 0);
        $this->check_inbox_mail_map_count($replyid, $this->wade->id, 1);

        // Step 4) test reply to all with no parent (returns as fail).
        $this->setAdminUser();
        $params["parentmessage"] = 0;
        $mailrecord = new mail_record(0, false);
        $mailrecord->set_send_params($params, array("1"));
        $this->send_mail($mailrecord, $this->testcourseid, 'bad reply');
    }

    /**
     * I have opted to test the CC functionality of reply-all on its own because
     * I have setup the initial email differently.
     *
     */
    public function test_reply_to_all_with_cc() {

        // Step 1) send mail from Craig to friend and cc to Martha.
        $params = array(
                "subject" => 'Reply-to-all Test Subject',
                "message" => 'This message will be used to test reply-to-all.',
                "parentmessage" => 0,
                "replytype" => 'single',
                "draftid" => 0);
        $this->setUser($this->craig);
        $mailrecord = new mail_record(0, false);
        $mailrecord->set_send_params($params, array("{$this->friend->id}"), array("{$this->martha->id}"));
        $mailid = $this->send_mail($mailrecord, $this->testcourseid);

        // Step 2) reply-all from "friend" (cc preserved so Martha gets copy).
        $this->setUser($this->friend);
        $params = array(
            "subject" => 'This will get replaced',
            "message" => 'Reply to all from friend.',
            "parentmessage" => $mailid,
            "replytype" => 'all',
            "draftid" => 0);
        $mailrecord = new mail_record(0, false);
        $mailrecord->set_send_params($params, array("1"));
        $replyid = $this->send_mail($mailrecord, $this->testcourseid);

        $this->check_inbox_mail_map_count($replyid, $this->craig->id, 1);
        $this->check_inbox_mail_map_count($replyid, $this->friend->id, 0);
        $this->check_inbox_mail_map_count($replyid, $this->martha->id, 1);

        // Step 3) reply-all from "friend" with new CC of Wade.
        $this->setUser($this->friend);
        $params = array(
            "subject" => 'This will get replaced',
            "message" => 'Reply to all from friend.',
            "parentmessage" => $mailid,
            "replytype" => 'all',
            "draftid" => 0);
        $mailrecord = new mail_record(0, false);
        $mailrecord->set_send_params($params, array("1"), array("{$this->wade->id}"));
        $replyid = $this->send_mail($mailrecord, $this->testcourseid);

        $this->check_inbox_mail_map_count($replyid, $this->craig->id, 1);
        $this->check_inbox_mail_map_count($replyid, $this->friend->id, 0);
        $this->check_inbox_mail_map_count($replyid, $this->martha->id, 1);
        $this->check_inbox_mail_map_count($replyid, $this->wade->id, 1);
    }

    /**
     * This function tests generating a unique attachment ID for the mail.
     *
     */
    public function test_generate_attachment_id() {
        global $DB;

        $params = array(
                "subject" => 'Group Test Subject',
                "message" => 'Group Test Message',
                "parentmessage" => 0,
                "replytype" => 'single',
                "draftid" => 123456789);
        $this->setAdminUser();
        $mailrecord = new mail_record(0);
        $mailrecord->set_send_params($params, array("{$this->friend->id}"));
        $attachmentid = $mailrecord->generate_attachment_id($this->testcourseid);

        // Is the ID unique? Yes -> pass.
        $records = $DB->get_records('files', array('itemid' => $attachmentid));
        $this->assertEquals(count($records), 0);

    }

    /**
     * This function tests deleting a mail.  One of the emails in the starter
     * DB is deleted, then I check to make sure it is gone.
     *
     */
    public function test_delete_mail() {
        global $DB;
        $courseid = $this->testcourseid;

        // Try deleting a mail the user does not own.
        $this->setUser($this->martha);

        // Grab the mail with an attachment.
        $query = "SELECT * FROM {course_message_mails} WHERE message = 'This mail will be deleted.'";
        $record = $DB->get_record_sql($query);
        $mailid = intval($record->id);

        // Note the explicit conversion to an int here.
        $mailrecord = new mail_record($mailid, false);
        $mailrecord->delete_mail($courseid);
        // Make sure no copies were deleted.
        $res = $DB->get_records('course_message_mail_map', array('mailid' => $mailid));
        $this->assertEquals(count($res), 2);

        // Now work on mails the user owns.
        $this->setUser($this->friend);

        // Use same mail.
        $mailrecord = new mail_record($mailid, false);
        $mailrecord->delete_mail($courseid);

        // See if Friend's copy was deleted.
        $res = $DB->get_records('course_message_mail_map', array('mailid' => $mailid, 'userid' => $this->friend->id));
        $this->assertEquals(count($res), 0);
        // But ensure that Craig still has his copy.
        $res = $DB->get_records('course_message_mail_map', array('mailid' => $mailid, 'userid' => $this->craig->id));
        $this->assertEquals(count($res), 1);
        // Ensure that the mails table copy is still there.
        $res = $DB->get_records('course_message_mails', array('id' => $mailid));
        $this->assertEquals(count($res), 1);
        // And that the attachment is as well.
        $res = $DB->get_records('files', array('itemid' => $record->attachment));
        $this->assertEquals(count($res), 2);

        // Now delete Craig's copy -> this should chain to delete the mail entirely.
        $this->setUser($this->craig);

        $mailrecord = new mail_record($mailid, false);
        $mailrecord->delete_mail($courseid);

        // Check to see if map record was deleted.
        $res = $DB->get_records('course_message_mail_map', array('mailid' => $mailid, 'userid' => $this->craig->id));
        $this->assertEquals(count($res), 0);
        // Now the record in the mails table should be gone too -> no remaining references.
        $res = $DB->get_records('course_message_mails', array('id' => $mailid));
        $this->assertEquals(count($res), 0);
        // And that the attachment should be gone as well.
        $res = $DB->get_records('files', array('itemid' => $record->attachment));
        $this->assertEquals(count($res), 0);
    }

    /**********************************************************************
     * Helper functions are below:
     **********************************************************************/
    /**
     * This function is used to check a series of mail map records.
     *
     * @param int $mailid This is the mail ID (same for all)
     * @param int $numberofrecords This is the mail map record to test
     * @param array $userids The array of user ids (one for each record)
     * @param array $folder The array of folders (one for each record)
     *
     */
    private function check_multiple_mail_map($mailid, $numberofrecords, $userids, $folders) {
        global $DB;

        $records = $DB->get_records('course_message_mail_map', array('mailid' => $mailid));
        $this->assertEquals(count($records), $numberofrecords);
        reset($records);
        for ($i = 0; $i < count($records); $i++) {
            $this->check_mail_map(current($records), $mailid, $userids[$i], $folders[$i], 0);
            next($records);
        }
    }

    /**
     * This function is a helper for the testing the mail map.  It accepts
     * the record and the fields to test it against.
     *
     * @param object $record This is the mail map record to test
     * @param int $mailid This is the mail ID
     * @param string $folder This is the folder
     * @param int $timeread This is the time the mail was read
     *
     */
    private function check_mail_map($record, $mailid, $userid, $folder, $timeread) {
        $this->assertEquals($record->mailid, $mailid);
        $this->assertEquals($record->userid, $userid);
        $this->assertEquals($record->folder, $folder);
        $this->assertEquals($record->timeread, $timeread);
    }

    /**
     * This function is a helper for the group send.  The group consists of
     * {Wade, Martha, Craig}, but Craig is sending the mails.
     *
     * @param int $mailid This is the mail ID
     *
     */
    private function check_group_mail_map_count($mailid) {
        $this->check_inbox_mail_map_count($mailid, $this->martha->id, 1);
        $this->check_inbox_mail_map_count($mailid, $this->wade->id, 1);
        $this->check_inbox_mail_map_count($mailid, $this->craig->id, 0);

    }

    /**
     * This function is a helper for the all students test.  In the
     * test course, students -> {martha, wade}
     *
     * @param int $mailid This is the mail ID
     *
     */
    private function check_student_mail_map_count($mailid) {
        // User ID = 2 is the admin user.
        $this->check_inbox_mail_map_count($mailid, 2, 0);
        $this->check_inbox_mail_map_count($mailid, $this->craig->id, 0);
        $this->check_inbox_mail_map_count($mailid, $this->friend->id, 0);
        $this->check_inbox_mail_map_count($mailid, $this->martha->id, 1);
        $this->check_inbox_mail_map_count($mailid, $this->wade->id, 1);
    }

    /**
     * This function is a helper for the all instructors test.  In the
     * test course, instructors -> {craig, friend}
     *
     * @param int $mailid This is the mail ID
     *
     */
    private function check_instructor_mail_map_count($mailid) {
        // User ID = 2 is the admin user.
        $this->check_inbox_mail_map_count($mailid, 2, 0);
        $this->check_inbox_mail_map_count($mailid, $this->craig->id, 1);
        $this->check_inbox_mail_map_count($mailid, $this->friend->id, 1);
        $this->check_inbox_mail_map_count($mailid, $this->martha->id, 0);
        $this->check_inbox_mail_map_count($mailid, $this->wade->id, 0);
    }

    /**
     * This function checks the inbox mail map for a specified number
     * of records.  It is used to find out who a mail was sent to and to
     * ensure that they only got a specified number of copies.
     *
     * @param int $mailid This is the mail ID
     * @param int $userid Check the mail map for this person
     * @param int $desirednumber The number of DB entries there should be (typically 0 or 1)
     *
     */
    private function check_inbox_mail_map_count($mailid, $userid, $desirednumber) {
        global $DB;

        $query = "SELECT * FROM {course_message_mail_map} WHERE mailid = $mailid AND userid=$userid AND folder='inbox'";
        $res = $DB->get_records_sql($query);
        $this->assertEquals(count($res), $desirednumber);
    }

    /**
     * This method is used to both send a test mail and grab the output string
     * that would result.  That output string is compared against the expected
     * result.
     *
     * @param object $mailrecord This object contains the information to send the mail
     * @param int $courseid This is the ID for the course
     * @param bool $outcome Expected outcome of sending the mail
     * @return int The ID of the mailrecord object
     *
     */
    private function send_mail($mailrecord, $courseid, $outcome = "success") {
        ob_start();
        $mailrecord->send_mail($courseid);
        $outputbuffer = ob_get_contents();
        ob_end_clean();
        if ($outcome == "success") {
            $this->assertEquals(self::MAILSUCCESS, $outputbuffer);
        } else {
            // Currently sending "bad reply" to this function to indicate this case.
            $this->assertEquals(self::MAILBADREPLY, $outputbuffer);
        }
        return $mailrecord->mailid;
    }
}