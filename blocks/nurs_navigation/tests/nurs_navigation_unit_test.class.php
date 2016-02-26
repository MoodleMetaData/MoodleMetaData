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

/**
 * This is class does the course setup for the nurs_navigation tests.
 *
 * I created a derived class here (from advanced_testcase) which all my test classes are
 * then derived from, so they all use the same setUp() and tearDown() routines.  This does
 * slow down the tests a bit but tests should not be that time sensitive anyway.
 *
 * @package    block_course_message
 * @copyright  2012 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class nurs_navigation_unit_test extends advanced_testcase{
    /** This is the course ID of the test course that is setup */
    protected $testcourseid;
    /** This is the block ID of the block that is setup */
    protected $blockid;
    /** This is the full instance of the block */
    protected $blockinstance;
    /** This is the "student" user */
    protected $student;
    /** By default the test course has 5 sections */
    const DEFAULT_NUMBER_OF_SECTIONS = 5;
    /** Name for section 1 */
    const SECTION1_NAME = "Craig's Resources";
    /** Name for section 2 */
    const SECTION2_NAME = "Some Other Section";
    /** Name for section 2 */
    const SECTION5_NAME = "Tutor Resources";
    /** file IDs */
    protected $fileids = array();
    /** file contents (could be anything -> all text files for convenience) */
    protected $filecontents = array('section 1 global', 'section 1 course specific', 'section 2 global',
                                    'section 2 course specific', 'section 3 global', 'section 3 course specific',
                                    'section 4 global');
    /** Indices for file IDs entries in array */
    const TOTAL_FILES = 7;
    const SECTION1_GLOBAL_FILE_ID_INDEX = 0;
    const SECTION1_COURSE_FILE_ID_INDEX = 1;
    const SECTION2_GLOBAL_FILE_ID_INDEX = 2;
    const SECTION2_COURSE_FILE_ID_INDEX = 3;
    const SECTION3_GLOBAL_FILE_ID_INDEX = 4;
    const SECTION3_COURSE_FILE_ID_INDEX = 5;
    const SECTION4_GLOBAL_FILE_ID_INDEX = 6;
    const SECTION5_GLOBAL_FILE_ID_INDEX = 0; // Section 5 also uses section 1 file.

    /**
     * The setup function is used to place the DB in its initial state.
     *
     */
    protected function setUp() {
        // This needs to be here for the dummy test below.
        $this->resetAfterTest(true);

        // Create course.
        $course = $this->getDataGenerator()->create_course();
        $this->testcourseid = $course->id;

        // Create two dummy sections.
        $this->create_section_for_course($course->id, array('sectionnumber' => 1, 'sectionname' => self::SECTION1_NAME));
        $this->create_section_for_course($course->id, array('sectionnumber' => 2, 'sectionname' => self::SECTION2_NAME));
        // Sections 3, 4, 5 use default names.
        $this->create_section_for_course($course->id, array('sectionnumber' => 3));
        $this->create_section_for_course($course->id, array('sectionnumber' => 4));
        $this->create_section_for_course($course->id, array('sectionnumber' => 5, 'sectionname' => self::SECTION5_NAME));
        // Create user.
        $this->create_user();
        // Create an instance of the block.
        $this->create_block();
        // Create files table.
        $this->create_files();

        parent::setUp();
    }

    /**
     * Dummy test to avoid a warning (all files need one test).
     *
     */
    public function test_dummy() {
        $somevariable = 1;
    }

    /**********************************************************************
     * Helper functions are below:
     **********************************************************************/
    /**
     * This function creates a section in a course by writing to the DB.  It's odd
     * that there doesn't seem to be a built-in function to do this, but I couldn't
     * find one.  If this suddenly breaks, it probably means one was added.
     *
     * create_course_section() exists for the data generator, but it won't set a
     * name for a section.
     *
     * @param int $courseid This is the course ID
     * @param array $sectioninfo Information to write to the DB
     *
     */
    protected function create_section_for_course($courseid, $sectioninfo) {
        global $DB;

        $section = new stdClass();
        $section->course = $courseid;
        $section->section = $sectioninfo['sectionnumber'];
        $section->name = isset($sectioninfo['sectionname']) ? $sectioninfo['sectionname'] : null;

        $id = $DB->insert_record('course_sections', $section, true);
    }

    /**
     * This function creates a nurs_navigation block that can be used for
     * testing.  It is created such that it sits in the course that is created.
     *
     */
    protected function create_block() {
        // Create a nurs_navigation block.
        $blockname = 'nurs_navigation';
        $blockrecord = new stdClass();
        $blockrecord->parentcontextid = context_course::instance($this->testcourseid)->id;
        $blockrecord->showinsubcontexts = 1;
        $blockrecord->pagetypepattern = 'course-view-*';
        // I think the configdata entry is OK being blank.

        $this->blockinstance = $this->getDataGenerator()->create_block($blockname, $blockrecord, null);
        // Save the block ID for later (needed for context).
        $this->blockid = $this->blockinstance->id;
    }

    /**
     * This function creates the various files that are used with testing.  All of the files are
     * created as text files, even though the ones used in a course would be images.  With respect
     * to the plugin, however, it doesn't care too much about what the file type is (pluginfile.php
     * will serve up anything).
     *
     */
    protected function create_files() {
        if ($this->blockid == null) {
            echo 'Please create block prior to creating files.  Context may be invalid.';
            return;
        }

        // Sequence of course IDs for saving the files, 1 => global icon.
        $ids = array(1, $this->blockid, 1, $this->blockid, 1, $this->blockid, 1, 1);
        $fs = get_file_storage();

        for ($i = 0; $i < self::TOTAL_FILES; $i++) {
            $filename = 'testfile'.$i.'.txt';
            $itemid = get_unused_file_id();
            $contextid = $ids[$i] == 1 ? context_course::instance($ids[$i])->id : context_block::instance($ids[$i])->id;
            $fileinfo = array(
                    'contextid' => $contextid,
                    'component' => BNN_BLOCK_SAVE_COMPONENT,
                    'filearea' => BNN_BLOCK_SAVE_AREA,
                    'itemid' => $itemid,
                    'filepath' => '/',
                    'filename' => $filename);

            $fs->create_file_from_string($fileinfo, $this->filecontents[$i]);
            // Store ID for later use.
            $this->fileids[] = $itemid;
        }
    }

    /**
     * This function creates a single student and enrolls them in the course that was previously
     * created.
     *
     */
    protected function create_user() {
        global $DB;

        $this->student = $this->getDataGenerator()->create_user();
        $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $this->assertNotEmpty($studentrole);
        $this->getDataGenerator()->enrol_user($this->student->id, $this->testcourseid, $studentrole->id, 'manual');
    }

}