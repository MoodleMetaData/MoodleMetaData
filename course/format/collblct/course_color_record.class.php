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
require_once(dirname(__FILE__).'/../../../config.php');
require_once($CFG->dirroot.'/course/format/collblct/locallib.php');

/**
 * This is a fairly simple class that is used to add color records for a particular course.
 * I've created it just to wrap the database calls in a safer manner.  Perhaps in the future it
 * will be extended with more functionality.
 *
 * @package    format_collblct
 * @copyright  2012 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_color_record {
    private $id;
    private $courseid;
    private $backgroundcolor;
    private $foregroundcolor;

    /**
     * This is method constructs the record.  Currently it just passes the call down
     * to the set_courseid() method, since the functionality is the same.  However, this
     * need not always be the case.
     *
     * @param int $courseid The course ID of the record to construct
     *
     */
    public function __construct($courseid) {
        $this->set_courseid($courseid);
    }

    /**
     * This is method returns the course ID of the current color record.
     *
     * @return int Course ID of the current record
     *
     */
    public function get_courseid() {
        return $this->courseid;
    }

    /**
     * This is method sets the current course ID and retrieves the corresponding record.
     *
     * @param int $newcourseid The course ID of the record to return
     *
     */
    public function set_courseid($newcourseid) {
        global $DB;

        $this->courseid = $newcourseid;
        $colorrecord = $DB->get_record('collblct', array('courseid' => $newcourseid));

        if ($colorrecord != false) {
            $this->backgroundcolor = ($colorrecord->backgroundcolor != null) ? $colorrecord->backgroundcolor : DEFAULT_BACKGROUND;
            $this->foregroundcolor = ($colorrecord->foregroundcolor != null) ? $colorrecord->foregroundcolor : DEFAULT_FOREGROUND;
            $this->id = $colorrecord->id; // Store the ID so the record gets updated.
        } else {
            // Fill out defaults if record does not exist.
            $this->backgroundcolor = DEFAULT_BACKGROUND;
            $this->foregroundcolor = DEFAULT_FOREGROUND;
            $this->id = 0; // Flag ID as 0 so we know to create a new record.
        }
    }

    /**
     * This is method returns the current background color for a course
     *
     * @return string The current backround color
     *
     */
    public function get_background_color() {
        return $this->backgroundcolor;
    }

    /**
     * This is method returns the current foreground color for a course
     *
     * @return string The current foreground color
     *
     */
    public function get_foreground_color() {
        return $this->foregroundcolor;
    }

    /**
     * This is method sets the background color for a course
     *
     * @param string $backgroundcolor New background color for course
     * @return bool T/F indicating success or failure
     *
     */
    public function set_background_color($backgroundcolor) {
        $result = $this->write_record($backgroundcolor, $this->foregroundcolor);

        if ($result != false) {
            // Update copy of color in class if successful.
            $this->backgroundcolor = $backgroundcolor;
            $this->id = $result;
            return true;
        } else {
            return false;
        }
    }

    /**
     * This is method sets the foreground color for a course
     *
     * @param string $foregroundcolor New foreground color for course
     * @return bool T/F indicating success or failure
     *
     */
    public function set_foreground_color($foregroundcolor) {
        $result = $this->write_record($this->backgroundcolor, $foregroundcolor);

        if ($result != false) {
            // Update copy of object in class if successful.
            $this->foregroundcolor = $foregroundcolor;
            $this->id = $result;
            return true;
        } else {
            return false;
        }
    }

    /**
     * I've setup this method to have one place to do the DB writes.  This seemed like a
     * better solution than having two functions that looked very similar.
     *
     * @param string $backgroundcolor Background color (will write null if equal to default)
     * @param string $foregroundcolor Foreground color (will write null if equal to default)
     * @return bool T/F indicating success or failure
     *
     */
    private function write_record($backgroundcolor, $foregroundcolor) {
        global $DB;

        $colorrecord = new stdClass();

        // Assume update failed.
        $result = false;

        if ($this->id == 0) {
            $colorrecord->courseid = $this->courseid;
            $colorrecord->foregroundcolor = ($foregroundcolor != DEFAULT_FOREGROUND) ? $foregroundcolor : null;
            $colorrecord->backgroundcolor = ($backgroundcolor != DEFAULT_BACKGROUND) ? $backgroundcolor : null;
            $result = $DB->insert_record('collblct', $colorrecord, true);
        } else {
            $colorrecord->id = $this->id;
            $colorrecord->foregroundcolor = ($foregroundcolor != DEFAULT_FOREGROUND) ? $foregroundcolor : null;
            $colorrecord->backgroundcolor = ($backgroundcolor != DEFAULT_BACKGROUND) ? $backgroundcolor : null;
            $DB->update_record('collblct', $colorrecord);
            $result = $this->id;
        }

        return $result;
    }

    /**
     * This is method deletes any records associated with the current courseid.  This
     * provides a way to flush out the record and force it back to the defaults.
     *
     */
    public function delete_record() {
        global $DB;

        $colorrecord = new stdClass();
        $colorrecord->courseid = $this->courseid;

        if ($DB->count_records('collblct', array('courseid' => $this->courseid))) {
            $DB->delete_records('collblct', array('courseid' => $this->courseid));
        }
    }
}