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
require_once("{$CFG->dirroot}/config.php");
require_once("{$CFG->dirroot}/enrol/uaims/eclass_course_manager.php");
/**
 * Tests for the eClass Course Manager.
 *
 * Test automatic opening and closing of courses based on dates
 * stored in eclass_course_management DB table.
 *
 * @package    enrol_uaims
 * @copyright  2014 Dom Royko royko@ualberta.ca
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class eclass_course_manager_test extends advanced_testcase {

    public function test_set_courseid_visibility() {
        global $DB;
        $this->resetAfterTest(true);

        $ecm = new EclassCourseManager(time());

        // Create a visible course.
        $course = $this->getDataGenerator()->create_course();
        $this->assertEquals(1, $course->visible);

        // Make it invisible.
        $ecm->set_courseid_visible($course->id, 0);
        $after = get_course($course->id);
        $this->assertEquals(0, $after->visible);

        // Make it visible again.
        $ecm->set_courseid_visible($course->id, 1);
        $after = get_course($course->id);
        $this->assertEquals(1, $after->visible);

        // Try setting visibility of non-existent course.
        $this->assertEquals(false, $ecm->set_courseid_visible($course->id + 1, 1));
    }

    public function test_auto_openclose_courses() {
        global $DB, $CFG;
        $this->resetAfterTest(true);

        $dbtables = $DB->get_tables(false);
        $this->assertEquals(true, array_key_exists('eclass_course_management', $dbtables));

        $eergisteren = time() - 2 * 86400;
        $gisteren = time() - 86400;
        $morgen = time() + 86400;
        $overmorgen = time() + 2 * 86400;

        // Course names are appended with Start, Now, and End, in chronological order.
        $coursense = $this->create_course_vis_start_end(0, $morgen, $overmorgen);
        $coursesne = $this->create_course_vis_start_end(0, $gisteren, $morgen);
        $coursesen = $this->create_course_vis_start_end(0, $eergisteren, $gisteren);
        $coursenes = $this->create_course_vis_start_end(0, $overmorgen, $morgen);
        $courseens = $this->create_course_vis_start_end(1, $morgen, $gisteren);
        $courseesn = $this->create_course_vis_start_end(0, $gisteren, $eergisteren);

        // Bad course change data.
        $coursebadstart = $this->create_course_vis_start_end(0, 0, $overmorgen);
        $coursebadend = $this->create_course_vis_start_end(0, $gisteren, 0);
        $coursebadstartend = $this->create_course_vis_start_end(1, 0, 0);

        $courseopendone = $this->create_course_vis_start_end(0, $gisteren, $morgen, time() - 86000);
        $courseclosedone = $this->create_course_vis_start_end(1, $eergisteren, $gisteren, null, time() - 86000);

        // Model open/close info for a course which has been deleted.
        // Processing this record must not crash the functions.
        $deletedcourseid = $courseclosedone->id + 1;
        $ecmrecord = new stdClass();
        $ecmrecord->courseid = $deletedcourseid;
        $ecmrecord->startdate = $eergisteren;
        $ecmrecord->enddate = $gisteren;
        $DB->insert_record('eclass_course_management', $ecmrecord);

        $ecm = new EclassCourseManager(time());

        $ecm->auto_open_courses();

        $this->check_courseid_vis_opened_closed($coursense->id, 0, true, null);
        $this->check_courseid_vis_opened_closed($coursesne->id, 1, false, null);
        $this->check_courseid_vis_opened_closed($coursesen->id, 1, false, null);
        $this->check_courseid_vis_opened_closed($coursenes->id, 0, true, null);
        $this->check_courseid_vis_opened_closed($courseens->id, 1, true, null);
        $this->check_courseid_vis_opened_closed($courseesn->id, 1, false, null);

        // Check the bad start/end date courses.
        $this->check_courseid_vis_opened_closed($coursebadstart->id, 0, true, true);
        $this->check_courseid_vis_opened_closed($coursebadend->id, 1, false, true);
        $this->check_courseid_vis_opened_closed($coursebadstartend->id, 1, true, true);

        $this->check_courseid_vis_opened_closed($courseopendone->id, 0, false, null);
        $this->check_courseid_vis_opened_closed($courseclosedone->id, 1, false, false);

        $ecm->auto_close_courses();

        $this->check_courseid_vis_opened_closed($coursense->id, 0, null, true);
        $this->check_courseid_vis_opened_closed($coursesne->id, 1, null, true);
        $this->check_courseid_vis_opened_closed($coursesen->id, 0, null, false);
        $this->check_courseid_vis_opened_closed($coursenes->id, 0, null, true);
        $this->check_courseid_vis_opened_closed($courseens->id, 0, null, false);
        $this->check_courseid_vis_opened_closed($courseesn->id, 0, null, false);

        $this->check_courseid_vis_opened_closed($courseopendone->id, 0, false, true);
        $this->check_courseid_vis_opened_closed($courseclosedone->id, 1, false, false);
    }

    /**
     * Tests that the library returns the correct start/end dates
     */
    public function test_returns_correct_dates() {
        // Required if modifying database within the test.
        $this->resetAfterTest(true);

        $start = time() + 86400;
        $end = time() + 2 * 86400;

        // Course names are appended with Start, Now, and End, in chronological order.
        $course = $this->create_course_vis_start_end(0, $start, $end);
        $this->assertEquals($start, EclassCourseManager::get_course_open($course->id));
        $this->assertEquals($end, EclassCourseManager::get_course_close($course->id));
    }

    /**
     * Helper for test_auto_openclose_courses() and test_enrol_uaims_cron().
     * @param $visible
     * @param $startdate
     * @param $enddate
     * @param null $lastopened
     * @param null $lastclosed
     * @return stdClass The created course object
     */
    public function create_course_vis_start_end($visible, $startdate, $enddate,
            $lastopened = null, $lastclosed = null) {
        global $DB;

        $ecm = new EclassCourseManager(time());
        $course = $this->getDataGenerator()->create_course();
        $ecm->set_courseid_visible($course->id, $visible);

        $ecmrecord = new stdClass();
        $ecmrecord->courseid = $course->id;
        $ecmrecord->startdate = $startdate;
        $ecmrecord->enddate = $enddate;
        if (isset($lastopened)) {
            $ecmrecord->lastopened = $lastopened;
        }
        if (isset($lastclosed)) {
            $ecmrecord->lastclosed = $lastclosed;
        }
        $DB->insert_record('eclass_course_management', $ecmrecord);

        return $course;
    }

    /**
     * Helper for test_auto_openclose_courses() and test_enrol_uaims_cron().
     * @param $courseid
     * @param $visible Assert that course visibility matches this value
     * @param null $lastopenedshouldbezero true, we assert for zero values; false, we assert not non-null values; null, we don't
     * assert
     * @param null $lastclosedshouldbezero true, we assert for zero values; false, we assert not non-null values; null, we don't
     * assert
     */
    public function check_courseid_vis_opened_closed($courseid, $visible,
            $lastopenedshouldbezero = null, $lastclosedshouldbezero = null) {
        global $DB;

        $course = get_course($courseid);
        $ecmrecord = $DB->get_record('eclass_course_management',
                array('courseid' => $courseid));
        $this->assertEquals($visible, $course->visible);

        if (isset($lastopenedshouldbezero)) {
            if ($lastopenedshouldbezero) {
                $this->assertEquals(0, $ecmrecord->lastopened);
            } else {
                $this->assertNotEquals(0, $ecmrecord->lastopened);
            }
        }
        if (isset($lastclosedshouldbezero)) {
            if ($lastclosedshouldbezero) {
                $this->assertEquals(0, $ecmrecord->lastclosed);
            } else {
                $this->assertNotEquals(0, $ecmrecord->lastclosed);
            }
        }
    }

}
