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
require_once($CFG->dirroot.'/blocks/skills_group/classes/skills_group_student.class.php');
require_once($CFG->dirroot.'/blocks/skills_group/tests/skillsgroupunittest.php');

/**
 * This is the unittest class for skills_group_student.class.php.
 *
 * get_score()
 * get_scores()
 * get_lock_choice()
 * set_lock_choice()
 *
 * @package    block_skills_group
 * @group      block_skills_group_tests
 * @copyright  2014 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class test_skillsgroupstudent extends skills_group_unit_test {

    /**
     * This function tests to see if individual student scores can be retrieved.
     *
     */
    public function test_get_score() {

        $this->configure_settings();

        // Student 0 answers 1 to all questions, which converts to 0.
        $student = new skills_group_student($this->courseid, $this->users[0]->id);
        for ($i = 1; $i <= self::FEEDBACKITEMS; $i++) {
            $this->assertEquals(0, $student->get_score($i));
        }

        // Student 4 answers 5 to all questions, which converts to 4.
        $student = new skills_group_student($this->courseid, $this->users[4]->id);
        for ($i = 1; $i <= self::FEEDBACKITEMS; $i++) {
            $this->assertEquals(4, $student->get_score($i));
        }

        // Student 7 answers 3 to all questions, which converts to 2.
        $student = new skills_group_student($this->courseid, $this->users[7]->id);
        for ($i = 1; $i <= self::FEEDBACKITEMS; $i++) {
            $this->assertEquals(2, $student->get_score($i));
        }
    }

    /**
     * This function tests to see if a full set of student scores can be retrieved.
     *
     */
    public function test_get_scores() {

        $this->configure_settings();

        // Student 1 answers 2 to all questions, which converts to 1.
        $student = new skills_group_student($this->courseid, $this->users[1]->id);
        $scores = $student->get_scores();
        for ($i = 1; $i <= self::FEEDBACKITEMS; $i++) {
            $this->assertEquals(1, $scores[$i]);
        }

        // Student 3 answers 4 to all questions, which converts to 3.
        $student = new skills_group_student($this->courseid, $this->users[3]->id);
        $scores = $student->get_scores();
        for ($i = 1; $i <= self::FEEDBACKITEMS; $i++) {
            $this->assertEquals(3, $scores[$i]);
        }

        // Student 9 answers 5 to all questions, which converts to 4.
        $student = new skills_group_student($this->courseid, $this->users[9]->id);
        $scores = $student->get_scores();
        for ($i = 1; $i <= self::FEEDBACKITEMS; $i++) {
            $this->assertEquals(4, $scores[$i]);
        }
    }
    /**
     * This function tests the flag that locks a student's choice.  I'm testing
     * both the getter and the setter.  Check to ensure that the record defaults
     * to false when no record exists.
     *
     */
    public function test_lock_choice() {
        global $DB;

        $this->configure_settings();

        $sgroup = new skills_group_student($this->courseid, $this->users[0]->id);
        $this->assertFalse($sgroup->get_lock_choice());
        $sgroup->set_lock_choice(true);
        $lockchoice = $DB->get_field('skills_group_student', 'finalizegroup', array('userid' => $this->users[0]->id));
        $this->assertEquals($lockchoice, 1);
        $this->assertTrue($sgroup->get_lock_choice());
        $sgroup->set_lock_choice(false);
        $this->assertFalse($sgroup->get_lock_choice());
    }

}