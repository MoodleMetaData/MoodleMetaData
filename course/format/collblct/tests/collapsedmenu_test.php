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
require_once($CFG->dirroot.'/course/format/collblct/collapsed_menu.class.php');
require_once($CFG->dirroot.'/course/format/collblct/tests/collblctunittest.php');

/**
 * This is the unittest class for the collapsed_menu class.
 *
 * Testing this class is reasonably straightforward, since most of the class is
 * a series of private functions.  The primary method is render_menu() for which
 * the test consists of a series of mods in a test course that I setup.  This
 * series of test mods covers the cases that the plugin needs to be able to handle.
 * I also addded a specific test for the depth generation function as well, by
 * making the results of it running accessible through a public function.
 *
 * NOTES: mod with youtube link should be added to course for testing.
 *
 * @package    format_collblct
 * @group      format_collblct_tests
 * @copyright  2012 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class test_collapsedmenu extends collblct_unit_test{

    /**
     * This function tests the render_menu() method's ability to display
     * the menus properly and handle a number of special cases that should
     * arise.
     *
     * Note: for some reason (I think the cache gets flushed), all of the
     * section tests must go in here.
     *
     */
    public function test_renderarray() {
        global $DB;

        $courseid = $this->testcourseid;
        $course = $DB->get_record('course', array('id' => $courseid));
        rebuild_course_cache($courseid);
        $section = 1;
        $thissection = $DB->get_record('course_sections', array('course' => $courseid, 'section' => $section));
        $cm = new collapsed_menu($course, $section, false);
        $cm->render_menu();

        // Answers are below.
        $labelids = array($this->section1labelids[1], $this->section1labelids[2],
                        $this->section1labelids[3], $this->section1labelids[0],
                        $this->section1labelids[4]);
        $closeids = array("N".$this->section1labelids[2], "N".$this->section1labelids[3],
                        "N".$this->section1labelids[4], "N".$this->section1labelids[4],
                        "N".$this->section1contentids[8]);
        $depthindices = array(2, 2, 2, 1, 1);
        $depthvalues = array(2, 2, 2, 0, 0);
        $numlabels = 5;

        // Test labelinfo values.
        $labelinfo = $cm->get_label_info();
        for ($i = 0; $i < $numlabels; $i++) {
            $this->assertEquals($labelinfo->labelid[$i], $labelids[$i]);
            $this->assertEquals($labelinfo->closeid[$i], $closeids[$i]);
            $this->assertEquals($labelinfo->depthindex[$i], $depthindices[$i]);
            $this->assertEquals($labelinfo->depthvalue[$i], $depthvalues[$i]);
        }

        // Now for mod depths: answers.
        $modids = array($this->section1contentids[0], $this->section1contentids[1],
                        $this->section1contentids[2], $this->section1contentids[3],
                        $this->section1contentids[4], $this->section1contentids[5],
                        $this->section1contentids[6], $this->section1contentids[7],
                        $this->section1contentids[8]);
        $depths = array(0, 0, 0, 1, 0, 0, 0, 0, 0);
        $nummods = 9;

        // Test the moddepth values.
        $moddepths = $cm->get_mod_depths();
        for ($i = 0; $i < $nummods; $i++) {
            $this->assertEquals($moddepths->modid[$i], $modids[$i]);
            $this->assertEquals($moddepths->moddepth[$i], $depths[$i]);
        }

        // Change section to 2, it tests the "inclusive" label end.
        $section = 2;
        $thissection = $DB->get_record('course_sections', array('course' => $courseid, 'section' => $section));
        $cm = new collapsed_menu($course, $section, false);
        $cm->render_menu();

        $labelinfo = $cm->get_label_info();
        $this->assertEquals($labelinfo->labelid[0], $this->section2labelids[0]);
        $this->assertEquals($labelinfo->closeid[0], "I".$this->section2contentids[0]);

        // Go with section 3 next -> this section "reverses" the last entries.
        $section = 3;
        $thissection = $DB->get_record('course_sections', array('course' => $courseid, 'section' => $section));
        $cm = new collapsed_menu($course, $section, false);
        $cm->render_menu();

        $labelids = array($this->section3labelids[0], $this->section3labelids[2], $this->section3labelids[3],
                        $this->section3labelids[1], $this->section3labelids[4], $this->section3labelids[6],
                        $this->section3labelids[5], $this->section3labelids[7], $this->section3labelids[8]);
        $closeids = array("N".$this->section3labelids[1], "N".$this->section3labelids[3],
                        "N".$this->section3labelids[4], "N".$this->section3labelids[4],
                        "N".$this->section3labelids[5], "N".$this->section3labelids[7],
                        "I".$this->section3contentids[0], "I".$this->section3contentids[0],
                        "I".$this->section3contentids[0]);
        $depthindices = array(1, 2, 2, 1, 1, 2, 1, 2, 3);
        $depthvalues = array(0, 1, 1, 0, 0, 1, 0, 1, 2);
        $numlabels = 9;

        $labelinfo = $cm->get_label_info();
        for ($i = 0; $i < $numlabels; $i++) {
            $this->assertEquals($labelinfo->labelid[$i], $labelids[$i]);
            $this->assertEquals($labelinfo->closeid[$i], $closeids[$i]);
            $this->assertEquals($labelinfo->depthindex[$i], $depthindices[$i]);
            $this->assertEquals($labelinfo->depthvalue[$i], $depthvalues[$i]);
        }
    }
}