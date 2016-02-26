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
require_once($CFG->dirroot.'/blocks/skills_group/locallib.php');
require_once($CFG->dirroot.'/blocks/skills_group/classes/settings_record.php');

/**
 * skills_group_setting class
 *
 * This class abstracts the lower level (DB) functionality for the skills_group_settings
 * table.  Fairly straightforward -> can create/delete a record, retrieve IDs or names,
 * and there is a simple method to tell if the record exists.
 *
 * @package    block_skills_group
 * @copyright  2014 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class skills_group_setting {

    /** This is the cached database record. */
    private $record;
    /** This is the ID of the course. */
    private $courseid;

    /**
     * The constructor saves the courseid and also retrieves the record from the database.  It
     * is possible that false could be returned, use method exists() to check.
     *
     * @param int $courseid This is the course ID.
     *
     */
    public function __construct($courseid) {
        $this->courseid = $courseid;
        $this->retrieve_record();
    }

    /**
     * Retrieve the most up-to-date copy of the settings record.
     */
    public function retrieve_record() {
        global $DB;
        $this->record = $DB->get_record('skills_group_settings', array('courseid' => $this->courseid));
    }

    /**
     * This method returns the ID of the feedback activity associated with the skills_group.
     *
     * @returns int ID of feedback activity.
     *
     */
    public function get_feedback_id() {
        return $this->record->feedbackid;
    }

    /**
     * This method returns the name of the feedback activity associated with the skills_group.
     *
     * @returns string Name of feedback activity.
     *
     */
    public function get_feedback_name() {
        global $DB;

        $record = $DB->get_record('feedback', array('id' => $this->record->feedbackid));
        return $record->name;
    }

    /**
     * This method returns the ID of the grouping associated with the skills_group.
     *
     * @returns int ID of grouping.
     *
     */
    public function get_grouping_id() {
        return $this->record->groupingid;
    }

    /**
     * This method returns the name of the grouping associated with the skills_group.
     *
     * @returns string Name of grouping.
     *
     */
    public function get_grouping_name() {
        global $DB;

        $record = $DB->get_record('groupings', array('id' => $this->record->groupingid));
        return $record->name;
    }

    /**
     * This method gets the maximum group size parameter from the settings record.
     *
     * @return int Maximum group size.
     *
     */
    public function get_group_size() {
        return $this->record->maxsize;
    }

    /**
     * This method returns the score threshold.
     *
     * @return int Score threshold to determine difference between low/high
     */
    public function get_threshold() {
        return $this->record->threshold;
    }

    /**
     * This method returns the date restriction field.
     *
     * @return array Date restrction (if it exists).
     */
    public function get_date() {
        // Ensure record is up to date (no cacheing).
        $this->retrieve_record();
        return $this->record->date;
    }

    /**
     * This method checks for a valid date restriction and returns T/F to indicate its
     * existence.
     *
     * @return boolean T if date restriction exists, F if not
     */
    public function date_restriction() {
        // Ensure record is up to date (no cacheing).
        $this->retrieve_record();
        if ($this->record->date !== null) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * This method returns the allownaming field.
     *
     * @return int Whether students should be permitted to name their own groups
     */
    public function get_allownaming() {
        return $this->record->allownaming;
    }

    /**
     * This method updates or creates the settings record.  In both cases, the
     * cached values of the $record member are updated.
     *
     * @param array $settings The settings for the new (or updated) settings record.
     *
     */
    public function update_record($settings) {
        global $DB;

        if (!isset($settings->groupings)) {
            print_error(get_string('groupingmissing', BLOCK_SG_LANG_TABLE));
        }
        $sr = new settings_record($settings);

        if ($this->exists()) {
            $this->record->feedbackid = $sr->feedbackid;
            $this->record->groupingid = $sr->groupingid;
            $this->record->maxsize = $sr->maxsize;
            $this->record->threshold = $sr->threshold;
            $this->record->date = ($sr->datecheck == 1) ? $sr->date : null;
            $this->record->allownaming = $sr->allownaming;
            $DB->update_record('skills_group_settings', $this->record);
        } else {
            $record = new stdClass;
            $record->courseid = $this->courseid;
            $record->feedbackid = $sr->feedbackid;
            $record->groupingid = $sr->groupingid;
            $record->maxsize = $sr->maxsize;
            $record->threshold = $sr->threshold;
            $record->date = ($sr->datecheck == 1) ? $sr->date : null;
            $record->allownaming = $sr->allownaming;

            $id = $DB->insert_record('skills_group_settings', $record);
            if ($id === false) {
                print_error(get_string('dberror', BLOCK_SG_LANG_TABLE));
            } else {
                // On success, grab the new record and store it.
                $this->record = $DB->get_record('skills_group_settings', array('id' => $id));
            }
        }
    }

    /**
     * This method checks to see if the record exists.
     *
     * @return bool T/F indicating whether the record exists (T) or not (F).
     *
     */
    public function exists() {
        if ($this->record != false) {
            return true;
        } else {
            return false;
        }
    }
}