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
global $DB, $CFG;
require_once("{$CFG->dirroot}/config.php");
require_once("{$CFG->dirroot}/blocks/spedcompletion/block_spedcompletion.php");
require_once("{$CFG->dirroot}/blocks/spedcompletion/classes/task/sync_sped_completion.php");
require_once("{$CFG->dirroot}/blocks/spedcompletion/lib/sped_service.php");

/**
 * Tests for the SPED completion block.
 *
 * This tests to see if the database is updated when cron is run
 * so that the SPED completion block is displaying the right status
 *
 * @package    block_spedcompletion
 * @copyright  2014 Anthony Radziszewski radzisze@ualberta.ca
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class spedcompletion_test extends advanced_testcase
{
    public function setUp() {
        global $DB;
        // For convienience provide references to the objects in the tests.
        $this->referenceobjects = array();
        $this->referenceobjects['user1'] = $this->getDataGenerator()->create_user(array('idnumber' => 'user1'));
        $this->referenceobjects['user2'] = $this->getDataGenerator()->create_user(array('idnumber' => 'user2'));
        $this->referenceobjects['course1'] = $this->getDataGenerator()->create_course();
        $this->referenceobjects['course2'] = $this->getDataGenerator()->create_course();
        $this->getDataGenerator()->enrol_user($this->referenceobjects['user1']->id, $this->referenceobjects['course1']->id);
        $this->getDataGenerator()->enrol_user($this->referenceobjects['user2']->id, $this->referenceobjects['course1']->id);
        $this->getDataGenerator()->enrol_user($this->referenceobjects['user1']->id, $this->referenceobjects['course2']->id);
        $this->getDataGenerator()->enrol_user($this->referenceobjects['user2']->id, $this->referenceobjects['course2']->id);

        $ci1 = context_course::instance($this->referenceobjects['course1']->id);
        $ci2 = context_course::instance($this->referenceobjects['course2']->id);

        // Create block in course 1.
        $b1 = (object)array('parentcontextid' => $ci1->id);
        $v1 = array('sped_version' => '0');
        $this->referenceobjects['block1'] = $this->getDataGenerator()->create_block('spedcompletion', $b1, $v1);

        // Create block in course 2.
        $b2 = (object)array('parentcontextid' => $ci2->id);
        $v2 = array('sped_version' => '1');
        $this->referenceobjects['block2'] = $this->getDataGenerator()->create_block('spedcompletion', $b2, $v2);

        // User1 will have completed course1.
        $record = new stdClass();
        $record->userid = $this->referenceobjects['user1']->id;
        $record->course = $this->referenceobjects['course1']->id;
        $record->timecompleted = time();
        $DB->insert_record('course_completions', $record);

        // User2 will have completed course1.
        $record = new stdClass();
        $record->userid = $this->referenceobjects['user2']->id;
        $record->course = $this->referenceobjects['course1']->id;
        $record->timecompleted = time();
        $DB->insert_record('course_completions', $record);

        // User2 will have completed course2.
        $record = new stdClass();
        $record->userid = $this->referenceobjects['user2']->id;
        $record->course = $this->referenceobjects['course2']->id;
        $record->timecompleted = time();
        $DB->insert_record('course_completions', $record);
    }

    public function tearDown() {
        $this->referenceobjects = array();
    }

    public function test_spedcompletion_successful_post() {
        global $DB, $CFG;
        $this->resetAfterTest(true);

        $spedservicestub = $this->getMockBuilder('\block_spedcompletion\Sped')->setMethods(array('post_update',
            'get_message'))->setConstructorArgs(
            array('0', 'key', 'url'))->getMock();
        $spedservicestub->expects($this->exactly(3))->method('post_update')->with($this->stringStartsWith('user'))->will(
            $this->returnValue(true));
        $spedservicestub->expects($this->never())->method('get_message')->will($this->returnValue(''));

        $spedblockmock = $this->getMockBuilder('\block_spedcompletion\task\sync_sped_completion')->setMethods(
            array('newspedservice'))->getMock();
        $spedblockmock->expects($this->once())->method('newspedservice')->with(
            $this->isType('string'), $this->isType('string'), $this->isType('string'))->will($this->returnValue($spedservicestub));

        // Output buffering is disabled in phpunit runs.
        ini_set('output_buffering', 'On');
        ob_start();
        $this->assertEquals(true, $spedblockmock->execute($spedservicestub));
        ob_end_clean();
        ini_set('output_buffering', 'Off');
        // User1 should be marked updated for sped for course 1.
        $this->assertTrue($DB->record_exists('spedcompletion', array('userid' => $this->referenceobjects['user1']->id,
            'course' => $this->referenceobjects['course1']->id)));
        $this->assertTrue($DB->record_exists('spedcompletion', array('userid' => $this->referenceobjects['user2']->id,
            'course' => $this->referenceobjects['course1']->id)));
        $this->assertTrue($DB->record_exists('spedcompletion', array('userid' => $this->referenceobjects['user2']->id,
            'course' => $this->referenceobjects['course2']->id)));
        $this->assertFalse($DB->record_exists('spedcompletion', array('userid' => $this->referenceobjects['user1']->id,
            'course' => $this->referenceobjects['course2']->id)));
    }

    public function test_spedcompletion_failed_post() {
        global $DB, $CFG;
        $this->resetAfterTest(true);

        $spedservicestub = $this->getMockBuilder('\block_spedcompletion\Sped')->setMethods(array('post_update',
            'get_message'))->setConstructorArgs(
            array('0', 'url', 'key'))->getMock();
        $spedservicestub->expects($this->exactly(3))->method('post_update')->with($this->stringStartsWith('user'))->will(
            $this->returnValue(false));
        $spedservicestub->expects($this->exactly(3))->method('get_message')->will(
            $this->returnValue('Failed to post for some reason.'));

        $spedblockmock = $this->getMockBuilder('\block_spedcompletion\task\sync_sped_completion')->setMethods(
            array('newspedservice'))->getMock();
        $spedblockmock->expects($this->once())->method('newspedservice')->with(
            $this->isType('string'), $this->isType('string'), $this->isType('string'))->will($this->returnValue($spedservicestub));

        // Output buffering is disabled in phpunit runs.
        ini_set('output_buffering', 'On');
        ob_start();
        $res = $spedblockmock->execute($spedservicestub);
        ob_end_clean();
        ini_set('output_buffering', 'Off');

        $this->assertEquals(true, $res);

        // Neither user should be in the table.
        $this->assertFalse($DB->record_exists('spedcompletion', array('userid' => $this->referenceobjects['user1']->id)));
        $this->assertFalse($DB->record_exists('spedcompletion', array('userid' => $this->referenceobjects['user2']->id)));
    }
}