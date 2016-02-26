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
require_once($CFG->dirroot . '/blocks/nurs_navigation/locallib.php');
require_once($CFG->dirroot . '/blocks/nurs_navigation/tests/nurs_navigation_unit_test.class.php');

/**
 * This is the unittest class for locallib.php.
 *
 * @package    block_nurs_navigation
 * @group      block_nurs_navigation_tests
 * @copyright  2012 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class test_nursnavigationlib extends nurs_navigation_unit_test{

    /**
     * The setup function is used to place the DB in its initial state.
     *
     */
    protected function setUp() {
        global $DB,
        $CFG;

        $this->resetAfterTest(true);

        parent::setUp();
    }

    /**
     * This method tests get_unused_file_id().  After getting the ID, test to ensure
     * that it does not exist anywhere in the {files} table.
     *
     */
    public function test_get_unused_file_id() {
        global $DB;

        $fileid = get_unused_file_id();
        $this->assertEquals($DB->count_records('files', array('itemid' => $fileid)), 0);
    }

    /**
     * This method tests check_draft_id().  I test the two cases that can
     * exist: one where the ID is valid, one where it is not.
     *
     */
    public function test_check_draft_id() {
        // This ID exists.
        $fileid = $this->fileids[0];
        $this->assertTrue((bool)check_draft_id($fileid));

        // While this one does not.
        $fileid = get_unused_file_id();
        $this->assertFalse((bool)check_draft_id($fileid));
    }

    /**
     * This method tests get_number_of_sections().  I check both a valid
     * course ID and an invalid course ID.
     *
     */
    public function test_get_number_of_sections() {
        $courseid = $this->testcourseid;
        $count = get_number_of_sections($courseid);
        $this->assertEquals($count, 5);

        $courseid = 15;
        // Temporarily removed -> will fire an exception, need to figure out how to do this with phpunit.
        // Must look like comment: $count = get_number_of_sections($courseid);.
        // Must look like comment: $this->assertEquals($count, 0);.

    }

    /**
     * This method tests get_section_titles().  I check the first three section
     * titles for the course with an ID of 2.  Two of these are set, while the
     * third is set to NULL and is converted to "Topic X" by the function.  I also
     * ensure that no more than three entries are set, even though there are more
     * than three entries in the {course_sections} database.  That is, only the
     * active sections are returned.
     *
     */
    public function test_get_section_titles() {
        $courseid = $this->testcourseid;
        $sectionheaders = array();

        $count = get_section_titles($courseid, $sectionheaders);

        $this->assertEquals($count, self::DEFAULT_NUMBER_OF_SECTIONS);
        $this->assertEquals($sectionheaders[0], "Craig's Resources");
        $this->assertEquals($sectionheaders[1], "Some Other Section");
        $this->assertEquals($sectionheaders[2], "Topic 3");

        $this->assertFalse((bool)isset($sectionheaders[self::DEFAULT_NUMBER_OF_SECTIONS]));
    }

}