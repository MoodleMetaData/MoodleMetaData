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
require_once($CFG->dirroot . '/blocks/nurs_navigation/section_icon.php');
require_once($CFG->dirroot . '/blocks/nurs_navigation/locallib.php');
require_once($CFG->dirroot . '/blocks/nurs_navigation/tests/nurs_navigation_unit_test.class.php');

/**
 * This is the unittest class for section_icon.php.
 *
 * @package    block_nurs_navigation
 * @group      block_nurs_navigation_tests
 * @copyright  2012 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class test_sectionicon extends nurs_navigation_unit_test{
    /**
     * The setup function is used to place the DB in its initial state.
     *
     */
    protected function setUp() {
        global $DB,
        $CFG;

        $this->resetAfterTest(true);

        parent::setUp();
        $this->create_section_icon_entries();
    }

    /**
     * This function creates the test data for the various tests below.  The file
     * setup is done on its own in the base class.
     *
     */
    private function create_section_icon_entries() {
        $courseid = $this->testcourseid;

        $sectionheaders = array();
        get_section_titles($courseid, $sectionheaders);

        // Section 1 - {master, then course-specific}.
        $si = new section_icon($courseid, $sectionheaders[0]);
        $si->update_icon_record(1, $this->fileids[self::SECTION1_GLOBAL_FILE_ID_INDEX]);
        $si->update_icon_record($courseid, $this->fileids[self::SECTION1_COURSE_FILE_ID_INDEX]);

        // Section 2 - {master, then course-specific}.
        $si = new section_icon($courseid, $sectionheaders[1]);
        $si->update_icon_record(1, $this->fileids[self::SECTION2_GLOBAL_FILE_ID_INDEX]);
        $si->update_icon_record($courseid, $this->fileids[self::SECTION2_COURSE_FILE_ID_INDEX]);
        $si->update_disableicon(false);

        // Section 3 - {master, then course-specific}.
        $si = new section_icon($courseid, $sectionheaders[2]);
        $si->update_icon_record(1, $this->fileids[self::SECTION3_GLOBAL_FILE_ID_INDEX]);
        $si->update_icon_record($courseid, $this->fileids[self::SECTION3_COURSE_FILE_ID_INDEX]);
        $si->update_disableicon(false);
        $si->update_label("This is a custom label");

        // Section 4 - {master only}.
        $si = new section_icon($courseid, $sectionheaders[3]);
        $si->update_icon_record(1, $this->fileids[self::SECTION4_GLOBAL_FILE_ID_INDEX]);

        // Section 5 - {master only}.
        $si = new section_icon($courseid, $sectionheaders[4]);
        $si->update_icon_record(1, $this->fileids[self::SECTION5_GLOBAL_FILE_ID_INDEX]);
        $si->update_disableicon(true);
        $si->update_label("Custom label, but section disabled");

        // Another entry not attached to a real section.
        $si = new section_icon($courseid, "Disabled Topic");
        $si->update_disableicon(true);
    }

    /**
     * This method tests the constructor.  I then use the exists() and
     * master_exists() methods to make sure that the proper records were
     * grabbed from the DB.
     *
     */
    public function test_constructor() {
        $courseid = $this->testcourseid;
        // Test Craig's Resources first (no settings).
        $sectionname = "Craig's Resources";
        $si = new section_icon($courseid, $sectionname);
        $this->assertTrue((bool)$si->exists());
        $this->assertTrue((bool)$si->master_exists());
        $this->assertFalse((bool)$si->settings_exists());

        // And also another section with a course-specific record (has settings).
        $sectionname = "Some Other Section";
        $si = new section_icon($courseid, $sectionname);
        $this->assertTrue((bool)$si->exists());
        $this->assertTrue((bool)$si->master_exists());
        $this->assertTrue((bool)$si->settings_exists());

        // Topic 4 has no course specific record (has settings).
        $sectionname = "Topic 4";
        $si = new section_icon($courseid, $sectionname);
        $this->assertFalse((bool)$si->exists());
        $this->assertTrue((bool)$si->master_exists());
        $this->assertFalse((bool)$si->settings_exists());

        // Now try a new course altogether.
        $courseid = 9999;
        $sectionname = "Craig's Resources";
        $si = new section_icon($courseid, $sectionname);
        $this->assertFalse((bool)$si->exists());
        $this->assertTrue((bool)$si->master_exists());

        // Now try a section that does not exist.
        $sectionname = "This section does not exist";
        $si = new section_icon($courseid, $sectionname);
        $this->assertFalse((bool)$si->exists());
        $this->assertFalse((bool)$si->master_exists());
        $this->assertFalse((bool)$si->settings_exists());

    }

    /**
     * This method tests get_id().  I check two course specific records:
     * one that exists and oen that doesn't.  The one that does not should
     * return 0.
     *
     */
    public function test_getid() {
        $courseid = $this->testcourseid;
        // Check course with course specific record (has ID).
        $sectionname = "Craig's Resources";
        $si = new section_icon($courseid, $sectionname);
        $this->assertNotEquals($si->get_id(), 0);

        // Now test course without course specific record (no ID).
        $sectionname = "Topic 4";
        $si = new section_icon($courseid, $sectionname);
        $this->assertEquals($si->get_id(), 0);

    }

    /**
     * This method tests get_icon_disable().  This set of tests checks two
     * different sections: one with the icon disabled and one without.  I also
     * check a record that has no settings entry, for which it should return false,
     * since no record exists.
     *
     */
    public function test_get_icon_disable() {
        $courseid = $this->testcourseid;
        // Check course with course specific record that is not disabled.
        $sectionname = "Craig's Resources";
        $si = new section_icon($courseid, $sectionname);
        $this->assertFalse((bool)$si->get_icon_disable());

        // Check course with course specific record that is disabled.
        $sectionname = "Disabled Topic";
        $si = new section_icon($courseid, $sectionname);
        $this->assertTrue((bool)$si->get_icon_disable());

        // Now test course without course specific record -> record does not exist, so returns false.
        $sectionname = "Topic 4";
        $si = new section_icon($courseid, $sectionname);
        $this->assertFalse((bool)$si->get_icon_disable());
    }

    /**
     * This method tests get_custom_label().  The process is the same as the icon
     * disable test (one T, one F, one that does not exist).  In this case, when the
     * record is false or does not exist, the method should return null rather than
     * false.
     *
     */
    public function test_get_custom_label() {
        $courseid = $this->testcourseid;
        // Check course with course specific record that is not disabled.
        $sectionname = "Craig's Resources";
        $si = new section_icon($courseid, $sectionname);
        $this->assertEquals($si->get_custom_label(), null);

        // Check course with course specific record that is disabled.
        $sectionname = "Topic 3";
        $si = new section_icon($courseid, $sectionname);
        $this->assertEquals($si->get_custom_label(), 'This is a custom label');

        // Now test course without course specific record -> record does not exist, so returns false.
        $sectionname = "Record that does not exist";
        $si = new section_icon($courseid, $sectionname);
        $this->assertEquals($si->get_custom_label(), null);
    }

    /**
     * This method tests update_icon_record() for both course-specific entries
     * and master entries.  In each case, I test both creating a new record
     * and updating an existing record.
     *
     */
    public function test_update_icon_record() {
        $courseid = $this->testcourseid;
        // Test 1: Update a course-specific record.
        $sectionname = "Craig's Resources";
        $fileid = 123456789;
        $si = new section_icon($courseid, $sectionname);
        $si->update_icon_record($courseid, $fileid);
        $this->check_section_icon_record($courseid, $sectionname, $fileid);

        // Test 2: Insert a new course-specific record.
        $sectionname = "Topic 4";
        $fileid = 123456789;
        $si = new section_icon($courseid, $sectionname);
        $si->update_icon_record($courseid, $fileid);
        $this->check_section_icon_record($courseid, $sectionname, $fileid);

        // Test 3: existing record, file ID will still be written.
        $sectionname = "Craig's Resources";
        $fileid = 987654321;
        $si = new section_icon($courseid, $sectionname);
        $si->update_icon_record($courseid, $fileid);
        $this->check_section_icon_record($courseid, $sectionname, $fileid);

        $courseid = 1;
        // Test 3: Update a master record.
        $sectionname = "Craig's Resources";
        $fileid = 123456789;
        $si = new section_icon($courseid, $sectionname);
        $si->update_icon_record($courseid, $fileid);
        $this->check_section_icon_record($courseid, $sectionname, $fileid);

        // Test 4: Create a new master record.
        $sectionname = "New Section Header";
        $fileid = 123456789;
        $si = new section_icon($courseid, $sectionname);
        $si->update_icon_record($courseid, $fileid);
        $this->check_section_icon_record($courseid, $sectionname, $fileid);
    }

    /**
     * This method tests delete_record().  I also check to ensure that delete
     * properly chains into the files table when needed.
     *
     */
    public function test_delete_record() {
        global $DB;

        $courseid = $this->testcourseid;

        // Delete a record where nobody else is using that file.
        $sectionname = "Topic 3";
        $si = new section_icon($courseid, $sectionname);
        $fileid = $this->fileids[self::SECTION3_COURSE_FILE_ID_INDEX];
        $si->delete_record();

        // Now check to ensure that it is gone.
        $params = array($courseid, $sectionname);
        $query = "SELECT * FROM {nurs_navigation} WHERE courseid = ? AND sectionname = ?";
        $record = $DB->get_record_sql($query, $params, IGNORE_MULTIPLE);
        $this->assertFalse((bool)$record);
        // And the entry from the files table.
        $record = $DB->get_records('files', array('itemid' => $fileid));
        $this->assertTrue((bool)(count($record) == 0));

        // Delete a record with a shared file.
        $sectionname = "Craig's Resources";
        $si = new section_icon($courseid, $sectionname);
        $fileid = $this->fileids[self::SECTION1_GLOBAL_FILE_ID_INDEX];
        $si->delete_record();

        // Ensure that the navigation entry is gone.
        $params = array($courseid, $sectionname);
        $query = "SELECT * FROM {nurs_navigation} WHERE courseid = ? AND sectionname = ?";
        $record = $DB->get_record_sql($query, $params, IGNORE_MULTIPLE);
        $this->assertFalse((bool)$record);
        // But the files table entry remains.
        $record = $DB->get_records('files', array('itemid' => $fileid));
        $this->assertTrue((bool)(count($record) > 0));
    }

    /**
     * This method tests the update_label() routine.  Two possible outcomes can exist:
     * a new record can be created, or an existing record can be udpated.  I've included
     * both below.
     *
     */
    public function test_update_label() {
        $courseid = $this->testcourseid;

        // Create a new settings record.
        $sectionname = "This is a new section";
        $customlabel = "This is a new label";
        $si = new section_icon($courseid, $sectionname);
        $si->update_label($customlabel);
        // Comment: disable = 0 -> this is what a new record should default to.
        $this->check_section_settings_record($courseid, $sectionname, 0, $customlabel);

        // Update an existing record.
        $sectionname = self::SECTION5_NAME;
        $customlabel = "This is an updated label";
        $si = new section_icon($courseid, $sectionname);
        $si->update_label($customlabel);
        // Comment: disable = 1, since the disable flag is set in the test DB for Topic 5.
        $this->check_section_settings_record($courseid, $sectionname, 1, $customlabel);
    }

    /**
     * This method tests the update_disableicon() routine.  Two possible outcomes can exist:
     * a new record can be created, or an existing record can be udpated.  I've included
     * both below.  This follows almost exactly as the custom label routine, but this need not
     * always be the case, so separate tests are desired.
     *
     */
    public function test_update_disableicon() {
        $courseid = $this->testcourseid;

        // Create a new settings record.
        $sectionname = "This is a new section";
        $disableicon = true;
        $si = new section_icon($courseid, $sectionname);
        $si->update_disableicon($disableicon);
        // Comment: label = null -> this is what a new record should default to.
        $this->check_section_settings_record($courseid, $sectionname, $disableicon, null);

        // Update an existing record.
        $sectionname = self::SECTION5_NAME;
        $disableicon = false;
        $si = new section_icon($courseid, $sectionname);
        $si->update_disableicon($disableicon);
        // The custom label was pulled from the test tables.
        $this->check_section_settings_record($courseid, $sectionname, $disableicon, 'Custom label, but section disabled');
    }

    /**
     * This method tests get_image() for both the master record and course-specific
     * records.  I also check to ensure an empty string is returned when the record
     * does not exist.
     *
     */
    public function test_get_image() {
        $courseid = $this->testcourseid;
        $sectionname = "Craig's Resources";
        $si = new section_icon($courseid, $sectionname);

        // Test for course specific image -> returns course specific image.
        $image = $si->get_image(false);
        $context = context_block::instance($this->blockid);
        $pos = strpos($image, 'pluginfile.php/'.$context->id.'/block_nurs_navigation/nursing_image/'.
                    $this->fileids[self::SECTION1_COURSE_FILE_ID_INDEX].'/testfile1.txt');
        $this->assertTrue((bool)($pos > 0));

        // Test for course specific while allowing master -> still returns course specific first.
        $image = $si->get_image(true);
        $pos = strpos($image, 'pluginfile.php/'.$context->id.'/block_nurs_navigation/nursing_image/'.
                    $this->fileids[self::SECTION1_COURSE_FILE_ID_INDEX].'/testfile1.txt');
        $this->assertTrue((bool)($pos > 0));

        // Flip to record with only master image.
        $sectionname = "Topic 4";
        $si = new section_icon($courseid, $sectionname);

        // Test for course specific -> no record returned.
        $image = $si->get_image(false);
        $this->assertEquals($image, '');
        // Test for master -> master returned.
        $image = $si->get_image(true);
        $context = context_course::instance(1);
        $pos = strpos($image, 'pluginfile.php/'.$context->id.'/block_nurs_navigation/nursing_image/'.
                    $this->fileids[self::SECTION4_GLOBAL_FILE_ID_INDEX].'/testfile6.txt');
        $this->assertTrue((bool)($pos > 0));

        // Flip to record with neither master nor course-specific: make sure empty strings are returned.
        $sectionname = "This topic does not exist";
        $si = new section_icon($courseid, $sectionname);
        // Course specific.
        $image = $si->get_image(false);
        $this->assertEquals($image, '');
        // Master check.
        $image = $si->get_image(true);
        $this->assertEquals($image, '');
    }

    /**********************************************************************
     * Helper functions are below:
     **********************************************************************/
    /**
     * This helper function checks a section icon record based on the set of
     * parameters that it is passed.
     *
     * @param int $courseid The ID of the course to check against.
     * @param string $sectionname The name of the section to check against.
     * @param int $fileid The file ID to check against.
     *
     */
    private function check_section_icon_record($courseid, $sectionname, $fileid) {
        global $DB;

        // Query forces the courseid and sectionname to match.
        $params = array($courseid, $sectionname);
        $query = "SELECT * FROM {nurs_navigation} WHERE courseid = ? AND sectionname = ?";
        $record = $DB->get_record_sql($query, $params, IGNORE_MULTIPLE);
        // Now validate the file ID.
        $this->assertEquals($record->fileid, $fileid);
    }

    /**
     * This helper function checks a section settings record based on the set of
     * parameters that it is passed.
     *
     * @param int $courseid The ID of the course to check against.
     * @param string $sectionname The name of the section to check against.
     * @param bool $disableicon The value for disableicon to check against.
     * @param string $customlabel The custom label to check against.
     *
     */
    private function check_section_settings_record($courseid, $sectionname, $disableicon, $customlabel) {
        global $DB;

        // Query forces the courseid and sectionname to match.
        $params = array($courseid, $sectionname);
        $query = "SELECT * FROM {nurs_navigation_settings} WHERE courseid = ? AND sectionname = ?";
        $record = $DB->get_record_sql($query, $params, IGNORE_MULTIPLE);
        // Now validate the file ID -> explicit cast to bool since database stores as {0, 1}.
        $this->assertEquals((bool)$record->disableicon, $disableicon);
        $this->assertEquals($record->customlabel, $customlabel);
    }
}