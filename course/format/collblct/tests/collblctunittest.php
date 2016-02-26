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
require_once($CFG->dirroot . '/course/format/collblct/course_color_record.class.php');
require_once($CFG->dirroot . '/course/format/collblct/course_section_record.class.php');

/**
 * This is the master table list for format_collblct testing.
 *
 * I created a derived class here (from advanced_testcase) which all my test classes are
 * then derived from, so they all use the same setUp() and tearDown() routines.  All test tables
 * should be placed here and not in the actual test files.  This does slow down the tests a bit
 * but tests should not be that time sensitive anyway.
 *
 * @package    format_collblct
 * @copyright  2012 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class collblct_unit_test extends advanced_testcase{
    /** This is the course ID of the test course that is setup */
    protected $testcourseid;
    /** By default the test course has 5 sections */
    const DEFAULT_NUMBER_OF_SECTIONS = 5;
    /** These are the CMIDs for activites in section 1 that need to be stored */
    protected $section1labelids = array();
    protected $section1contentids = array();
    /** These are the CMIDs for activites in section 2 that need to be stored */
    protected $section2labelids = array();
    protected $section2contentids = array();
    /** These are the CMIDs for activites in section 3 that need to be stored */
    protected $section3labelids = array();
    protected $section3contentids = array();

    /**
     * The setup function is used to place the DB in its initial state.
     *
     */
    protected function setUp() {
        global $DB,
        $CFG;

        $this->resetAfterTest(true);

        $course = $this->getDataGenerator()->create_course();
        $this->testcourseid = $course->id;
        // Create some dummy sections.
        $this->create_section_for_course($course->id, array('sectionnumber' => 1));
        $this->create_section_for_course($course->id, array('sectionnumber' => 2));
        $this->create_section_for_course($course->id, array('sectionnumber' => 3));
        $this->create_section_for_course($course->id, array('sectionnumber' => 4));
        $this->create_section_for_course($course->id, array('sectionnumber' => 5));
        // Create the content in the sections.
        $this->create_section1();
        $this->create_section2();
        $this->create_section3();
        // Generate color information.
        $this->create_color_information();
        // Finally, section toggles.
        $this->create_section_toggles();

        parent::setUp();
    }

    /**
     * Dummy test to avoid a warning.
     *
     */
    public function test_dummy() {
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
     * This function sets up section 1 by creating the various activity
     * files according to my old test course.
     *
     * Note: the post 2.6 generators no longer seem to add extra fields (such as indent) to
     * the modules as they are created.  Instead, I have to use set_field() to add this info
     * after the fact.
     *
     */
    private function create_section1() {
        global $DB;
        $courseid = $this->testcourseid;

        $labelgenerator = $this->getDataGenerator()->get_plugin_generator('mod_label');
        $pagegenerator = $this->getDataGenerator()->get_plugin_generator('mod_page');
        // Create section 1: pages & labels.
        $this->section1labelids[] = $labelgenerator->create_instance(array('course' => $courseid), array('section' => 1))->cmid;
        $DB->set_field('course_modules', 'indent', 0, array('id' => $this->section1labelids[0]));
        $this->section1contentids[] = $pagegenerator->create_instance(array('course' => $courseid), array('section' => 1))->cmid;
        $DB->set_field('course_modules', 'indent', 1, array('id' => $this->section1contentids[0]));
        $this->section1contentids[] = $pagegenerator->create_instance(array('course' => $courseid), array('section' => 1))->cmid;
        $DB->set_field('course_modules', 'indent', 1, array('id' => $this->section1contentids[1]));
        $this->section1labelids[] = $labelgenerator->create_instance(array('course' => $courseid), array('section' => 1))->cmid;
        $DB->set_field('course_modules', 'indent', 2, array('id' => $this->section1labelids[1]));
        $this->section1labelids[] = $labelgenerator->create_instance(array('course' => $courseid), array('section' => 1))->cmid;
        $DB->set_field('course_modules', 'indent', 2, array('id' => $this->section1labelids[2]));
        $this->section1contentids[] = $pagegenerator->create_instance(array('course' => $courseid), array('section' => 1))->cmid;
        $DB->set_field('course_modules', 'indent', 3, array('id' => $this->section1contentids[2]));
        $this->section1contentids[] = $pagegenerator->create_instance(array('course' => $courseid), array('section' => 1))->cmid;
        $DB->set_field('course_modules', 'indent', 4, array('id' => $this->section1contentids[3]));
        $this->section1labelids[] = $labelgenerator->create_instance(array('course' => $courseid), array('section' => 1))->cmid;
        $DB->set_field('course_modules', 'indent', 2, array('id' => $this->section1labelids[3]));
        $this->section1contentids[] = $pagegenerator->create_instance(array('course' => $courseid), array('section' => 1))->cmid;
        $DB->set_field('course_modules', 'indent', 3, array('id' => $this->section1contentids[4]));
        $this->section1contentids[] = $pagegenerator->create_instance(array('course' => $courseid), array('section' => 1))->cmid;
        $DB->set_field('course_modules', 'indent', 3, array('id' => $this->section1contentids[5]));
        $this->section1labelids[] = $labelgenerator->create_instance(array('course' => $courseid), array('section' => 1))->cmid;
        $DB->set_field('course_modules', 'indent', 0, array('id' => $this->section1labelids[4]));
        $this->section1contentids[] = $pagegenerator->create_instance(array('course' => $courseid), array('section' => 1))->cmid;
        $DB->set_field('course_modules', 'indent', 1, array('id' => $this->section1contentids[6]));
        $this->section1contentids[] = $pagegenerator->create_instance(array('course' => $courseid), array('section' => 1))->cmid;
        $DB->set_field('course_modules', 'indent', 1, array('id' => $this->section1contentids[7]));
        $this->section1contentids[] = $pagegenerator->create_instance(array('course' => $courseid), array('section' => 1))->cmid;
        $DB->set_field('course_modules', 'indent', 0, array('id' => $this->section1contentids[8]));
    }

    /**
     * This function sets up section 2 by creating the various activity
     * files according to my old test course.
     *
     * Section 2 is fairly small.
     *
     * Note: the post 2.6 generators no longer seem to add extra fields (such as indent) to
     * the modules as they are created.  Instead, I have to use set_field() to add this info
     * after the fact.
     *
     */
    private function create_section2() {
        global $DB;
        $courseid = $this->testcourseid;

        $labelgenerator = $this->getDataGenerator()->get_plugin_generator('mod_label');
        $pagegenerator = $this->getDataGenerator()->get_plugin_generator('mod_page');
        // Create section 2: pages & labels.
        $temp = $pagegenerator->create_instance(array('course' => $courseid, 'name' => 'DiAS.', 'content' => 'Test Page'),
                                        array('section' => 2, 'indent' => 0));
        $DB->set_field('course_modules', 'indent', 0, array('id' => $temp->cmid));
        $this->section2labelids[] = $labelgenerator->create_instance(array('course' => $courseid), array('section' => 2))->cmid;
        $DB->set_field('course_modules', 'indent', 0, array('id' => $this->section2labelids[0]));
        $temp = $pagegenerator->create_instance(array('course' => $courseid, 'name' => 'PDF File 5', 'content' => 'Test Page'),
                                        array('section' => 2, 'indent' => 1));
        $DB->set_field('course_modules', 'indent', 1, array('id' => $temp->cmid));
        $this->section2contentids[] = $pagegenerator->create_instance(array('course' => $courseid, 'content' => 'Test Page'),
                                        array('section' => 2, 'indent' => 2))->cmid;
        $DB->set_field('course_modules', 'indent', 2, array('id' => $this->section2contentids[0]));
    }

    /**
     * This function sets up section 3 by creating the various activity
     * files according to my old test course.
     *
     * Section 2 consisted of a bunch of menus in a waterfall shape that
     * had no activity to close them.  Instead, they should be closed based
     * on the section ending.
     *
     * Note: the post 2.6 generators no longer seem to add extra fields (such as indent) to
     * the modules as they are created.  Instead, I have to use set_field() to add this info
     * after the fact.
     *
     */
    private function create_section3() {
        global $DB;
        $courseid = $this->testcourseid;

        $labelgenerator = $this->getDataGenerator()->get_plugin_generator('mod_label');
        $pagegenerator = $this->getDataGenerator()->get_plugin_generator('mod_page');
        // Create section 2: pages & labels.
        $this->section3labelids[] = $labelgenerator->create_instance(array('course' => $courseid), array('section' => 3))->cmid;
        $DB->set_field('course_modules', 'indent', 0, array('id' => $this->section3labelids[0]));

        $temp = $pagegenerator->create_instance(array('course' => $courseid, 'name' => 'USM 2012-13', 'content' => 'Test Page'),
                                        array('section' => 3, 'indent' => 1));
        $DB->set_field('course_modules', 'indent', 1, array('id' => $temp->cmid));

        $this->section3labelids[] = $labelgenerator->create_instance(array('course' => $courseid), array('section' => 3))->cmid;
        $DB->set_field('course_modules', 'indent', 0, array('id' => $this->section3labelids[1]));

        $temp = $pagegenerator->create_instance(array('course' => $courseid, 'name' => 'USM 2012-13', 'content' => 'Test Page'),
                                        array('section' => 3, 'indent' => 1));
        $DB->set_field('course_modules', 'indent', 1, array('id' => $temp->cmid));

        $this->section3labelids[] = $labelgenerator->create_instance(array('course' => $courseid), array('section' => 3))->cmid;
        $DB->set_field('course_modules', 'indent', 1, array('id' => $this->section3labelids[2]));

        $temp = $pagegenerator->create_instance(array('course' => $courseid, 'name' => 'CDD (ALRAC)', 'content' => 'Test Page'),
                                        array('section' => 3, 'indent' => 2));
        $DB->set_field('course_modules', 'indent', 2, array('id' => $temp->cmid));

        $temp = $pagegenerator->create_instance(array('course' => $courseid, 'name' => 'CDD et RDFABBP', 'content' => 'Test Page'),
                                        array('section' => 3, 'indent' => 2));
        $DB->set_field('course_modules', 'indent', 2, array('id' => $temp->cmid));

        $temp = $pagegenerator->create_instance(array('course' => $courseid, 'name' => 'GAC et RDFA', 'content' => 'Test Page'),
                                        array('section' => 3, 'indent' => 2));
        $DB->set_field('course_modules', 'indent', 2, array('id' => $temp->cmid));

        $this->section3labelids[] = $labelgenerator->create_instance(array('course' => $courseid), array('section' => 3))->cmid;
        $DB->set_field('course_modules', 'indent', 1, array('id' => $this->section3labelids[3]));

        $temp = $pagegenerator->create_instance(array('course' => $courseid, 'name' => "TOR BSAOct 2012", 'content' => 'Test Page'),
                                        array('section' => 3, 'indent' => 2));
        $DB->set_field('course_modules', 'indent', 2, array('id' => $temp->cmid));

        $this->section3labelids[] = $labelgenerator->create_instance(array('course' => $courseid), array('section' => 3))->cmid;
        $DB->set_field('course_modules', 'indent', 0, array('id' => $this->section3labelids[4]));

        $temp = $pagegenerator->create_instance(array('course' => $courseid, 'name' => 'USM 2012-13', 'content' => 'Test Page'),
                                        array('section' => 3, 'indent' => 1));
        $DB->set_field('course_modules', 'indent', 1, array('id' => $temp->cmid));

        $temp = $pagegenerator->create_instance(array('course' => $courseid, 'name' => 'Graduate Comp.', 'content' => 'Test Page'),
                                        array('section' => 3, 'indent' => 1));
        $DB->set_field('course_modules', 'indent', 1, array('id' => $temp->cmid));

        $this->section3labelids[] = $labelgenerator->create_instance(array('course' => $courseid), array('section' => 3))->cmid;
        $DB->set_field('course_modules', 'indent', 0, array('id' => $this->section3labelids[5]));

        $this->section3labelids[] = $labelgenerator->create_instance(array('course' => $courseid), array('section' => 3))->cmid;
        $DB->set_field('course_modules', 'indent', 1, array('id' => $this->section3labelids[6]));

        $temp = $pagegenerator->create_instance(array('course' => $courseid, 'name' => 'Application', 'content' => 'Test Page'),
                                        array('section' => 3, 'indent' => 2));
        $DB->set_field('course_modules', 'indent', 2, array('id' => $temp->cmid));

        $this->section3labelids[] = $labelgenerator->create_instance(array('course' => $courseid), array('section' => 3))->cmid;
        $DB->set_field('course_modules', 'indent', 1, array('id' => $this->section3labelids[7]));

        $temp = $pagegenerator->create_instance(array('course' => $courseid, 'name' => 'KINGSTON', 'content' => 'Test Page'),
                                        array('section' => 3, 'indent' => 2));
        $DB->set_field('course_modules', 'indent', 2, array('id' => $temp->cmid));

        $temp = $pagegenerator->create_instance(array('course' => $courseid, 'name' => 'HIGGINBOTTOM', 'content' => 'Test Page'),
                                        array('section' => 3, 'indent' => 2));
        $DB->set_field('course_modules', 'indent', 2, array('id' => $temp->cmid));

        $this->section3labelids[] = $labelgenerator->create_instance(array('course' => $courseid), array('section' => 3))->cmid;
        $DB->set_field('course_modules', 'indent', 2, array('id' => $this->section3labelids[8]));

        $this->section3contentids[] = $pagegenerator->create_instance(array('course' => $courseid, 'content' => 'Test Page'),
                                        array('section' => 3, 'indent' => 3))->cmid;
        $DB->set_field('course_modules', 'indent', 3, array('id' => $this->section3contentids[0]));
    }

    /**
     * This function creates the various color records that are needed
     * for testing.
     *
     */
    private function create_color_information() {
        $courseid = 15;
        $colorrecord = new course_color_record($courseid);
        $colorrecord->set_foreground_color('');
        $colorrecord->set_background_color('');

        $courseid = 18;
        $colorrecord = new course_color_record($courseid);
        $colorrecord->set_foreground_color('#112233');
        $colorrecord->set_background_color('#223344');

        $courseid = 19;
        $colorrecord = new course_color_record($courseid);
        $colorrecord->set_foreground_color('#aabbcc');

        $courseid = 20;
        $colorrecord = new course_color_record($courseid);
        $colorrecord->set_background_color('#667788');
    }

    /**
     * This function creates the various section records that are needed
     * for testing.
     *
     */
    private function create_section_toggles() {
        $courseid = $this->testcourseid;

        $csr = new course_section_record($courseid);
        $csr->update_section_record(3, false);
        // This statement does not create an entry, but I've left it in.
        $csr->update_section_record(4, true);

        $courseid = 3;
        $csr = new course_section_record($courseid);
        $csr->update_section_record(7, false);

        $courseid = 10;
        $csr = new course_section_record($courseid);
        $csr->update_section_record(15, false);
    }
}