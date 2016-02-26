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
require_once($CFG->dirroot . '/blocks/course_message/contact_list.class.php');
require_once($CFG->dirroot . '/blocks/course_message/locallib.php');
require_once($CFG->dirroot . '/blocks/course_message/tests/mailunittest.php');

/**
 * This is the unittest class for contact_list.class.php.
 *
 *	The following functions are checked:
 * 1) constructor
 * 2) display_contacts()
 *
 * @package    block_course_message
 * @group      block_course_message_tests
 * @copyright  2012 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class test_contactlist extends mail_unit_test {

    /**
     * This method tests the constructor.  It's very simple, but I included
     * it anyway for completeness.
     *
     */
    public function test_constructor() {

        $contactlist = new contact_list($this->testcourseid, $this->friend->id);

        $this->assertEquals($contactlist->courseid, $this->testcourseid);
        $this->assertEquals($contactlist->userid, $this->friend->id);
    }

    /**
     * This method checks the contact list generation for the block.  If the proper tags
     * are getting place in the divs, then it is pulling the right values from the DB.
     *
     */
    public function test_block_contacts() {

        $outputbuffer = '';

        $contactlist = new contact_list($this->testcourseid, $this->friend->id);
        $contactlist->display_contacts($outputbuffer);

        $matcher = array('tag' => 'div', 'attributes' => array('class' => 'contactList'));
        $this->assertTag($matcher, $outputbuffer);

        $this->check_for_contact_div($this->craig->id, 'Craig', 'Jamieson', $outputbuffer);
        $this->check_for_contact_div($this->martha->id, 'Martha', 'Stein', $outputbuffer);
        $this->check_for_contact_div($this->wade->id, 'Wade', 'Kelly', $outputbuffer);
        $this->check_for_contact_div("g{$this->testgroupid}", $this->testgroupname, '', $outputbuffer);
        $this->check_for_contact_div('s1', get_string('allstudents', BLOCK_CM_LANG_TABLE), '', $outputbuffer);
    }

    /**
     * This method checks for the appropriate contact (as a div) in the output
     * buffer.
     *
     * @param string $id The ID to check for (user or group) passed as a string
     * @param string $first The first name to look for
     * @param string $last The last name to look for
     * @param string $buffer The buffer to check for the tags in
     *
     */
    private function check_for_contact_div($id, $first, $last, &$buffer) {
        $matcher = array('tag' => 'div', 'attributes' => array('id' => $id));
        $this->assertTag($matcher, $buffer);
        $matcher = array('tag' => 'div', 'attributes' => array('first' => $first));
        $this->assertTag($matcher, $buffer);
        $matcher = array('tag' => 'div', 'attributes' => array('last' => $last));
        $this->assertTag($matcher, $buffer);
        $matcher = array('tag' => 'div', 'content' => $first.' '.$last);
        $this->assertTag($matcher, $buffer);
    }

}