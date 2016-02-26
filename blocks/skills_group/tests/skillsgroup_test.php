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
require_once($CFG->dirroot.'/blocks/skills_group/classes/skills_group.class.php');
require_once($CFG->dirroot.'/blocks/skills_group/tests/skillsgroupunittest.php');

/**
 * This is the unittest class for skills_group.class.php.
 *
 * get_group_name()
 * user_in_group()
 * count_members()
 * get_allow_others_to_join()
 * set_allow_others_to_join()
 * get_group_members()
 * get_scores()
 * get_average_score()
 * get_average_scores()
 *
 * @package    block_skills_group
 * @group      block_skills_group_tests
 * @copyright  2014 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class test_skillsgroup extends skills_group_unit_test {

    /**
     * This function tests if group names are returned correctly.
     *
     */
    public function test_get_group_name() {
        global $DB;

        $sgroup = new skills_group($this->groupids[0]);
        $name = $sgroup->get_group_name();
        $nameindb = $DB->get_field('groups', 'name', array('id' => $this->groupids[0]));
        $this->assertEquals($name, $nameindb);
    }

    /**
     * This function tests whether the class detect users properly in groups.
     *
     */
    public function test_user_in_group() {

        $sgroup = new skills_group($this->groupids[0]);
        // Only the last two users are in the group.
        $this->assertTrue($sgroup->user_in_group($this->users[self::NUMBEROFUSERS - 1]->id));
        $this->assertFalse($sgroup->user_in_group($this->users[0]->id));
        $this->assertFalse($sgroup->user_in_group($this->users[4]->id));
    }

    /**
     * This function tests whether the class can count group members correctly.
     *
     */
    public function test_count_members() {

        $sgroup = new skills_group($this->groupids[0]);
        // Only the last two users are in the group.
        $this->assertEquals($sgroup->count_members(), 2);
    }

    /**
     * This function tests the flag that lets others join a group.  I'm testing
     * both the getter and the setter.  Check to ensure that the record defaults
     * to false when no record exists.
     *
     */
    public function test_allow_others_to_join() {
        global $DB;

        $sgroup = new skills_group($this->groupids[0]);
        $this->assertFalse($sgroup->get_allow_others_to_join());
        $sgroup->set_allow_others_to_join(true);
        $allowjoin = $DB->get_field('skills_group', 'allowjoin', array('groupid' => $this->groupids[0]));
        $this->assertEquals($allowjoin, 1);
        $this->assertTrue($sgroup->get_allow_others_to_join());
        $sgroup->set_allow_others_to_join(false);
        $this->assertFalse($sgroup->get_allow_others_to_join());
    }

    /**
     * This function checks to see if the correct group members are returned.  Again,
     * it is assumed that the list is sorted numerically.
     *
     */
    public function test_get_group_members() {
        $this->configure_settings();
        $sgroup = new skills_group($this->groupids[0]);

        $students = $sgroup->get_group_members();
        $i = 2;
        foreach ($students as $id => $name) {
            $this->assertEquals($id, $this->users[self::NUMBEROFUSERS - $i--]->id);
        }
    }

    /**
     * This function checks for locked and unlocked group members to see if the two lists
     * are correctly returned.  The group consists of one locked member (the last ID) and
     * one unlocked member.
     *
     */
    public function test_get_members_list() {
        $this->configure_settings();
        $sgroup = new skills_group($this->groupids[0]);

        $userindex = self::NUMBEROFUSERS - 1;
        $students = $sgroup->get_members_list(true);
        $this->assertEquals(count($students), 1);
        $ids = array_keys($students);
        $this->assertEquals($ids[0], $this->users[self::NUMBEROFUSERS - 1]->id);
        $students = $sgroup->get_members_list(false);
        $this->assertEquals(count($students), 1);
        $ids = array_keys($students);
        $this->assertEquals($ids[0], $this->users[self::NUMBEROFUSERS - 2]->id);
    }

    /**
     * This function tests get_scores().  Both modes are tested, first, by retrieving
     * all scores and then by retrieving individual scores.  This particular function
     * returns the total aggregate scores (rather than averages).
     *
     */
    public function test_get_scores() {

        $this->configure_settings();
        // Group 1 scores 7's across the board, while Group 2 scores 3's across the board.
        $testscores = array(7, 3);
        for ($i = 0; $i < self::NUMBEROFGROUPS; $i++) {
            // Now test the group.
            $sgroup = new skills_group($this->groupids[$i]);
            $scores = $sgroup->get_scores();
            foreach ($scores as $score) {
                $this->assertEquals($score, $testscores[$i]);
            }
        }
        // Now test getting individual score.
        for ($i = 0; $i < self::NUMBEROFGROUPS; $i++) {
            $sgroup = new skills_group($this->groupids[$i]);
            for ($j = 1; $j <= self::FEEDBACKITEMS; $j++) {
                $this->assertEquals($sgroup->get_scores($j), $testscores[$i]);
            }
        }
    }

    /**
     * This function tests get_average_scores().  Both modes are tested, first, by
     * retrieving all scores and then by retrieving individual scores.  This particular
     * function returns the average scores.
     *
     */
    public function test_get_average_scores() {

        $this->configure_settings();
        // Group 1 scores 7's across the board (avg -> 3.5), while Group 2 scores 3's across the board (avg -> 1.5).
        $testscores = array(3.5, 1.5);
        for ($i = 0; $i < self::NUMBEROFGROUPS; $i++) {
            // Now test the group.
            $sgroup = new skills_group($this->groupids[$i]);
            $scores = $sgroup->get_average_scores();
            foreach ($scores as $score) {
                $this->assertEquals($score, $testscores[$i]);
            }
        }
        // Now test getting individual score.
        for ($i = 0; $i < self::NUMBEROFGROUPS; $i++) {
            $sgroup = new skills_group($this->groupids[$i]);
            for ($j = 1; $j <= self::FEEDBACKITEMS; $j++) {
                $this->assertEquals($sgroup->get_average_scores($j), $testscores[$i]);
            }
        }
    }

    /**
     * This function tests get_join_form_score().  Both modes are tested, first, by
     * retrieving all scores and then by retrieving individual scores.  This particular
     * function returns the code scores for the joining process.
     *
     */
    public function test_get_join_form_score() {

        $this->configure_settings();
        // Group 1 has both in "strong" region, while Group 2 has one in "strong" region (half).
        $expected = array('SS', 'SS');
        $this->verify_join_form_scores($expected);

        // Raise threshold -> Group 2 will have both in weak region.
        $this->configure_settings($threshold = 2);
        $expected = array('SS', '');
        $this->verify_join_form_scores($expected);
    }

    /**
     * This function runs through the scores (both all and individual items) and compares
     * them to the desired result stored in $expected.
     *
     * @param      array  $expected  Desired outcome of score comparison.
     */
    private function verify_join_form_scores($expected) {

        for ($i = 0; $i < self::NUMBEROFGROUPS; $i++) {
            // Now test the group.
            $sgroup = new skills_group($this->groupids[$i]);
            $scores = $sgroup->get_join_form_score();
            foreach ($scores as $score) {
                $this->assertEquals($score, $expected[$i]);
            }
        }
        // Now test getting individual score.
        for ($i = 0; $i < self::NUMBEROFGROUPS; $i++) {
            $sgroup = new skills_group($this->groupids[$i]);
            for ($j = 1; $j <= self::FEEDBACKITEMS; $j++) {
                $this->assertEquals($sgroup->get_join_form_score($j), $expected[$i]);
            }
        }
    }

}