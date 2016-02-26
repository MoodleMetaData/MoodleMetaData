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
require_once($CFG->dirroot.'/user/lib.php');
require_once($CFG->dirroot.'/group/lib.php');
require_once($CFG->dirroot.'/blocks/skills_group/locallib.php');
require_once($CFG->dirroot.'/blocks/skills_group/classes/skills_group_setting.class.php');
require_once($CFG->dirroot.'/blocks/skills_group/classes/skills_group_student.class.php');
require_once($CFG->dirroot.'/lib/accesslib.php');

/**
 * skills_grouping class
 *
 * This class is a helper class when working with groupings/groups.  It is
 * used to determine who are the potential members that could be assigned into
 * a new group (must be in course and not already grouped) and also to determine
 * the members of an existing group.
 *
 * @package    block_skills_group
 * @copyright  2014 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class skills_grouping {

    /** This is the ID of the course. */
    private $courseid;

    /**
     * Constructor holds onto the necessary values, but performs no actions.
     *
     * @param int $courseid The ID of the course.
     *
     */
    public function __construct($courseid) {

        $this->courseid = $courseid;
    }

    /**
     * This function creates a group with a particular name, adds it to the grouping,
     * and then adds the current user to that group.
     *
     * @param string $name The name to assign to the newly created group.
     * @return int The ID of the group that was group that was created.
     *
     */
    public function create_group($name) {
        global $DB, $USER;

        $sgs = new skills_group_setting($this->courseid);
        $group = new stdClass();
        if ($name === null || trim($name) == '') {
            $name = $this->name_empty_group();
        }
        $group->name = $name;
        $group->courseid = $this->courseid;
        $groupid = groups_create_group($group);
        groups_assign_grouping($sgs->get_grouping_id(), $groupid);
        groups_add_member($groupid, $USER->id);
        return $groupid;
    }

    /**
     * This function creates a name for a group that does not have one or if the
     * instructor has prevented student naming.
     *
     * It searches the current course for the first open name of format "Team XX".
     *
     * @return string name to be used for group
     *
     */
    private function name_empty_group() {
        global $DB;

        $like = get_string('groupautoname', BLOCK_SG_LANG_TABLE) . ' %';
        $sql = "SELECT name
                FROM {groups}
                WHERE name LIKE '" . $like . "' and courseid = :courseid
                ORDER BY name ASC";
        $params = array('courseid' => $this->courseid);
        $records = $DB->get_records_sql($sql, $params);
        if (count($records) == 0) {
            // No records -> use 1.
            $i = 1;
        } else {
            $numerics = $this->strip_text_from_names($records);
            // Use range and diff to find gaps in sequence.
            $ordered = range(1, max($numerics));
            $missing = array_diff($ordered, $numerics);
            if (count($missing) > 0) {
                // Use first gap in sequence.
                $i = reset($missing);
            } else {
                // No results -> arrays match perfectly, so increment by 1.
                $i = count($numerics) + 1;
            }
        }
        $name = get_string('groupautoname', BLOCK_SG_LANG_TABLE) . ' ' . str_pad($i, 2, '0', STR_PAD_LEFT);
        return $name;
    }

    /**
     * Strip the text from an array of records, leaving only the numeric portion of the name.
     *
     * @param array $records records returned from DB with name field
     * @return array set of values already used in team names
     */
    private function strip_text_from_names($records) {

        $numerics = array();
        foreach ($records as $r) {
            $temp = preg_replace('/[^0-9.]+/', '', $r->name);
            if ($temp !== '') {
                $numerics[] = (int)$temp;
            }
        }
        return $numerics;
    }

    /**
     * This function calculates all unassigned students for a particular grouping.
     * We enforce a rule that students can only be a member of one group in the
     * grouping.
     *
     * @return array {IDs => names) of potential members.
     *
     */
    public function get_potential_students() {

        $student = get_archetype_roles('student');
        $student = reset($student);
        $allmembers = groups_get_potential_members($this->courseid, $student->id);
        $allocatedmembers = $this->get_all_grouped_students();
        $potentialmemberids = array();
        foreach ($allmembers as $allmember) {
            if (array_search($allmember->id, $allocatedmembers) === false) {
                $potentialmemberids[] = $allmember->id;
            }
        }
        sort($potentialmemberids);
        $potentialmembernames = block_skills_group_retrieve_names($potentialmemberids);
        // Note: array_combine() will not work with empty arrays.
        if (count($potentialmemberids) > 0) {
            return array_combine($potentialmemberids, $potentialmembernames);
        } else {
            return array();
        }
    }

    /**
     * This function retrieves a list of all students currently within a group in the
     * saved grouping.
     *
     * @return mixed Array containing grouped member IDs or false if none found.
     *
     */
    private function get_all_grouped_students() {
        global $DB;

        $sgs = new skills_group_setting($this->courseid);
        $params = array($sgs->get_grouping_id());
        $query = "SELECT DISTINCT b.userid
                  FROM {groupings_groups} a INNER JOIN {groups_members} b ON a.groupid = b.groupid
                  WHERE a.groupingid = ?";
        $records = $DB->get_records_sql($query, $params);
        if ($records === false) {
            return false;
        } else {
            return block_skills_group_strip_to_ids($records);
        }
    }

    /**
     * This function checks to see if an individual user is in a group inside
     * a grouping.  If the user is found, the ID of the group that the user
     * belongs to is returned.
     *
     * @param int $userid ID of the user to check for in the grouping.
     * @return mixed The ID of the group if found or false if not found.
     *
     */
    public function check_for_user_in_grouping($userid) {
        global $DB;

        $sgs = new skills_group_setting($this->courseid);
        $params = array($sgs->get_grouping_id(), $userid);
        $query = "SELECT *
                  FROM {groupings_groups} a inner join {groups_members} b ON a.groupid = b.groupid
                  WHERE a.groupingid = ? AND b.userid = ?";
        $record = $DB->get_record_sql($query, $params, IGNORE_MULTIPLE);
        if ($record !== false) {
            return $record->groupid;
        } else {
            return false;
        }
    }

}