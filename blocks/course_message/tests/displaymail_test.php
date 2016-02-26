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
require_once($CFG->dirroot.'/blocks/course_message/display_mail.class.php');
require_once($CFG->dirroot.'/blocks/course_message/tests/mailunittest.php');

/**
 * This is the unittest class for display_mail.class.php.
 *
 * The following functions are checked:
 * 1) is_thread()
 * 2) check_user_identification()
 * 3) constructor (for both threaded and non-threaded)
 *
 * @package    block_course_message
 * @group      block_course_message_tests
 * @copyright  2012 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class test_displaymail extends mail_unit_test {
    /**
     * This function tests that parent messages are being found properly.  Most
     * of the rest of the tests here will fail if this test does.
     *
     */
    public function test_is_thread() {
        $this->setUser($this->friend);

        // Last mail in thread is a child.
        $mailid = $this->mailids[self::LASTMAILID];
        $folder = 'inbox';

        $dm = new display_mail($mailid, $folder, $this->testcourseid, false);
        $this->assertTrue((bool)$dm->is_thread());

        // Parent mail is part of a thread.
        $mailid = $this->mailids[self::PARENTMAILID];;
        $folder = 'inbox';

        $dm = new display_mail($mailid, $folder, $this->testcourseid, false);
        $this->assertTrue((bool)$dm->is_thread());

        // Generic mail is not part of a thread.
        $mailid = $this->mailids[self::GENERICMAILID];;
        $folder = 'inbox';

        $dm = new display_mail($mailid, $folder, $this->testcourseid, false);
        $this->assertFalse((bool)$dm->is_thread());
    }

    /**
     * This function checks that users who have no copy of the mail they are
     * trying to view are not allowed to view it.
     *
     */
    public function test_check_user_identification() {
        // Check user that has no copy of mail (inbox|sent).
        $this->setUser($this->martha);

        $mailid = $this->mailids[self::LASTMAILID];
        $folder = 'inbox';

        $dm = new display_mail($mailid, $folder, $this->testcourseid, false);
        $this->assertFalse((bool)$dm->check_user_identification($folder));

        $folder = 'sent';
        $dm = new display_mail($mailid, $folder, $this->testcourseid, false);
        $this->assertFalse((bool)$dm->check_user_identification($folder));

        $this->setUser($this->friend);
        // User has copy in inbox, but not in sent.
        $mailid = $this->mailids[self::LASTMAILID];

        $folder = 'inbox';
        $dm = new display_mail($mailid, $folder, $this->testcourseid, false);
        $this->assertTrue((bool)$dm->check_user_identification($folder));

        $folder = 'sent';
        $dm = new display_mail($mailid, $folder, $this->testcourseid, false);
        $this->assertFalse((bool)$dm->check_user_identification($folder));

        // User has copy in sent, but not in inbox.
        $mailid = $this->mailids[self::CHILDMAILID];

        $folder = 'sent';
        $dm = new display_mail($mailid, $folder, $this->testcourseid, false);
        $this->assertTrue((bool)$dm->check_user_identification($folder));

        $folder = 'inbox';
        $dm = new display_mail($mailid, $folder, $this->testcourseid, false);
        $this->assertFalse((bool)$dm->check_user_identification($folder));

    }

    /**
     * This function tests retrieving a non-threaded mail.
     *
     */
    public function test_non_threaded_message() {
        $this->setUser($this->craig);
        $mailid = $this->mailids[self::GENERICMAILID];

        $folder = 'inbox';
        $dm = new display_mail($mailid, $folder, $this->testcourseid, false);
        $this->assertFalse((bool)$dm->is_thread());
    }

    /**
     * This function tests retrieving a threaded mail.  I test the end
     * point of the thread, the middle of the thread and the beginning
     * of the thread.
     *
     */
    public function test_threaded_message() {
        $this->setUser($this->friend);

        // 1) Last mail in thread has a parent + three message thread.
        $mailid = $this->mailids[self::LASTMAILID];
        $folder = 'inbox';

        $dm = new display_mail($mailid, $folder, $this->testcourseid, false);
        $this->assertTrue((bool)$dm->is_thread());
        $this->assertEquals(count($dm->threadmails), 3);

        // 2) Child (middle) mail in thread has a parent + three message thread.
        $mailid = $this->mailids[self::CHILDMAILID];
        $folder = 'sent';

        $dm = new display_mail($mailid, $folder, $this->testcourseid, false);
        $this->assertTrue((bool)$dm->is_thread());
        $this->assertEquals(count($dm->threadmails), 3);

        // 3) Parent mail: is parent, part of thread.
        $mailid = $this->mailids[self::PARENTMAILID];
        $folder = 'inbox';

        $dm = new display_mail($mailid, $folder, $this->testcourseid, false);
        $this->assertTrue((bool)$dm->is_thread());
        $this->assertEquals(count($dm->threadmails), 3);
    }
}