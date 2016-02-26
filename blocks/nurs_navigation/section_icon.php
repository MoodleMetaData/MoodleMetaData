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
require_once(dirname(__FILE__) . '/../../config.php');
global $CFG;
require_once($CFG->dirroot.'/blocks/nurs_navigation/locallib.php');

/**
 * section_icon class
 *
 * This class abstracts the lower level (DB) functionality of the section
 * icons away from the higher layers.  There are routines to create, update,
 * and delete images, as well as get the image URL formatted for a pluginfile.
 * Recently I've also added routines to interact with the image settings, for
 * example, disabling or adding custom text.
 *
 * @package    block_nurs_navigation
 * @copyright  2012 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class section_icon {

    /** This is the course-specific icon record. */
    private $record;
    /** This is the master icon record. */
    private $masterrecord;
    /** This is the settings record: custom features are stored here. */
    private $settingsrecord;
    /** This is the ID of the course that we are getting the icon for. */
    private $courseid;
    /** This caches the section name. */
    private $sectionname;

    /**
     * This method constructs a section icon based on the courseid and section name.  It
     * pulls both the course specific record (if it exists) and master icon record (if it exists)
     * from the database.  Use the exists() routine to check if the course specific record
     * exists.  Special course settings are also retrieved.
     *
     */
    public function __construct($courseid, $sectionname) {
        global $DB;

        $this->courseid = $courseid;
        $this->sectionname = $sectionname;

        $params = array($courseid, $sectionname);
        $query = "SELECT * FROM {nurs_navigation} WHERE courseid = ? AND sectionname = ?";
        $record = $DB->get_record_sql($query, $params, IGNORE_MULTIPLE);

        $this->record = $record;

        $params = array(1, $sectionname);
        $query = "SELECT * FROM {nurs_navigation} WHERE courseid = ? AND sectionname = ?";
        $record = $DB->get_record_sql($query, $params, IGNORE_MULTIPLE);

        $this->masterrecord = $record;

        $params = array($courseid, $sectionname);
        $query = "SELECT * FROM {nurs_navigation_settings} WHERE courseid = ? AND sectionname = ?";
        $record = $DB->get_record_sql($query, $params, IGNORE_MULTIPLE);

        $this->settingsrecord = $record;
    }

    /**
     * This method returns the ID of the course specific image record or 0 if no record
     * exists in the database.
     *
     * @returns int ID of the image record.
     *
     */
    public function get_id() {
        if ($this->exists()) {
            return $this->record->id;
        } else {
            return 0;
        }
    }

    /**
     * This method returns the status of the disableicon field in the database.
     *
     * @returns bool T/F indicating if the disable icon flag is set or not.
     *
     */
    public function get_icon_disable() {
        if ($this->settings_exists() != false) {
            return $this->settingsrecord->disableicon;
        } else {
            return false;
        }
    }

    /**
     * This method returns a custom label if one exists or null if there is not
     * one.
     *
     * @returns string Custom label if it exists or null if there is not one.
     *
     */
    public function get_custom_label() {
        if ($this->settings_exists() != false) {
            return $this->settingsrecord->customlabel;
        } else {
            return null;
        }
    }

    /**
     * This method creates/updates the image record from the database.  If the
     * record already exists, it is updated, or if it is new, the appropriate
     * record is created.
     *
     * @param int $courseid The ID of the course to update the record of (1 = master).
     * @param int $fileid The file ID of the new icon.
     *
     */
    public function update_icon_record($courseid, $fileid) {
        global $DB;

        // Setup ref to master or course specific so it gets updated.
        if ($courseid == 1) {
            $record = &$this->masterrecord;
        } else {
            $record = &$this->record;
        }

        // Now update or create.
        if ($courseid == 1 && $this->master_exists()) {
            $record->fileid = $fileid;
            $DB->update_record('nurs_navigation', $record);
        } else if ($courseid != 1 && $this->exists()) {
            $record->fileid = $fileid;
            $DB->update_record('nurs_navigation', $record);
        } else {
            $record = new stdClass;
            $record->courseid = $courseid;
            $record->sectionname = $this->sectionname;
            $record->fileid = $fileid;

            if (!$DB->insert_record('nurs_navigation', $record)) {
                print_error(get_string('dberror', BNN_LANG_TABLE));
            }
        }
    }

    /**
     * This method creates/updates a custom label in the settings table for
     * an icon in a particular course.  There is no master-equivalent for
     * the settings table, the settings are always course-specific.
     *
     * @param string $customlabel Custom label for icon
     *
     */
    public function update_label($customlabel) {
        if ($this->settingsrecord != false) {
            $record = $this->settingsrecord;
            $record->customlabel = $customlabel;
        } else {
            $record = new stdClass;
            $record->courseid = $this->courseid;
            $record->sectionname = $this->sectionname;
            $record->disableicon = false;
            $record->customlabel = $customlabel;
        }

        $this->write_settings_record($record);
    }

    /**
     * This method creates/updates the setting for whether the icon for a
     * particular section should be visible from within that course.  There's
     * quite a bit of duplication here, but I'm not sure about a cleaner way (other
     * than also passing flags as well) to do this at the moment.
     *
     * @param bool $disableicon T/F indicating whether to disable the icon for that section.
     *
     */
    public function update_disableicon($disableicon) {
        if ($this->settingsrecord != false) {
            $record = $this->settingsrecord;
            $record->disableicon = $disableicon;
        } else {
            $record = new stdClass;
            $record->courseid = $this->courseid;
            $record->sectionname = $this->sectionname;
            $record->disableicon = $disableicon;
            $record->customlabel = null;
        }

        $this->write_settings_record($record);
    }

    /**
     * This method is responsible for writing to the settings table.  It
     * checks to see whether it should update an existing record or create
     * a new record altogether.  I created this routine to remove some of the
     * duplicate code for the two functions above.
     *
     * @param object $record The record to write to the settings table.
     *
     */
    private function write_settings_record(&$record) {
        global $DB;

        if ($this->settingsrecord != false) {
            $DB->update_record('nurs_navigation_settings', $record);
        } else {
            $id = $DB->insert_record('nurs_navigation_settings', $record);
            if ($id === false) {
                print_error(get_string('dberror', BNN_LANG_TABLE));
            } else {
                // On success, grab the new record and store it.
                $this->settingsrecord = $DB->get_record('nurs_navigation_settings', array('id' => $id));
            }
        }
    }

    /**
     * This method deletes the course specific image if it exists.
     *
     */
    public function delete_record() {
        global $DB;

        if ($this->exists()) {
            // Save the file ID.
            $fileid = $this->record->fileid;
            $DB->delete_records('nurs_navigation', array('id' => $this->record->id));
            // Any other sections using the file id?
            $reminaingreferences = $DB->count_records('nurs_navigation', array('fileid' => $fileid));
            // If not, delete.
            if ($reminaingreferences == 0) {
                $DB->delete_records('files', array('itemid' => $fileid));
            }
        }
    }

    /**
     * This method checks to see if the course specific image record exists.
     *
     * @returns bool T/F indicating whether the record exists (T) or not (F).
     *
     */
    public function exists() {
        if ($this->record != false) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * This method checks to see if the master image record exists.
     *
     * @returns bool T/F indicating whether the master record exists (T) or not (F).
     *
     */
    public function master_exists() {
        if ($this->masterrecord != false) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * This method checks to see if the settings record exists.
     *
     * @returns bool T/F indicating whether the settings exists (T) or not (F).
     *
     */
    public function settings_exists() {
        if ($this->settingsrecord != false) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * This method gets and returns the URL of the image (if it exists).  The master
     * image record can be checked if desired (set $checkmaster = true).
     *
     * @param bool $checkmaster T/F indicating whether to check the master icon record if
     * there is no course specific record.
     * @returns string URL of the image or empty string if none exists.
     *
     */
    public function get_image($checkmaster) {
        if ($this->exists()) {
            $image = $this->get_filename($this->record->courseid, $this->record->fileid);
        } else {
            if ($checkmaster && $this->master_exists()) {
                $image = $this->get_filename(1, $this->masterrecord->fileid);
            }
        }
        if (!isset($image)) {
            $image = '';
        }
        return $image;
    }

    /**
     * This private method retrives the filename (url) of the image.
     * image record can be checked if desired (set $checkmaster = true).
     *
     * @param bool $courseid The ID of the course to check.
     * @param int $fileid The ID of the file stored with the image.
     * @returns string URL of the image or empty string if none exists.
     *
     */
    private function get_filename($courseid, $fileid) {
        global $CFG, $DB;

        $coursecontext = context_course::instance($courseid);
        if ($courseid != 1) {
            // For course-specific records, we must retrieve the block context to display the file.
            $params = array($coursecontext->id, 'nurs_navigation');
            $query = "SELECT * FROM {block_instances} WHERE parentcontextid = ? AND blockname = ?";
            $block = $DB->get_record_sql($query, $params, IGNORE_MULTIPLE);
            $context = context_block::instance($block->id);
        } else {
            // For master records, the course context is fine.
            $context = $coursecontext;
        }

        $out = array();
        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, BNN_BLOCK_SAVE_COMPONENT, BNN_BLOCK_SAVE_AREA, $fileid);

        foreach ($files as $file) {
            $filename = $file->get_filename();
            // Skip displaying the null files.
            if ($filename != '.') {
                $url = "/pluginfile.php/{$file->get_contextid()}/" . BNN_BLOCK_SAVE_COMPONENT . "/" . BNN_BLOCK_SAVE_AREA;
                $fileurl = $url . $file->get_filepath() . $file->get_itemid() . '/' . $filename;
            }
        }
        // Check to see if the file was found -> return empty string if no file exists.
        if (isset($fileurl)) {
            return $fileurl;
        } else {
            return '';
        }
    }
}