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
require_once($CFG->dirroot . '/blocks/course_message/locallib.php');
require_once($CFG->dirroot . '/blocks/course_message/tests/mailunittest.php');

/**
 * This is the unittest class for locallib.php.
 *
 *	The following functions are checked:
 * 1) block_course_message_has_unread_mail()
 * 2) block_course_message_get_mail_preference()
 * 3) block_course_message_update_mail_preference()
 * 4) block_course_message_get_display_preference()
 * 5) block_course_message_update_display_preference()
 * 6) block_course_message_map_ids_to_names()
 *
 * @package    block_course_message
 * @group      block_course_message_tests
 * @copyright  2012 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class test_locallib extends mail_unit_test{

    /**
     * This function tests the has_unread_mail() function.  There is not a lot
     * of mail in my test DB, so the numbers are small, but it should still be
     * sufficient to test.
     *
     */
    public function test_has_unread_mail() {

        // Friend has 4 unread mail -> {generic, delete, parent, last}.
        $this->setUser($this->friend);
        $unreadmails = block_course_message_has_unread_mail($this->testcourseid);
        $this->assertEquals($unreadmails, 4);

        // Admin user has no mail.
        $this->setAdminUser();
        $unreadmails = block_course_message_has_unread_mail($this->testcourseid);
        $this->assertEquals($unreadmails, 0);

        // Craig has 1 unread mail -> {child}.
        $this->setUser($this->craig);
        $unreadmails = block_course_message_has_unread_mail($this->testcourseid);
        $this->assertEquals($unreadmails, 1);
    }

    /**
     * This function tests the block_course_message_get_mail_preference() function.
     * It checks the mail preference of a few users, then finally tries a user (admin)
     * that does not have the value set.
     *
     */
    public function test_get_mail_preference() {
        $this->setUser($this->craig);
        $this->assertTrue((bool)block_course_message_get_mail_preference('inbox', $this->craig->id));
        $this->assertTrue((bool)block_course_message_get_mail_preference('sent', $this->craig->id));

        $this->setUser($this->friend);
        $this->assertTrue((bool)block_course_message_get_mail_preference('inbox', $this->friend->id));
        $this->assertFalse((bool)block_course_message_get_mail_preference('sent', $this->friend->id));

        $this->setUser($this->martha);
        $this->assertFalse((bool)block_course_message_get_mail_preference('inbox', $this->martha->id));
        $this->assertTrue((bool)block_course_message_get_mail_preference('sent', $this->martha->id));

        $this->setUser($this->wade);
        $this->assertFalse((bool)block_course_message_get_mail_preference('inbox', $this->wade->id));
        $this->assertFalse((bool)block_course_message_get_mail_preference('sent', $this->wade->id));

        // Admin should come back as false (not in the table).
        $this->setAdminUser();
        $this->assertFalse((bool)block_course_message_get_mail_preference('inbox', 2));
        $this->assertFalse((bool)block_course_message_get_mail_preference('sent', 2));
    }

    /**
     * This function tests the block_course_message_get_editor_preference() function.
     * The Craig user has no preference (defaults to 'atto'), the friend user prefers
     * 'tinymce' and the Martha user prefers 'atto'.
     *
     */
    public function test_get_editor_preference() {

        $this->setUser($this->craig);
        $this->assertEquals(block_course_message_get_editor_preference($this->craig->id), 'atto');
        $this->setUser($this->friend);
        $this->assertEquals(block_course_message_get_editor_preference($this->friend->id), 'tinymce');
        $this->setUser($this->martha);
        $this->assertEquals(block_course_message_get_editor_preference($this->martha->id), 'atto');
    }

    /**
     * This function tests the block_course_message_get_display_preference() function.
     * It checks the mail preference of a two users, then tries a user with no value
     * set to ensure it defaults to an iframe.
     *
     * Note: the iframe version was removed, so now the function always returns ON_PAGE
     *
     */
    public function test_get_display_preference() {
        $this->assertEquals(block_course_message_get_display_preference($this->craig->id), BLOCK_CM_ON_PAGE);
        $this->assertEquals(block_course_message_get_display_preference($this->friend->id), BLOCK_CM_ON_PAGE);
        // Admin user has no preference, now always returns BLOCK_CM_ON_PAGE.
        $this->assertEquals(block_course_message_get_display_preference(2), BLOCK_CM_ON_PAGE);
    }

    /**
     * This function tests the block_course_message_update_mail_preference() function.  It is quite simple
     * since the function itself is fairly straightforward.
     *
     */
    public function test_update_mail_preference() {
        $this->setUser($this->craig);

        // Make sure it is true (inbox setting).
        $this->assertTrue((bool)block_course_message_get_mail_preference('inbox', $this->craig->id));
        // Toggle to false.
        block_course_message_update_mail_preference('inbox', 'false');
        $this->assertFalse((bool)block_course_message_get_mail_preference('inbox', $this->craig->id));
        // Toggle back to true.
        block_course_message_update_mail_preference('inbox', 'true');
        $this->assertTrue((bool)block_course_message_get_mail_preference('inbox', $this->craig->id));

        // Make sure it is true (sent setting).
        $this->assertTrue((bool)block_course_message_get_mail_preference('sent', $this->craig->id));
        // Toggle to false.
        block_course_message_update_mail_preference('sent', 'false');
        $this->assertFalse((bool)block_course_message_get_mail_preference('sent', $this->craig->id));
        // Toggle back to true.
        block_course_message_update_mail_preference('sent', 'true');
        $this->assertTrue((bool)block_course_message_get_mail_preference('sent', $this->craig->id));
    }

    /**
     * This function tests the block_course_message_update_display_preference() function.
     * The ability to load in an iframe has been removed: no longer tested.
     *
     * To be clear: the function to change the preference will still do so, however, the
     * function that reads the preference will always return BLOCK_CM_ON_PAGE.
     *
     */
    public function test_update_display_preference() {
        $this->setUser($this->craig);

        // Toggle to on page.
        block_course_message_update_display_preference(BLOCK_CM_ON_PAGE);
        $this->assertEquals(block_course_message_get_display_preference($this->craig->id), BLOCK_CM_ON_PAGE);
        // Toggle back to iframe -- no longer possible (no assert to test).
        block_course_message_update_display_preference(BLOCK_CM_IN_IFRAME);
    }

    /**
     * This function tests the block_course_message_map_ids_to_names() function.  This
     * is a fairly important function, since it has to handle decoding both user ids and
     * group ids.
     *
     */
    public function test_map_ids_to_names() {

        // Test 1: viewing mail in inbox (single user).
        $ids = $this->friend->id;
        $folder = 'inbox';
        $names = block_course_message_map_ids_to_names($ids, $folder);
        $this->assertEquals($names, "Craig's Friend");

        // Test 2: viewing mail in inbox (array or multiple users) -> straight return.
        $ids = "[\"{$this->friend->id}\"]"; // JSON decodes as array.
        $names = block_course_message_map_ids_to_names($ids, $folder);
        $this->assertEquals($names, "");
        $ids = "[\"{$this->friend->id}\", \"{$this->craig->id}\"]"; // Multiple recipients.
        $names = block_course_message_map_ids_to_names($ids, $folder);
        $this->assertEquals($names, "");

        // Test 3: viewing sent message (to: single user).
        $ids = "[\"{$this->friend->id}\"]"; // This works for 'sent'.
        $folder = 'sent';
        $names = block_course_message_map_ids_to_names($ids, $folder);
        $this->assertEquals($names, "Craig's Friend");

        // Test 4: viewing sent message (to: multiple users).
        $ids = "[\"{$this->friend->id}\", \"{$this->craig->id}\", \"{$this->martha->id}\", \"2\"]";
        $names = block_course_message_map_ids_to_names($ids, $folder);
        $this->assertEquals($names, "Craig's Friend, Craig Jamieson, Martha Stein, Admin User");

        // Test 5: viewing sent message (to: multiple users + group).
        $ids = "[\"{$this->friend->id}\", \"{$this->craig->id}\", \"g{$this->testgroupid}\", \"{$this->martha->id}\", \"2\"]";
        $names = block_course_message_map_ids_to_names($ids, $folder);
        $this->assertEquals($names, "Craig's Friend, Craig Jamieson, {$this->testgroupname}, Martha Stein, Admin User");

        // Test out all instructors.
        $ids = '["i1"]';
        $names = block_course_message_map_ids_to_names($ids, $folder);
        $this->assertEquals($names, get_string('allinstructors', BLOCK_CM_LANG_TABLE));

        // Test out all students.
        $ids = '["s1"]';
        $names = block_course_message_map_ids_to_names($ids, $folder);
        $this->assertEquals($names, get_string('allstudents', BLOCK_CM_LANG_TABLE));

        // Test all instructors + a student.
        $ids = "[\"{$this->friend->id}\", \"i1\"]";
        $names = block_course_message_map_ids_to_names($ids, $folder);
        $this->assertEquals($names, "Craig's Friend, ".get_string('allinstructors', BLOCK_CM_LANG_TABLE));
    }
}