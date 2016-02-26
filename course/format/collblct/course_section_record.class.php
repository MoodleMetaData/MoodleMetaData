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
class course_section_record {
    private $courseid;

    /**
     * This constructor currently just needs to setup the course id.
     *
     * @param int $courseid The course ID of the record to construct
     *
     */
    public function __construct($courseid) {
        $this->set_courseid($courseid);
    }

    /**
     * This is method returns the course ID.
     *
     * @return int Course ID of the current record
     *
     */
    public function get_courseid() {
        return $this->courseid;
    }

    /**
     * This is method sets the current course ID.
     *
     * @param int $newcourseid The course ID of the record to return
     *
     */
    public function set_courseid($newcourseid) {
        $this->courseid = $newcourseid;
    }

    /**
     * This is method updates the enable/disable flag for a particular section
     * in the course with the stored course ID.  I have set the function up so
     * that only false records are actually stored in the database.  Flagging a
     * record as true simply means that it should be deleted.
     *
     * @param int $sectionid This is the section number to update.
     * @param bool $status This is the new status.
     *
     */
    public function update_section_record($sectionid, $status) {
        global $DB;

        // Enable status should just not be recorded: check for existing record and delete.
        if ($status == true) {
            if ($DB->count_records('collblct_enable', array('courseid' => $this->courseid, 'sectionid' => $sectionid)) > 0) {
                $DB->delete_records('collblct_enable', array('courseid' => $this->courseid, 'sectionid' => $sectionid));
            }
        } else {
            $record = $DB->get_record('collblct_enable', array('courseid' => $this->courseid, 'sectionid' => $sectionid),
                                      '*', IGNORE_MISSING);
            // Does record already exist?
            if ($record != false) {
                // Ensure the status is false (true should just not exist).
                if ($record->status != false) {
                    $record->status = false;
                    $DB->update_record('collblct_enable', $record);
                }
            } else {
                // Create new record.
                $record = new stdClass();
                $record->courseid = $this->courseid;
                $record->sectionid = $sectionid;
                $record->status = $status;

                $result = $DB->insert_record('collblct_enable', $record, true);
            }
        }
    }

    /**
     * This method gets the enable/disable status for a particular section.
     * For records that do not exist, the plugin is assumed to be enabled, since
     * the user would have to select this course format.
     *
     * @param int $sectionid This is the section number.
     * @return bool T/F indicating whether the plugin should be enabled/disable for this section.
     *
     */
    public function get_section_status($sectionid) {
        global $DB;

        $record = $DB->get_record('collblct_enable', array('courseid' => $this->courseid, 'sectionid' => $sectionid));
        if ($record == false) {
            return true;
        } else {
            return $record->status;
        }
    }

}