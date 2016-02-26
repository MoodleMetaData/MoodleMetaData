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
 * settings_record class.
 *
 * This class encapsulates a record that the skills_group_settings are stored
 * within.  The constructor assigns safe defaults to any non-existing properties.
 *
 * @package    block_skills_group
 * @copyright  2015 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class settings_record {

    /** Feedback ID */
    public $feedbackid;
    /** Grouping ID */
    public $groupingid;
    /** Maximum Group size */
    public $maxsize;
    /** Score threshold for feedback activities */
    public $threshold;
    /** Whether a date exists (1) or not (0) */
    public $datecheck;
    /** Date to lock group changes */
    public $date;
    /** Allow students to name their own groups */
    public $allownaming;
    /** Default group size if none given */
    const DEFAULTMAXSIZE = 5;
    /** Default threshold if none given */
    const DEFAULTTHRESHOLD = 1;

    /**
     * Create a settings record.
     *
     * @param object $record DB record on which to base the settings record.
     */
    public function __construct($record) {

        $this->groupingid = (isset($record->groupings)) ? $record->groupings : 0;
        $this->feedbackid = (isset($record->feedbacks)) ? $record->feedbacks : 0;
        $this->maxsize = (isset($record->maxsize)) ? $record->maxsize : self::DEFAULTMAXSIZE;
        $this->threshold = (isset($record->threshold)) ? $record->threshold : self::DEFAULTTHRESHOLD;
        $this->datecheck = (isset($record->datecheck)) ? $record->datecheck : 0;
        $this->date = (isset($record->date)) ? $record->date : null;
        $this->allownaming = (isset($record->allownaming)) ? $record->allownaming : true;
    }

}