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
 * This file handles displays to the user their group results.
 *
 * @package    block_skills_group
 * @category   block
 * @copyright  2014 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG;
require_once($CFG->dirroot.'/user/lib.php');
require_once($CFG->dirroot.'/blocks/skills_group/locallib.php');
require_once($CFG->dirroot.'/blocks/skills_group/classes/skills_grouping.class.php');
require_once($CFG->dirroot.'/blocks/skills_group/classes/skills_group_setting.class.php');
require_once($CFG->dirroot.'/blocks/skills_group/classes/group_records.class.php');

global $PAGE, $OUTPUT, $USER;

$courseid = required_param('courseid', PARAM_INT);
if (!blocks_skills_group_verify_access('block/skills_group:cancreateorjoinskillsgroups', true)) {
    redirect(new moodle_url('/course/view.php', array('id' => $courseid)));
}
$url = new moodle_url('/blocks/skills_group/view_group.php', array('courseid' => $courseid, 'sesskey' => $USER->sesskey));
block_skills_group_setup_page($courseid, $url, get_string('viewskillsgroup', BLOCK_SG_LANG_TABLE));

$error = null;
$groupingid = 0;
$sgs = new skills_group_setting($courseid);
// In case user tries to manually access page - check that settings exist.
if (!$sgs->exists()) {
    $error = get_string('notconfigured', BLOCK_SG_LANG_TABLE);
} else {
    $groupingid = $sgs->get_grouping_id();
    $sgrouping = new skills_grouping($courseid);
    $groupid = $sgrouping->check_for_user_in_grouping($USER->id);
    // If user has - display error.
    if ($groupid === false) {
        $error = get_string('nogrouperror', BLOCK_SG_LANG_TABLE);
    }
}

set_header();
echo $OUTPUT->header();
if ($error != null) {
    echo html_writer::nonempty_tag('h2', $error);
} else {
    if ($sgs->get_feedback_id() == 0) {
        display_members($groupid);
    } else {
        display_group_stats($groupid);
    }
}
echo $OUTPUT->footer();

/**
 * This function set the page header -> JS/CSS includes.
 *
 */
function set_header() {
    global $PAGE;

    $PAGE->requires->css('/blocks/skills_group/css/skills_group.css');
}

/**
 * This function draws the group members only (no stats).
 *
 * @param int $groupid The ID of the group to draw on the screen
 */
function display_members($groupid) {
    global $DB;

    $courseid = required_param('courseid', PARAM_INT);
    $members = $DB->get_records('groups_members', array('groupid' => $groupid));
    echo html_writer::nonempty_tag('h2', get_string('groupmembers', BLOCK_SG_LANG_TABLE));

    $usernames = array();
    foreach ($members as $member) {
        $user = user_get_users_by_id(array($member->userid));
        $usernames[] = $user[$member->userid]->firstname . ' ' . $user[$member->userid]->lastname;
    }
    echo html_writer::alist($usernames);
}

/**
 * This function draws the group stats table.
 *
 */
function display_group_stats($groupid) {
    global $DB;

    $courseid = required_param('courseid', PARAM_INT);
    $members = $DB->get_records('groups_members', array('groupid' => $groupid));
    $fullscores = array();
    foreach ($members as $member) {
        $sgs = new skills_group_student($courseid, $member->userid);
        $fullscores[] = $sgs->get_scores();
    }

    $gr = new group_records($courseid);
    $skillslist = $gr->get_skills_list(true, 'multichoice');
    $labellist = $gr->get_skills_list(true, 'label');

    echo html_writer::start_div('yui3-skin-sam', array('id' => 'viewgroup'));
    echo html_writer::start_tag('table', array('class' => 'yui3-datatable-table'));
    draw_table_header($members);
    draw_table_results($members, $skillslist, $labellist, $fullscores, $courseid);
    echo html_writer::end_tag('table');
    echo html_writer::end_div();
}

/**
 * This function draws the table header - the student's names.
 *
 * @param object $members List of group members
 *
 */
function draw_table_header($members) {

    echo html_writer::start_tag('thead', array('class' => 'yui3-datatable-columns'));
    echo html_writer::start_tag('tr');
    echo html_writer::nonempty_tag('th', get_string('skillheader', BLOCK_SG_LANG_TABLE),
                                   array('class' => 'yui3-datatable-header'));
    foreach ($members as $member) {
        $user = user_get_users_by_id(array($member->userid));
        $username = $user[$member->userid]->firstname . ' ' . $user[$member->userid]->lastname;
        echo html_writer::nonempty_tag('th', $username, array('class' => 'yui3-datatable-header'));
    }
    echo html_writer::nonempty_tag('th', get_string('skillcount', BLOCK_SG_LANG_TABLE),
                                   array('class' => 'yui3-datatable-header'));
    echo html_writer::end_tag('tr');
    echo html_writer::end_tag('thead');
}

/**
 * This function draws the main table contents - skills -> {W, S} depending on
 * the value.
 *
 * @param object $members List of group members
 * @param array $skillslist Array containing the names of the skills in the list
 * @param array $labellist Array containing labels in the survey, so they can be rendered as titles
 * @param 2darray $fullscores Scores, index1 -> student, index2 -> skill
 * @param int $courseid The ID for the course we're currently in
 *
 */
function draw_table_results($members, $skillslist, $labellist, $fullscores, $courseid) {

    $fulllist = $skillslist + $labellist;
    ksort($fulllist);
    $labelindices = array_keys($labellist);
    $threshold = (new skills_group_setting($courseid))->get_threshold();

    foreach ($fulllist as $position => $name) {
        echo html_writer::start_tag('tr');
        // Label take up the entire column width.
        if (array_search($position, $labelindices) !== false) {
            echo html_writer::nonempty_tag('td', $name, array('class' => 'yui3-datatable-cell',
                                           'colspan' => count($members) + 2));
        } else {
            // Non-labels are presented as results across the table.
            echo html_writer::nonempty_tag('td', $name, array('class' => 'yui3-datatable-cell'));
            $totalstrong = 0;
            for ($i = 0; $i < count($members); $i++) {
                if ($fullscores[$i][$position] > $threshold) {
                    $skillvalue = 'S';
                    $totalstrong++;
                } else if ($fullscores[$i][$position] === null) {
                    $skillvalue = get_string('incomplete', BLOCK_SG_LANG_TABLE);
                } else {
                    $skillvalue = 'W';
                }
                echo html_writer::nonempty_tag('td', $skillvalue, array('class' => 'yui3-datatable-cell tdcenter'));
            }
            echo html_writer::nonempty_tag('td', $totalstrong, array('class' => 'yui3-datatable-cell tdcenter'));
        }
        echo html_writer::end_tag('tr');
    }
}
