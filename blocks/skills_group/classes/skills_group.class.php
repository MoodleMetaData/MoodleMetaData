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
require_once($CFG->dirroot.'/blocks/skills_group/classes/skills_group_student.class.php');
require_once($CFG->dirroot.'/blocks/skills_group/classes/skills_group_setting.class.php');
require_once($CFG->dirroot.'/blocks/skills_group/locallib.php');

/**
 * skills_group class.
 *
 * This class holds the results of a particular group.  The results need to
 * be retrieved from a particular feedback activity.
 *
 * @package    block_skills
 * @copyright  2014 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class skills_group {

    /** Group ID */
    private $groupid;
    /** T/F that keeps track of whether scores have been computed */
    private $scorescomputed = false;
    /** Accumulated scores */
    private $accumulatedscores = array();
    /** Number of students in a group scoring "highly" */
    private $highscores = array();
    /** Total number of group members - used to return averages */
    private $numberofmembers;

    /**
     * This function stores the needed variables and computes the total scores
     * for the group.
     *
     * @param int $groupid The ID of the group to retrieve the scores for.
     * @param int $itemids The IDs of the feedback items that contain valid scores.
     *
     */
    public function __construct($groupid, $itemids = null) {

        $this->groupid = $groupid;
    }

    /**
     * This function returns the name of the group from the database.
     *
     * @return string The name of the group or false if not found.
     *
     */
    public function get_group_name() {
        global $DB;

        return $DB->get_field('groups', 'name', array('id' => $this->groupid));
    }

    /**
     * This function checks to see if an individual user is in a group. If the user
     * is found, true will be returned.
     *
     * @param int $userid ID of the user to check for in the grouping.
     * @return bool T/F indicating whether the user is in the group.
     *
     */
    public function user_in_group($userid) {
        global $DB;

        $record = $DB->get_record('groups_members', array('groupid' => $this->groupid, 'userid' => $userid));
        return ($record !== false) ? true : false;
    }

    /**
     * This function is used to return the count of the number of members in a group.
     *
     * @return int Number of members in group.
     *
     */
    public function count_members() {
        global $DB;

        return $DB->count_records('groups_members', array('groupid' => $this->groupid));
    }

    /**
     * This function returns whether others are allowed to join this group.
     * IMPORTANT: if no record exists in the table, false must be returned.
     *
     * @return bool T/F indicating if others are allowed to join
     *
     */
    public function get_allow_others_to_join() {
        global $DB;

        $allowjoin = $DB->get_field('skills_group', 'allowjoin', array('groupid' => $this->groupid));
        if ($allowjoin !== false) {
            if ($allowjoin == 1) {
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
     * This function updates the flag that determines whether others can join this
     * group.  There are several different cases, depending on the new status of the flag.
     *
     * @param bool $allowjoin Indicates whether students should be allowed to join.
     *
     */
    public function set_allow_others_to_join($allowjoin) {
        global $DB;

        $record = $DB->get_record('skills_group', array('groupid' => $this->groupid));
        if ($allowjoin === true) {
            if ($record !== false) {
                $record->allowjoin = 1;
                $DB->update_record('skills_group', $record);
            } else {
                $record = new stdClass;
                $record->groupid = $this->groupid;
                $record->allowjoin = 1;
                if (!$DB->insert_record('skills_group', $record)) {
                    print_error(get_string('dberror', BLOCK_SG_LANG_TABLE));
                }
            }
        } else {
            // Only update if record exists -> no record will default to 0.
            if ($record !== false) {
                $record->allowjoin = 0;
                $DB->update_record('skills_group', $record);
            }
        }
    }

    /**
     * This function retrieves and returns a list of members in the given group ID.
     *
     * @return array {IDs => names} of all members in group.
     *
     */
    public function get_group_members() {
        global $DB, $USER;

        $params = array($this->groupid, $USER->id);
        $query = "SELECT userid
                  FROM {groups_members}
                  WHERE groupid = ? AND userid <> ?";
        $records = $DB->get_records_sql($query, $params);
        $ids = block_skills_group_strip_to_ids($records);
        sort($ids);
        $names = block_skills_group_retrieve_names($ids);
        // Note: array_combine() will not work with empty arrays.
        if (count($ids) > 0) {
            return array_combine(array_values($ids), $names);
        } else {
            return array();
        }
    }

    /**
     * This function retrieves and returns a list of group members that have (or have not)
     * locked in based on the status of $lock.
     *
     * @param bool $lock T/F determining whether to get locked members (T) or unlocked members (F).
     * @return array {IDs => names} of all members in group.
     *
     */
    public function get_members_list($lock = false) {
        global $DB;

        $members = $this->get_group_members();
        $courseid = $DB->get_field('groups', 'courseid', array('id' => $this->groupid));
        $newlist = array();
        foreach ($members as $key => $member) {
            $student = new skills_group_student($courseid, $key);
            if ($student->get_lock_choice() === $lock) {
                $newlist[$key] = $member;
            }
        }
        return $newlist;
    }

    /**
     * This function accumulates the total score for all members across items on the feedback
     * activity.
     *
     */
    private function compute_scores() {

        global $DB;

        $courseid = $DB->get_field('groups', 'courseid', array('id' => $this->groupid));
        $threshold = (new skills_group_setting($courseid))->get_threshold();
        $members = $DB->get_records('groups_members', array('groupid' => $this->groupid));
        $this->numberofmembers = count($members);
        foreach ($members as $member) {
            $sgs = new skills_group_student($courseid, $member->userid);
            $scores = $sgs->get_scores();
            foreach ($scores as $key => $score) {
                if (isset($this->accumulatedscores[$key])) {
                    $this->accumulatedscores[$key] += $score;
                } else {
                    $this->accumulatedscores[$key] = $score;
                }
                // Count number of scores greater than threshold.
                if ($score > $threshold) {
                    if (isset($this->highscores[$key])) {
                        $this->highscores[$key]++;
                    } else {
                        $this->highscores[$key] = 1;
                    }
                } else {
                    // Ensure that a zero gets entered at some point for the key.
                    if (!isset($this->highscores[$key])) {
                        $this->highscores[$key] = 0;
                    }
                }
            }
        }
        $this->scorescomputed = true;
    }

    /**
     * This function returns the accumulated scores array for all feedback items or for
     * an individual item ID if requested.
     *
     * @param int $itemid The ID of the feedback item to retrieve the score for (if desired).
     * @return array The accumulated score(s) as indicated (all or one).
     *
     */
    public function get_scores($itemid = null) {
        if ($this->scorescomputed === false) {
            $this->compute_scores();
        }
        if ($itemid == null) {
            return $this->accumulatedscores;
        } else {
            return $this->accumulatedscores[$itemid];
        }
    }

    /**
     * This function returns the average scores array for all feedback items or for
     * an individual item ID if requested.
     *
     * @param int $itemid The ID of the feedback item to retrieve the score for (if desired).
     * @return array The average score(s) as indicated (all or one).
     *
     */
    public function get_average_scores($itemid = null) {

        if ($this->scorescomputed === false) {
            $this->compute_scores();
        }
        $averagescores = array();
        foreach ($this->accumulatedscores as $key => $score) {
            $averagescores[$key] = $score / $this->numberofmembers;
        }
        if ($itemid == null) {
            return $averagescores;
        } else {
            return $averagescores[$itemid];
        }
    }

    /**
     * This function encodes the score into the format that is required by the public form.
     * 'SS' => strong skill for more than half the group
     * 'S' => strong skill
     * 'W' => does not appear, just shows up blank
     *
     * @param int $itemid The ID of the feedback item to retrieve the score for (if desired).
     * @return array The average score(s) as indicated (all or one).
     *
     */
    public function get_join_form_score($itemid = null) {

        if ($this->scorescomputed === false) {
            $this->compute_scores();
        }
        $scores = array();
        foreach ($this->highscores as $key => $score) {
            if ($score >= $this->count_members() / 2) {
                $scores[$key] = 'SS';
            } else if ($score > 0) {
                $scores[$key] = 'S';
            } else {
                $scores[$key] = '';     // Weak is not displayed.
            }
        }
        if ($itemid == null) {
            return $scores;
        } else {
            return $scores[$itemid];
        }
    }

}