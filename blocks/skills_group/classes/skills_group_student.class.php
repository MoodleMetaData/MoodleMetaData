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
require_once($CFG->dirroot.'/mod/feedback/lib.php');
require_once($CFG->dirroot.'/blocks/skills_group/classes/skills_group_setting.class.php');

/**
 * skills_group_student class.
 *
 * This class holds the results of a particular student.  The results need to
 * be retrieved from a specified feedback activity.
 *
 * @package    block_skills_group
 * @copyright  2014 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class skills_group_student {

    /** ID of the course */
    private $courseid;
    /** Feedback activity ID - to retrieve results from */
    private $feedbackid;
    /** Student user ID */
    private $userid;
    /** Item scores */
    private $itemscores = array();

    /**
     * This function stores the needed variables and computes the total skill
     * scores for a student.
     *
     * @param int $courseid The ID of the course.
     * @param int $userid The ID of the user to retrieve the scores for.
     * @param int $itemids The IDs of the feedback items that contain valid scores.
     *
     */
    public function __construct($courseid, $userid, $itemids = null) {

        $this->courseid = $courseid;
        $sgsetting = new skills_group_setting($courseid);
        $this->feedbackid = $sgsetting->get_feedback_id();
        $this->userid = $userid;
        $this->compute_scores();
    }

    /**
     * This function retrieves the scores from the feedback activity for the
     * current student.
     *
     * The feedback results have three different parts:
     * 1) Feedback questions (items)
     * 2) Feedback completed status
     * 3) Feedback question values (responses)
     *
     */
    private function compute_scores() {
        global $DB;

        $feedbackitems = $DB->get_records('feedback_item', array('feedback' => $this->feedbackid), 'position');
        $params = array('feedback' => $this->feedbackid, 'userid' => $this->userid, 'anonymous_response' => FEEDBACK_ANONYMOUS_NO);
        $feedbackcompleted = $DB->get_record('feedback_completed', $params);
        foreach ($feedbackitems as $feedbackitem) {
            if ($feedbackitem->typ == 'multichoice') {
                if ($feedbackcompleted !== false) {
                    $params = array('completed' => $feedbackcompleted->id, 'item' => $feedbackitem->id);
                    $value = $DB->get_record('feedback_value', $params);
                    // We subtract one from the user's score here because we allow values to be 0.
                    $this->itemscores[$feedbackitem->position] = $value->value - 1;
                } else {
                    // Add null valued scored if student has not completed the survey.
                    $this->itemscores[$feedbackitem->position] = null;
                }
            }
        }
    }

    /**
     * This function returns the score of a student for a particular feedback question
     * based on the item ID that it is given.
     *
     * @param int $itemid The ID of the feedback item to retrieve the score for.
     * @return int The score for the given feedback item ID.
     *
     */
    public function get_score($itemid) {
        return $this->itemscores[$itemid];
    }

    /**
     * This function returns the set of scores for the student.
     *
     * @return array The scores for the student.
     *
     */
    public function get_scores() {
        return $this->itemscores;
    }

    /**
     * This function returns whether the student has locked in their choice.
     * IMPORTANT: if no record exists in the table, false must be returned.
     *
     * @return bool T/F indicating if choice has been locked
     *
     */
    public function get_lock_choice() {
        global $DB;

        $lockchoice = $DB->get_field('skills_group_student', 'finalizegroup', array('userid' => $this->userid));
        if ($lockchoice !== false) {
            if ($lockchoice == 1) {
                return true;
            } else {
                return false;
            }
        } else {
            // If no setting exists, return false (safe default).
            return false;
        }
    }

    /**
     * This function updates the flag that determines whether the student has locked in
     * their group choice.
     *
     * @param bool $lockchoice Indicates whether student has locked in their choice.
     *
     */
    public function set_lock_choice($lockchoice) {
        global $DB;

        $record = $DB->get_record('skills_group_student', array('userid' => $this->userid));
        if ($lockchoice === true) {
            if ($record !== false) {
                $record->finalizegroup = 1;
                $DB->update_record('skills_group_student', $record);
            } else {
                $record = new stdClass;
                $record->userid = $this->userid;
                $record->finalizegroup = 1;
                if (!$DB->insert_record('skills_group_student', $record)) {
                    print_error(get_string('dberror', BLOCK_SG_LANG_TABLE));
                }
            }
        } else {
            // Only update if record exists -> no record will default to 0.
            if ($record !== false) {
                $record->finalizegroup = 0;
                $DB->update_record('skills_group_student', $record);
            }
        }
    }
}