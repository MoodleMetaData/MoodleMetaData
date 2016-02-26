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


/**
 * This is a very simple intermediate page where the user can make changes
 * to their group.  If they are currently not part of a group, they are given
 * the option to create one.  If they already belong to a group, they can choose
 * to edit that group, or remove themselves from that group.
 *
 * @package    block_skills_group
 * @category   block
 * @copyright  2014 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__).'/../../config.php');
global $CFG;
require_once($CFG->dirroot.'/blocks/skills_group/locallib.php');
require_once($CFG->dirroot.'/blocks/skills_group/classes/create_skills_group_form.class.php');
require_once($CFG->dirroot.'/blocks/skills_group/classes/skills_grouping.class.php');
require_once($CFG->dirroot.'/blocks/skills_group/classes/skills_group.class.php');
require_once($CFG->dirroot.'/group/lib.php');

global $OUTPUT, $PAGE, $USER;

$courseid = required_param('courseid', PARAM_INT);
if (!blocks_skills_group_verify_access('block/skills_group:cancreateorjoinskillsgroups', true)) {
    redirect(new moodle_url('/course/view.php', array('id' => $courseid)));
}
$url = new moodle_url('/blocks/skills_group/create_skills_group.php', array('courseid' => $courseid, 'sesskey' => $USER->sesskey));
block_skills_group_setup_page($courseid, $url, get_string('creategrouptitle', BLOCK_SG_LANG_TABLE));

$creategroupform = new create_skills_group_form($courseid);
$sgrouping = new skills_grouping($courseid);
$groupid = $sgrouping->check_for_user_in_grouping($USER->id);
if ($groupid !== false) {
    $sgroup = new skills_group($groupid);
    if ($sgroup->get_allow_others_to_join() === true) {
        $toform['allowjoincheck'] = 1;
    }
}
$toform['courseid'] = $courseid;
$creategroupform->set_data($toform);

if ($creategroupform->is_cancelled()) {
    $courseurl = new moodle_url('/course/view.php', array('id' => $courseid));
    redirect($courseurl);
} else if ($fromform = $creategroupform->get_data()) {
    $url = process_form($courseid, $fromform);
    redirect($url);
} else {
    $site = get_site();
    echo $OUTPUT->header();
    $creategroupform->display();
    echo $OUTPUT->footer();
}

/**
 * This function determines the user's desired course of action -> {create, edit, drop}
 * and processes it accordingly.
 *
 * @param int $courseid The ID of the course.
 * @param object $submittedform The object contains the results of the form when changes were saved.
 * @return string|boolean The url to redirect the user to or false to prevent redirect.
 *
 */
function process_form($courseid, &$submittedform) {
    global $DB, $USER;

    $sgs = new skills_group_setting($courseid);
    if ($sgs->date_restriction() && time() > $sgs->get_date()) {
        // Process no data and redirect back to same form.  Form will draw expired version to alert user.
        $url = new moodle_url('/blocks/skills_group/create_skills_group.php', array('courseid' => $courseid,
                              'sesskey' => $USER->sesskey));
        return $url;
    }

    if ($submittedform->type == 'create') {
        if ($submittedform->creategroupcheck) {
            $sgrouping = new skills_grouping($courseid);
            // Blank names are OK -> plugin will autoname.
            $groupname = (isset($submittedform->creategroup)) ? $submittedform->creategroup : null;
            $groupid = $sgrouping->create_group($groupname);
            update_allow_join($groupid, $submittedform->allowjoincheck);
            $url = new moodle_url('/blocks/skills_group/edit_skills_group.php', array('courseid' => $courseid,
                                  'groupid' => $groupid, 'sesskey' => $USER->sesskey));
            // Logging create group action.
            $params = array(
                'context' => context_course::instance($courseid),
                'objectid' => $groupid,
                'courseid' => $courseid,
                'userid' => $USER->id
                );
            $event = \block_skills_group\event\skillsgroup_left::create($params);
            $event->trigger();
        } else {
            $url = new moodle_url('/course/view.php', array('id' => $courseid));
        }
    } else if ($submittedform->type == 'edit') {
        if (isset($submittedform->leavegroup)) {
            if ($submittedform->leavegroup) {
                groups_remove_member($submittedform->groupid, $USER->id);
                // Logging leave group action.
                $params = array(
                    'context' => context_course::instance($courseid),
                    'objectid' => $submittedform->groupid,
                    'courseid' => $courseid,
                    'userid' => $USER->id
                    );
                $event = \block_skills_group\event\skillsgroup_left::create($params);
                $event->trigger();
                $url = new moodle_url('/course/view.php', array('id' => $courseid));
                return $url;
            }
        }
        $groupid = $submittedform->groupid;
        update_allow_join($groupid, $submittedform->allowjoincheck);
        if ($submittedform->editmembers) {
            $url = new moodle_url('/blocks/skills_group/edit_skills_group.php', array('courseid' => $courseid,
                                  'groupid' => $groupid, 'sesskey' => $USER->sesskey));
        } else {
            $url = new moodle_url('/course/view.php', array('id' => $courseid));
        }
    } else {
        $url = new moodle_url('/course/view.php', array('id' => $courseid));
    }
    return $url;
}

/**
 * Small helper function that parses the {0, 1} return from the advanced checkbox
 * and passes it to the skills_group class to be updated.
 *
 * @param int $groupid The ID of the group to update
 * @param int $allowjoin {0, 1} indicating status of allowjoin flag
 *
 */
function update_allow_join($groupid, $allowjoin) {

    $sgroup = new skills_group($groupid);
    if ($allowjoin == 1) {
        $sgroup->set_allow_others_to_join(true);
    } else {
        $sgroup->set_allow_others_to_join(false);
    }
}
