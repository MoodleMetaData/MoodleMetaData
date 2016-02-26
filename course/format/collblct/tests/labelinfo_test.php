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
require_once($CFG->dirroot.'/course/format/collblct/label_info.class.php');
require_once($CFG->dirroot.'/course/format/collblct/tests/collblctunittest.php');

/**
 * This is the unittest class for the label_info class.
 *
 * This class is actually just a wrapper for several arrays, but it does have one
 * additional function that should be tested.  In certain cases (multiple levels
 * closing on the last activity), the order of the instructions sent to javascript
 * needs to be reversed.  I test a few examples of that here.
 *
 * @package    format_collblct
 * @group      format_collblct_tests
 * @copyright  2012 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class test_labelinfo extends collblct_unit_test {

    /**
     * This function tests the reverse_arrays_from_index() method's ability
     * to reverse three of the arrays based on a particular position.
     *
     */
    public function test_reverse_arrays_from_index() {
        $li = new label_info();
        $answers = new label_info();
        $original = new label_info();

        $this->init_label_info($original);
        $this->init_label_info($li);

        $answers->labelid = array(1087, 1086, 1091, 1089);
        // Close IDs do not get reversed.
        $answers->closeid = array("N1089", "N091", "I1093", "I1093");
        $answers->depthindex = array(2, 1, 2, 2);
        $answers->depthvalue = array(2, 0, 1, 2);
        $li->reverse_arrays_from_index(1);
        $this->compare_li_values($li, $answers);

        // Reset values.
        $this->init_label_info($li);
        $answers->labelid = array(1087, 1089, 1086, 1091);
        // Close IDs do not get reversed.
        $answers->closeid = array("N1089", "N091", "I1093", "I1093");
        $answers->depthindex = array(2, 2, 1, 2);
        $answers->depthvalue = array(2, 2, 0, 1);
        $li->reverse_arrays_from_index(2);
        $this->compare_li_values($li, $answers);

        // Reset values.
        $this->init_label_info($li);
        $li->reverse_arrays_from_index(3);
        // Reversing last index causes no change.
        $this->compare_li_values($li, $original);
    }

    /**
     * This function places some test data in the label_info class.
     *
     * @param object $li label_info object to fill out
     *
     */
    private function init_label_info(&$li) {
        $li->labelid = array(1087, 1089, 1091, 1086);
        $li->closeid = array("N1089", "N091", "I1093", "I1093");
        $li->depthindex = array(2, 2, 2, 1);
        $li->depthvalue = array(2, 2, 1, 0);
    }

    /**
     * This is a small helper method that is used to test the label_info
     * object for the correct values.
     *
     * @param object $valuetotest label_info object to test
     * @param object $answers label_info object containing the correct values
     *
     */
    private function compare_li_values($valuetotest, $answers) {
        $len = count($valuetotest->labelid);

        for ($i = 0; $i < $len; $i++) {
            $this->assertEquals($valuetotest->labelid[$i], $answers->labelid[$i]);
            $this->assertEquals($valuetotest->closeid[$i], $answers->closeid[$i]);
            $this->assertEquals($valuetotest->depthindex[$i], $answers->depthindex[$i]);
            $this->assertEquals($valuetotest->depthvalue[$i], $answers->depthvalue[$i]);
        }

    }
}