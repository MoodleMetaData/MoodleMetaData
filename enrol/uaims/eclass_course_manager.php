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

/**
 * Class EclassCourseManager
 */
class EclassCourseManager
{
    /**
     * @param $now Timestamp giving current time.
     */
    public function __construct($now) {
        $this->now = $now;
        $this->coursesopened = false;
        $this->coursesclosed = false;
    }

    public function auto_close_courses() {
        global $DB;
        $coursestoclose = $DB->get_recordset_select('eclass_course_management',
                                                    "lastclosed = '0' AND enddate <= ? AND enddate > 0",
                                                    array($this->now),
                                                    'courseid',
                                                    'id, courseid, enddate');
        if (!$coursestoclose->valid()) {
            return false;
        }
        $courseidsclosed = array();
        foreach ($coursestoclose as $course) {
            if ($this->set_courseid_visible($course->courseid, 0)) {
                array_push($courseidsclosed, $course->courseid);
                $course->lastclosed = $this->now;
                $DB->update_record('eclass_course_management', $course);
            }
        }
        $coursestoclose->close();
        return $courseidsclosed;
    }

    public function auto_open_courses() {
        global $DB;
        $coursestoopen = $DB->get_recordset_select('eclass_course_management',
                                                    "lastopened = '0' AND startdate <= ? AND startdate > 0",
                                                    array($this->now),
                                                    'courseid',
                                                    'id, courseid, startdate');
        if (!$coursestoopen->valid()) {
            return false;
        }
        $courseidsopened = array();
        foreach ($coursestoopen as $course) {
            if ($this->set_courseid_visible($course->courseid, 1)) {
                array_push($courseidsopened, $course->courseid);
                $course->lastopened = $this->now;
                $DB->update_record('eclass_course_management', $course);
            }
        }
        $coursestoopen->close();
        return $courseidsopened;
    }

    public function set_courseid_visible($courseid, $visible) {
        global $DB;
        $course = $DB->get_record('course', array('id' => $courseid), '*');
        if (!$course) {
            return false;
        }
        $course->visible = $visible;
        $DB->update_record('course', $course);
        return true;
    }

    /**
     * Get the opening time stamp of the course
     * @param $courseid
     * @return mixed timestamp or false if no data exists
     */
    public static function get_course_open($courseid) {
        global $DB;
        $start = $DB->get_field('eclass_course_management', 'startdate', array('courseid' => $courseid));
        return $start;
    }

    /**
     * Get the closing time stamp of the course
     * @param $courseid
     * @return mixed timestamp or false if no data exists
     */
    public static function get_course_close($courseid) {
        global $DB;
        $end = $DB->get_field('eclass_course_management', 'enddate', array('courseid' => $courseid));
        return $end;
    }

}
