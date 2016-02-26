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
require_once($CFG->dirroot.'/course/format/collblct/course_color_record.class.php');
require_once($CFG->dirroot.'/course/format/collblct/locallib.php');
require_once($CFG->dirroot.'/course/format/collblct/tests/collblctunittest.php');

/**
 * This is the unittest class for the course_color_record class.
 *
 * @package    format_collblct
 * @group      format_collblct_tests
 * @copyright  2012 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class test_coursecolorrecord extends collblct_unit_test {

    /**
     * This method tests the constructor.  Primarly, I'm calling the helper
     * method to check the records, which explicity uses the constructor.  This
     * is an awkward construction that I don't really like, but the alternative I
     * can think of requires a lot of repeated code.  I've added a note in the
     * helper method that says it must test the constructor.
     *
     */
    public function test_constructor() {
        $this->check_course_color_record(1, null, null); // This record does not actually exist.
        $this->check_course_color_record(15, null, null);
        $this->check_course_color_record(18, '#112233', '#223344');
        $this->check_course_color_record(19, '#aabbcc', null);
        $this->check_course_color_record(20, null, "#667788");
    }

    /**
     * This method tests set_courseid().  The test is fairly simple, I change the
     * current course ID and make sure the new record has been retrieved.
     *
     */
    public function test_set_courseid() {
        $colorrecord = new course_color_record(15); // Start at 15.
        $colorrecord->set_courseid(18); // Flip to 18.
        $this->assertEquals($colorrecord->get_foreground_color(), '#112233');
        $this->assertEquals($colorrecord->get_background_color(), '#223344');

        $colorrecord->set_courseid(15); // Now back to 15.
        $this->assertEquals($colorrecord->get_courseid(), 15);
    }

    /**
     * This method tests set_foreground_color() by changing the foreground color of
     * a record and verifying that not only the cached copy, but also the copy of
     * the record in the database has been changed.  Additionally, I make sure that
     * changing a color to the default writes a null into the database, rather than
     * the actual color.
     *
     */
    public function test_set_foreground_color() {
        global $DB;

        $newforegroundcolor = '#bbccdd';

        $colorrecord = new course_color_record(18);
        $colorrecord->set_foreground_color($newforegroundcolor);
        // Check cached copy.
        $this->assertEquals($colorrecord->get_foreground_color(), $newforegroundcolor);
        // And directy in the database.
        $record = $DB->get_record('collblct', array('courseid' => 18));
        $this->assertEquals($record->foregroundcolor, $newforegroundcolor);

        $colorrecord->set_foreground_color(DEFAULT_FOREGROUND);
        // Make sure default is returned by member.
        $this->assertEquals($colorrecord->get_foreground_color(), DEFAULT_FOREGROUND);
        // But the database has a null.
        $record = $DB->get_record('collblct', array('courseid' => 18));
        $this->assertEquals($record->foregroundcolor, null);
    }

    /**
     * This method tests set_background_color(). Currently, this test matches the
     * foreground color test exactly, however, I've added it as well since they may
     * not both always be the same.
     *
     */
    public function test_set_background_color() {
        global $DB;

        $newbackgroundcolor = '#778899';

        $colorrecord = new course_color_record(18);
        $colorrecord->set_background_color($newbackgroundcolor);
        // Check cached copy.
        $this->assertEquals($colorrecord->get_background_color(), $newbackgroundcolor);
        // And directy in the database.
        $record = $DB->get_record('collblct', array('courseid' => 18));
        $this->assertEquals($record->backgroundcolor, $newbackgroundcolor);

        $colorrecord->set_background_color(DEFAULT_BACKGROUND);
        // Make sure default is returned by member.
        $this->assertEquals($colorrecord->get_background_color(), DEFAULT_BACKGROUND);
        // But the database has a null.
        $record = $DB->get_record('collblct', array('courseid' => 18));
        $this->assertEquals($record->backgroundcolor, null);
    }

    /**
     * This method tests delete_record().  I try to delete a record that exists and a record
     * that does not exist.  Both cases should be handled without any errors displayed to the
     * user.
     *
     */
    public function test_delete_record() {
        global $DB;

        $courseid = 18; // This record exists.
        $colorrecord = new course_color_record($courseid);
        $colorrecord->delete_record();
        $this->assertEquals($DB->count_records('collblct', array('courseid' => $courseid)), 0);

        $courseid = 3; // This record does not exist.
        $colorrecord = new course_color_record($courseid);
        $colorrecord->delete_record();
        $this->assertEquals($DB->count_records('collblct', array('courseid' => $courseid)), 0);
    }

    /**********************************************************************
     * Helper functions are below:
     **********************************************************************/
    /**
     * This helper function checks a course color record based on the set of
     * parameters that it is passed.  This function must use the constructor,
     * since this is how I've setup the tests above.
     *
     * @param int $courseid The ID of the course to check against.
     * @param string $foregroundcolor The foreground color to check against (can be null).
     * @param int $backgroundcolor The background color to check against (can be null).
     *
     */
    protected function check_course_color_record($courseid, $foregroundcolor, $backgroundcolor) {
        $foregroundcolor = ($foregroundcolor == null) ? DEFAULT_FOREGROUND : $foregroundcolor;
        $backgroundcolor = ($backgroundcolor == null) ? DEFAULT_BACKGROUND : $backgroundcolor;

        $colorrecord = new course_color_record($courseid);
        $this->assertEquals($colorrecord->get_foreground_color(), $foregroundcolor);
        $this->assertEquals($colorrecord->get_background_color(), $backgroundcolor);
    }

}