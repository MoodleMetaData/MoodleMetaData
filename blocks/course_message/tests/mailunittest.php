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
require_once($CFG->dirroot.'/blocks/course_message/locallib.php');
require_once($CFG->dirroot.'/blocks/course_message/mail_record.class.php');
require_once($CFG->dirroot.'/lib/moodlelib.php');

/**
 * This is the master table list for block_course_message testing.
 *
 * I created a derived class here (from advanced_testcase) which all my test classes are
 * then derived from, so they all use the same setUp() routine.  This does slow down the
 * tests a bit but tests should not be that time sensitive anyway.
 *
 * @package    block_course_message
 * @copyright  2012 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mail_unit_test extends advanced_testcase {

    /** This is the course ID of the test course that is setup */
    protected $testcourseid;
    /** This is the group ID of the test group that is setup */
    protected $testgroupid;
    /** This is the name of the test group */
    protected $testgroupname;
    /** This is the list of IDs for the test mails that are generated */
    protected $mailids = array();
    /** This is the "craig" user */
    protected $craig;
    /** This is the "friend" user */
    protected $friend;
    /** This is the "martha" user */
    protected $martha;
    /** This is the "wade" user */
    protected $wade;
    /** This is the file ID for the single test file that is used */
    const FILEID = 123456789;
    /** mailids offset for generic mail */
    const GENERICMAILID = 0;
    /** mailids offset for parent of thread */
    const DELETEMAILID = 1;
    /** mailids offset for parent of thread */
    const PARENTMAILID = 2;
    /** mailids offset for first child in thread */
    const CHILDMAILID = 3;
    /** mailids offset for last mail in thread */
    const LASTMAILID = 4;

    /**
     * The setup function is used to place the DB in its initial state.
     *
     */
    protected function setUp() {
        global $DB, $CFG;

        // This needs to be here for the dummy test below.
        $this->resetAfterTest(true);
        // Create all of the test data.
        $this->create_course();
        $this->create_group();
        $this->create_test_mail();
        $this->create_files();
        $this->setup_user_preferences();

        parent::setUp();
    }

    /**
     * Dummy test to avoid a warning.
     *
     */
    public function test_dummy() {
        $somevariable = 1;
    }

    /**********************************************************************
     * Helper functions are below:
     **********************************************************************/
    /**
     * This function retrieves a mail from the database using the message
     * text.  It then compares the mail to the other data passed along to
     * ensure that it is correct.
     *
     * @param object $message Mail contents used to find the record
     * (the rest of the params are the ones to test against)
     * @param int $courseid This is the course ID
     * @param int $useridfrom This is the user ID from field
     * @param string $recipients This is the recipients string
     * @param string $subject This is the subject
     * @param int $parentmessage This is the parent message
     * @param int $attachment This is the attachment
     * @param string $carboncopy This is the carboncopy string
     * @return int The ID of the mail that was found
     *
     */
    protected function check_mails_db_by_message($message, $courseid, $useridfrom, $recipients, $subject, $parentmessage = 0,
                                                 $attachment = 0, $carboncopy = null) {
        global $DB;

        $params = array($message);
        $query = "SELECT * FROM {course_message_mails} WHERE message = ?";
        $record = $DB->get_record_sql($query, $params);
        $this->check_mails_db($record, $courseid, $useridfrom, $recipients, $subject, $message, $parentmessage,
                              $attachment, $carboncopy);
        return $record->id;
    }

    /**
     * This function is a helper for the testing the mails DB.  It accepts
     * the record and the fields to test it against.
     *
     * @param object $record This is the mail record to test
     * (the rest of the params are the ones to test against)
     * @param int $courseid This is the course ID
     * @param int $useridfrom This is the user ID from field
     * @param string $recipients This is the recipients string
     * @param string $subject This is the subject
     * @param string $message This is the message
     * @param int $parentmessage This is the parent message
     * @param int $attachment This is the attachment
     * @param string $carboncopy This is the carboncopy string
     *
     */
    protected function check_mails_db($record, $courseid, $useridfrom, $recipients, $subject, $message,
                                      $parentmessage = 0, $attachment = 0, $carboncopy = null) {
        $this->assertEquals($record->courseid, $courseid);
        $this->assertEquals($record->useridfrom, $useridfrom);
        $this->assertEquals($record->recipients, $recipients);
        $this->assertEquals($record->subject, $subject);
        $this->assertEquals($record->message, $message);
        $this->assertEquals($record->parentmessage, $parentmessage);
        $this->assertEquals($record->attachment, $attachment);
        $this->assertEquals($record->carboncopy, $carboncopy);
    }

    /**
     * This function finds a mail in the database by its message field and returns its ID.
     *
     * @param string $message The contents of the message field (to locate the mail).
     * @return int The ID of the mail
     *
     */
    protected function get_mail_id($message) {
        global $DB;

        $params = array($message);
        $query = "SELECT * FROM {course_message_mails} WHERE message = ?";
        $record = $DB->get_record_sql($query, $params);

        return $record->id;
    }

    /**
     * This function creates the course that is used for testing.  The course is created,
     * along with my four test users.  These test users are all enrolled in the course.
     *
     */
    protected function create_course() {

        $course = $this->getDataGenerator()->create_course();
        $this->testcourseid = $course->id;

        $this->craig = $this->getDataGenerator()->create_user(array('email' => 'craig.jamieson@ualberta.ca', 'username' => 'craig',
                                                                    'firstname' => 'Craig', 'lastname' => 'Jamieson'));
        $this->friend = $this->getDataGenerator()->create_user(array('email' => 'cjamieson@ualberta.ca', 'username' => 'friend',
                                                                     'firstname' => "Craig's", 'lastname' => 'Friend'));
        $this->martha = $this->getDataGenerator()->create_user(array('email' => 'martha.stein@ualberta.ca', 'username' => 'martha',
                                                                     'firstname' => 'Martha', 'lastname' => 'Stein'));
        $this->wade = $this->getDataGenerator()->create_user(array('email' => 'wade.kelly@ualberta.ca', 'username' => 'wade',
                                                                   'firstname' => 'Wade', 'lastname' => 'Kelly'));

        $this->enroll_users();
    }

    /**
     * This function enrolls the test users.  The first two users are enrolled as
     * teachers, while the other two are enrolled as students.
     *
     */
    protected function enroll_users() {
        global $DB;

        // Get role IDs by shortname.
        $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $this->assertNotEmpty($studentrole);
        $teacherrole = $DB->get_record('role', array('shortname' => 'teacher'));
        $this->assertNotEmpty($teacherrole);
        // Enroll users.
        $this->getDataGenerator()->enrol_user($this->craig->id, $this->testcourseid, $teacherrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($this->friend->id, $this->testcourseid, $teacherrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($this->martha->id, $this->testcourseid, $studentrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($this->wade->id, $this->testcourseid, $studentrole->id, 'manual');
    }

    /**
     * This function creates group to be used while testing.  Three of the four users
     * {Craig|Martha|Wade} are all members of this group.
     *
     */
    protected function create_group() {

        $group = $this->getDataGenerator()->create_group(array('courseid' => $this->testcourseid));
        $this->testgroupid = $group->id;
        $this->testgroupname = $group->name;
        groups_add_member($group->id, $this->craig->id);
        groups_add_member($group->id, $this->martha->id);
        groups_add_member($group->id, $this->wade->id);
    }

    /**
     * This function creates the mail to be used for testing.  Only 5 mail messages
     * are created -> {one generic mail, one mail with an attachment, and three
     * mail as part of a thread}.
     *
     */
    protected function create_test_mail() {
        global $DB;

        ob_start();

        $this->setUser($this->craig);

        // Send first test mail -> generic.
        $params = array(
                "subject" => 'Test Mail 1',
                "message" => 'Generic message goes here.',
                "parentmessage" => 0,
                "replytype" => 'single',
                "draftid" => 0);

        $mailto = array("{$this->friend->id}");
        $mailrecord = new mail_record(0, false);
        $mailrecord->set_send_params($params, $mailto);
        $mailrecord->send_mail($this->testcourseid);
        $this->mailids[] = $this->get_mail_id($params['message']);

        // Second test mail: used for deletion test.
        $params = array(
                "subject" => 'Delete Mail 1',
                "message" => 'This mail will be deleted.',
                "parentmessage" => 0,
                "replytype" => 'single',
                "draftid" => 0);

        $mailrecord = new mail_record(0, false);
        $mailrecord->set_send_params($params, $mailto);
        $mailrecord->send_mail($this->testcourseid);
        $this->mailids[] = $this->get_mail_id($params['message']);
        // Here I'm spoofing adding an attachment afterwards: I think this could be condensed to 1 step.
        $query = "SELECT * FROM {course_message_mails} WHERE message = '{$params['message']}'";
        $record = $DB->get_record_sql($query);
        $record->attachment = self::FILEID;
        $DB->update_record('course_message_mails', $record);

        // Now create a thread of three.
        $params = array(
                "subject" => 'Threaded Message Test',
                "message" => 'This is the parent for the thread.',
                "parentmessage" => 0,
                "replytype" => 'single',
                "draftid" => 0);

        $mailrecord = new mail_record(0, false);
        $mailrecord->set_send_params($params, $mailto);
        $mailrecord->send_mail($this->testcourseid);
        $this->mailids[] = $this->get_mail_id($params['message']);

        // Switch to friend and reply.
        $this->setUser($this->friend);

        $params = array(
                "subject" => 'Threaded Message Test',
                "message" => 'This is the first reply.',
                "parentmessage" => $this->mailids[2],
                "replytype" => 'single',
                "draftid" => 0);

        $mailto = array("1");
        $mailrecord = new mail_record(0, false);
        $mailrecord->set_send_params($params, $mailto);
        $mailrecord->send_mail($this->testcourseid);
        $this->mailids[] = $this->get_mail_id($params['message']);

        // Now reply back.
        $this->setUser($this->craig);

        $params = array(
                "subject" => 'Threaded Message Test',
                "message" => 'This is the second reply.',
                "parentmessage" => $this->mailids[3],
                "replytype" => 'single',
                "draftid" => 0);

        $mailto = array("1");
        $mailrecord = new mail_record(0, false);
        $mailrecord->set_send_params($params, $mailto);
        $mailrecord->send_mail($this->testcourseid);
        $this->mailids[] = $this->get_mail_id($params['message']);

        ob_end_clean();

    }

    /**
     * This function creates the one file that is used for testing.
     *
     */
    protected function create_files() {

        $fs = get_file_storage();
        $fileinfo = array(
                'contextid' => context_course::instance($this->testcourseid)->id,
                'component' => BLOCK_CM_COMPONENT_NAME,
                'filearea' => BLOCK_CM_FILE_AREA_NAME,
                'itemid' => self::FILEID,
                'filepath' => '/',
                'filename' => 'testfile.txt');

        $fs->create_file_from_string($fileinfo, 'test file for deletion');
    }

    /**
     * This function sets the user preferences (emailing & display) for the four
     * test users.
     *
     */
    protected function setup_user_preferences() {

        $this->setUser($this->craig);
        block_course_message_update_mail_preference('inbox', 'true');
        block_course_message_update_mail_preference('sent', 'true');
        block_course_message_update_display_preference('new_page');
        $this->setUser($this->friend);
        block_course_message_update_mail_preference('inbox', 'true');
        block_course_message_update_mail_preference('sent', 'false');
        block_course_message_update_display_preference('iframe');
        set_user_preferences(array('htmleditor' => 'tinymce'));
        $this->setUser($this->martha);
        block_course_message_update_mail_preference('inbox', 'false');
        block_course_message_update_mail_preference('sent', 'true');
        set_user_preferences(array('htmleditor' => 'atto'));
        $this->setUser($this->wade);
        block_course_message_update_mail_preference('inbox', 'false');
        block_course_message_update_mail_preference('sent', 'false');
    }
}