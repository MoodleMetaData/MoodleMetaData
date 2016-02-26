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
require_once($CFG->dirroot.'/course/format/collblct/course_section_record.class.php');
require_once($CFG->dirroot.'/course/format/collblct/locallib.php');
require_once($CFG->dirroot.'/course/format/collblct/tests/collblctunittest.php');

/**
 * This is the unittest class for the course_section_record class.
 *
 * @package    format_collblct
 * @group      format_collblct_tests
 * @copyright  2012 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class test_coursesectionrecord extends collblct_unit_test {

    /**
     * Test the get_courseid() member -> this test is very straightforward.
     *
     */
    public function test_get_courseid() {
        $csr = new course_section_record(2);
        $this->assertEquals(2, $csr->get_courseid());
    }

    /**
     * Test the set_courseid() member -> this test is also very simple.
     *
     */
    public function test_set_courseid() {
        $csr = new course_section_record(2);
        $this->assertEquals(2, $csr->get_courseid());
        $csr->set_courseid(5);
        $this->assertEquals(5, $csr->get_courseid());
    }

    /**
     * This method tests the get_section_status() member.  First I check that
     * the default (to return true for no record) works properly, then I check
     * to ensure that two existing records return false as they should, since they
     * are set that way in the test tables.
     *
     */
    public function test_get_section_status() {
        $courseid = 3;
        $csr = new course_section_record($courseid);
        $sectionid = 100;
        // Non-existing record returns true (default = ON).
        $this->assertTrue((bool)$csr->get_section_status($sectionid));

        $sectionid = 7;
        // This entry is set to false in the table.
        $this->assertFalse((bool)$csr->get_section_status($sectionid));

        $courseid = 10;
        $sectionid = 15;
        $csr->set_courseid($courseid);
        // This entry is also set to false.
        $this->assertFalse((bool)$csr->get_section_status($sectionid));
    }

    /**
     * This method tests the update_section_record() method by using the
     * get_section_status() member that was tested in the previous test.
     * I do a bit of value toggling and just test to make sure the status
     * flag gets properly updated.
     *
     */
    public function test_update_section_record() {
        $courseid = 2;
        $csr = new course_section_record($courseid);

        $sectionid = 3;
        // This should delete the record.
        $csr->update_section_record($sectionid, true);
        $this->assertTrue((bool)$csr->get_section_status($sectionid));

        // This should re-create the record, but with false.
        $csr->update_section_record($sectionid, false);
        $this->assertFalse((bool)$csr->get_section_status($sectionid));

        $sectionid = 4;
        // Toggle existing record to false.
        $csr->update_section_record($sectionid, false);
        $this->assertFalse((bool)$csr->get_section_status($sectionid));
    }
}