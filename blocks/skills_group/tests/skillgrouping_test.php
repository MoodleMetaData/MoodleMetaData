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
require_once($CFG->dirroot.'/blocks/skills_group/classes/skills_group_setting.class.php');
require_once($CFG->dirroot.'/blocks/skills_group/classes/skills_grouping.class.php');
require_once($CFG->dirroot.'/blocks/skills_group/tests/skillsgroupunittest.php');

/**
 * This is the unittest class for skills_grouping.class.php.
 *
 * create_group()
 * get_potential_students()
 * check_for_user_in_grouping()
 *
 * @package    block_skills_group
 * @group      block_skills_group_tests
 * @copyright  2014 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class test_skillsgrouping extends skills_group_unit_test {

    /**
     * Some additional setup is performed for this class only.
     */
    protected function setUp() {

        parent::setUp();

        // Add extra test groups.
        $this->getDataGenerator()->create_group(array('courseid' => $this->courseid, 'name' => 'Team 01'));
        $this->getDataGenerator()->create_group(array('courseid' => $this->courseid, 'name' => 'Team 02'));
        $this->getDataGenerator()->create_group(array('courseid' => $this->courseid, 'name' => 'Team 05'));
    }

    /**
     * This function tests to see if a user-named group is created correctly.
     */
    public function test_create_group() {
        global $DB;

        $this->configure_settings();
        $sgrouping = new skills_grouping($this->courseid);

        // Test 1: group creation with name specified.
        $this->setUser($this->users[0]);
        $testname = 'Test Skills Group';
        $groupid = $sgrouping->create_group($testname);
        $record = $DB->get_record('groups', array('id' => $groupid));
        $this->assertEquals($record->name, $testname);
        $memberrecord = $DB->get_record('groups_members', array('groupid' => $groupid));
        $this->assertEquals($memberrecord->userid, $this->users[0]->id);
        groups_remove_member($groupid, $this->users[0]->id);

        // Test 2: auto-created group name.
        $groupid = $sgrouping->create_group(null);
        $record = $DB->get_record('groups', array('id' => $groupid));
        $this->assertEquals($record->name, 'Team 03');
        groups_remove_member($groupid, $this->users[0]->id);
        $groupid = $sgrouping->create_group(null);
        $record = $DB->get_record('groups', array('id' => $groupid));
        $this->assertEquals($record->name, 'Team 04');
        $groupid = $sgrouping->create_group(null);
        $record = $DB->get_record('groups', array('id' => $groupid));
        $this->assertEquals($record->name, 'Team 06');
    }


    /**
     * This function checks to see whether the list of ungrouped students is returned
     * correctly.  I don't sort the list of user IDs upon return, since it is assumed
     * that they are sorted numerically.
     */
    public function test_get_potential_students() {

        $this->configure_settings();
        $sgrouping = new skills_grouping($this->courseid);

        $students = $sgrouping->get_potential_students();
        $i = 0;
        $users = $this->get_ungrouped_studentids();
        foreach ($students as $id => $name) {
            $this->assertEquals($users[$i]->firstname.' '.$users[$i]->lastname, $name);
            $this->assertEquals($users[$i++]->id, $id);
        }
    }

    /**
     * This function checks to see whether the list of ungrouped students is returned
     * correctly.  I don't sort the list of user IDs upon return, since it is assumed
     * that they are sorted numerically.
     */
    public function test_check_for_user_in_grouping() {

        $this->configure_settings();
        $sgrouping = new skills_grouping($this->courseid);

        // Test user not in group.
        $this->assertFalse($sgrouping->check_for_user_in_grouping($this->users[0]->id));
        // Test user in group.
        $this->assertEquals($sgrouping->check_for_user_in_grouping($this->users[self::NUMBEROFUSERS - 1]->id), $this->groupids[0]);
    }

}