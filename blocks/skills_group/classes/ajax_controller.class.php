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
require_once($CFG->dirroot.'/blocks/skills_group/classes/skills_grouping.class.php');
require_once($CFG->dirroot.'/blocks/skills_group/classes/skills_group_setting.class.php');
require_once($CFG->dirroot.'/blocks/skills_group/classes/group_records.class.php');

/**
 * This is the main controller class that handles all of the ajax requests.
 *
 * @package    block_skills_group
 * @copyright  2014 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ajax_controller {

    /** Hold the courseid */
    private $courseid;

    /**
     * Empty constructor for the time being.
     *
     */
    public function __construct() {
    }

    /**
     * This is function verifies that the user has basic access to this page.  More detailed checks
     * may be performed later depending on the action.
     *
     * @param int $requesttype The type of the ajax request.
     *
     */
    public function verify_access($requesttype) {
        $this->courseid = required_param('courseid', PARAM_INT);
        // Require users to be logged in, but do not redirect to login page -> we'll tell the user manually.
        try {
            require_login($this->courseid, false, null, false, true);
        } catch (Exception $e) {
            echo(json_encode(array('result' => 'false', 'text' => get_string('nologin', BLOCK_SG_LANG_TABLE))));
            return false;
        }
        if (!confirm_sesskey(required_param("sesskey", PARAM_TEXT))) {
            echo(json_encode(array('result' => 'false', 'text' => get_string('badsesskey', BLOCK_SG_LANG_TABLE))));
            return false;
        }
        return true;
    }

    /**
     * This is function dispatches the request based on its type.
     *
     * @param int $requesttype The type of the ajax request.
     *
     */
    public function perform_request($requesttype) {
        switch ($requesttype) {
            case 'add_members':
                $this->add_members();
                break;
            case 'get_group_stats':
                $this->get_group_stats();
                break;
            case 'join_group':
                $this->join_group();
                break;
        }
    }

    /**
     * This is function adds members to a particular group.
     *
     */
    private function add_members() {
        global $DB, $USER;

        $groupid = required_param('groupid', PARAM_INT);
        $members = required_param('members', PARAM_TEXT);
        $allowjoin = required_param('allowjoin', PARAM_TEXT);
        $this->courseid = required_param('courseid', PARAM_INT);

        // Update allowjoin flag.
        $sgroup = new skills_group($groupid);
        // The boolean encoding with json gets messed up -> so check for the string version here.
        if ($allowjoin == 'true') {
            $sgroup->set_allow_others_to_join(true);
        } else {
            $sgroup->set_allow_others_to_join(false);
        }

        $decodedmembers = json_decode($members, true);
        $decodedmembers = is_array($decodedmembers) ? array_unique($decodedmembers) : array();
        // Add the list of IDs of locked members to the list of individuals to add to the group.
        // TODO: would be nice if this was part of the skills_group class?
        $lockedmembers = $sgroup->get_members_list($lock = true);
        foreach ($lockedmembers as $key => $lockedmember) {
            $decodedmembers[] = $key;
        }

        $sgsetting = new skills_group_setting($this->courseid);
        // Wipe out old group.
        $DB->delete_records('groups_members', array('groupid' => $groupid));
        // Add self.
        groups_add_member($groupid, $USER->id);
        // Logging edit group action.
        $params = array(
            'context' => context_course::instance($this->courseid),
            'objectid' => $groupid,
            'courseid' => $this->courseid,
            'userid' => $USER->id
            );
        $event = \block_skills_group\event\skillsgroup_joined::create($params);
        $event->trigger();

        if (count($decodedmembers) <= $sgsetting->get_group_size() - 1) {
            foreach ($decodedmembers as $dm) {
                groups_add_member($groupid, $dm);
            }
            echo(json_encode(array('result' => 'true', 'text' => get_string('groupupdatesuccess', BLOCK_SG_LANG_TABLE))));
        } else {
            echo(json_encode(array('result' => 'false', 'text' => get_string('toomanymembers', BLOCK_SG_LANG_TABLE))));
        }
    }

    /**
     * This is function grabs the records from a particular folder, encodes them in json, and writes
     * the records to the page.
     *
     */
    private function get_group_stats() {
        global $USER;

        $this->courseid = required_param('courseid', PARAM_INT);
        $gr = new group_records($this->courseid);

        $tablerows = $gr->get_table_rows();
        $skillslist = $gr->get_skills_list();
        $table = array('skills' => $skillslist, 'rows' => $tablerows);
        echo json_encode($table);
    }

    /**
     * This function allows the user to join a group if they are not already part
     * of a group.
     *
     */
    private function join_group() {
        global $USER;

        $groupid = required_param('groupid', PARAM_INT);
        $groupingid = required_param('groupingid', PARAM_INT);
        $this->courseid = required_param('courseid', PARAM_INT);

        $sgs = new skills_group_setting($this->courseid);
        $sgrouping = new skills_grouping($this->courseid);
        $sgroup = new skills_group($groupid);

        if (($sgroup->count_members() < $sgs->get_group_size()) && ($sgroup->get_allow_others_to_join() === true)) {
            if ($sgrouping->check_for_user_in_grouping($USER->id) === false) {
                groups_add_member($groupid, $USER->id);
                // Logging join group action.
                $params = array(
                    'context' => context_course::instance($this->courseid),
                    'objectid' => $groupid,
                    'courseid' => $this->courseid,
                    'userid' => $USER->id
                    );
                $event = \block_skills_group\event\skillsgroup_joined::create($params);
                $event->trigger();
                echo(json_encode(array('result' => 'true', 'text' => get_string('groupjoinsuccess', BLOCK_SG_LANG_TABLE))));
            } else {
                echo(json_encode(array('result' => 'false', 'text' => get_string('alreadyingroup', BLOCK_SG_LANG_TABLE))));
            }
        } else {
                echo(json_encode(array('result' => 'false', 'text' => get_string('toomanymembers', BLOCK_SG_LANG_TABLE))));
        }
    }
}