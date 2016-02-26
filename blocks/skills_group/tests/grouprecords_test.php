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
require_once($CFG->dirroot.'/blocks/skills_group/classes/group_records.class.php');
require_once($CFG->dirroot.'/blocks/skills_group/classes/skills_group.class.php');
require_once($CFG->dirroot.'/blocks/skills_group/tests/skillsgroupunittest.php');

/**
 * This is the unittest class for group_records.class.php.
 *
 * get_table_rows()
 * get_skills_list()
 *
 * @package    block_skills_group
 * @group      block_skills_group_tests
 * @copyright  2014 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class test_grouprecords extends skills_group_unit_test {

    /**
     * This function tests to see if individual student scores can be retrieved.
     *
     */
    public function test_get_table_rows() {
        $this->configure_settings();
        $this->allow_group_join();
        $gr = new group_records($this->courseid);
        $rows = $gr->get_table_rows();

        // Group 1 scores 9's across the board (avg -> 4.5), while Group 2 scores 5's across the board (avg -> 2.5).
        $testscores = array('SS', 'SS');
        $i = 0;
        foreach ($rows as $row) {
            $this->assertEquals($row['id'], $this->groupids[$i]);
            for ($j = 0; $j < self::FEEDBACKITEMS; $j++) {
                $this->assertEquals($row[$j], $testscores[$i]);
            }
            $i++;
        }
    }

    /**
     * This function tests retrieving the skills list.  Currently the result is hard-coded
     * so the test will have to be updated.
     *
     */
    public function test_get_skills_list() {

        $this->configure_settings();
        // Now test the returned skills list.
        $gr = new group_records($this->courseid);
        $skillsnames = $gr->get_skills_list();
        $i = 1;
        foreach ($skillsnames as $skillsname) {
            // The test data adds a "?" to the end.
            $this->assertEquals('skill ' . $i++ . '?', $skillsname);
        }
    }

    /**********************************************************************
     * Helper functions are below:
     **********************************************************************/
    /**
     * This function sets it so that all groups have enabled the flag that lets
     * other students join their group.
     *
     */
    private function allow_group_join() {

        for ($i = 0; $i < self::NUMBEROFGROUPS; $i++) {
            $sgroup = new skills_group($this->groupids[$i]);
            $sgroup->set_allow_others_to_join(true);
        }
    }


}