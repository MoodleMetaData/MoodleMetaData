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
global $DB, $CFG;
require_once("{$CFG->dirroot}/enrol/uaims/eclass_course_manager.php");

/**
 * Tests for the eclass_course_management block.
 *
 * This tests to see if the data accessor retrieves expected data
 *
 * @package    block_eclass_course_management
 * @copyright  2014 Trevor Jones <tdjones@ualberta.ca>
 * @copyright  Joey Andres <jandres@ualberta.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class eclass_course_management_test extends advanced_testcase
{
    public function setUp() {
        global $DB;
        // For convenience provide references to the objects in the tests.
        $this->referenceobjects = array();
        $this->referenceobjects['course1'] = $this->getDataGenerator()->create_course();
        $this->referenceobjects['course2'] = $this->getDataGenerator()->create_course();

        $ci1 = context_course::instance($this->referenceobjects['course1']->id);
        $ci2 = context_course::instance($this->referenceobjects['course2']->id);

        // Create block in course 1.
        $b1 = (object)array('parentcontextid' => $ci1->id);
        $this->referenceobjects['block1'] = $this->getDataGenerator()->create_block('eclass_course_management', $b1);

        // Create block in course 2.
        $b2 = (object)array('parentcontextid' => $ci2->id);
        $this->referenceobjects['block2'] = $this->getDataGenerator()->create_block('eclass_course_management', $b2);

        $record = new stdClass();
        $record->courseid = $this->referenceobjects['course1']->id;
        $record->startdate = '1414595336';
        $record->enddate = '1414695336';
        $record->lastmodified = '1414595336';
        $record->lastopened = '1414595336';
        $record->lastclosed = '';

        $DB->insert_record('eclass_course_management', $record);

    }

    public function tearDown() {
        $this->referenceobjects = array();
    }

    public function test_course_management_with_data() {
        global $CFG, $DB;
        $this->resetAfterTest(false);

        $open = EclassCourseManager::get_course_open($this->referenceobjects['course1']->id);
        $close = EclassCourseManager::get_course_close($this->referenceobjects['course1']->id);

        $this->assertEquals('1414595336', $open);
        $this->assertEquals('1414695336', $close);
    }

    public function test_course_management_without_data() {
        global $CFG, $DB;
        $this->resetAfterTest(false);

        $open = EclassCourseManager::get_course_open($this->referenceobjects['course2']->id);
        $close = EclassCourseManager::get_course_close($this->referenceobjects['course2']->id);
        $this->assertEquals(false, $open);
        $this->assertEquals(false, $close);
    }
}