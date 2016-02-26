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
 * This file handles the editing of group members.  I had hoped to use the built-in one,
 * but it would be difficult without changing capabilities.  This draws a very simple
 * javascript-based form for the user: {a multi-member selector, checkbox, submit button}
 *
 * @package    block_skills_group
 * @category   block
 * @copyright  2014 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG;
require_once($CFG->dirroot.'/blocks/skills_group/locallib.php');
require_once($CFG->dirroot.'/blocks/skills_group/classes/skills_group.class.php');
require_once($CFG->dirroot.'/blocks/skills_group/classes/skills_grouping.class.php');
require_once($CFG->dirroot.'/blocks/skills_group/classes/skills_group_setting.class.php');
require_once($CFG->dirroot.'/local/yuigallerylibs/module_info.php');
global $USER, $PAGE, $OUTPUT;

$courseid = required_param('courseid', PARAM_INT);
if (!blocks_skills_group_verify_access('block/skills_group:cancreateorjoinskillsgroups', true)) {
    redirect(new moodle_url('/course/view.php', array('id' => $courseid)));
}
$groupid = required_param('groupid', PARAM_INT);
$url = new moodle_url('/blocks/skills_group/edit_skills_group.php', array('courseid' => $courseid, 'groupid' => $groupid,
                      'sesskey' => $USER->sesskey));
block_skills_group_setup_page($courseid, $url, get_string('adduserstogroup', BLOCK_SG_LANG_TABLE));

$error = null;
$sgs = new skills_group_setting($courseid);
// In case user tries to manually access page - check that settings exist.
if ($sgs->exists()) {
    $sgroup = new skills_group($groupid);
} else {
    $error = get_string('notconfigured', BLOCK_SG_LANG_TABLE);
}

set_header();
echo $OUTPUT->header();
display_header();
if ($error == null) {
    if ($sgroup->user_in_group($USER->id)) {
        display_group_selector();
        display_locked_students($courseid, $groupid);
        display_settings($groupid);
    } else {
        $error = get_string('notingroup', BLOCK_SG_LANG_TABLE);
    }
}
display_buttons();
load_yui_modules($courseid, $groupid, $error);
echo $OUTPUT->footer();

/**
 * This function set the page header -> JS/CSS includes.
 *
 */
function set_header() {
    global $PAGE;

    $PAGE->requires->css('/local/yuigallerylibs/gallery-multivalue-input/assets/skins/sam/gallery-multivalue-input.css');
    $PAGE->requires->css('/blocks/skills_group/css/skills_group.css');
    $PAGE->requires->js_module(get_js_module_info('gallery-multivalue-input'));
    $PAGE->requires->strings_for_js(array('groupplaceholder', 'nomembers', 'groupupdatesuccess',
            'groupupdateerror', 'toomanymembers'), BLOCK_SG_LANG_TABLE);
}

/**
 * This function draws the header and the status text area.
 *
 */
function display_header() {
    echo html_writer::nonempty_tag('h2', get_string('adduserstogroup', BLOCK_SG_LANG_TABLE));
    echo html_writer::div('', '', array('id' => 'statustext'));
}

/**
 * This function displays a list of members that have locked in.
 *
 * @param int $courseid The ID of the course
 * @param int $groupid The ID of the group that the user is in
 *
 */
function display_locked_students($courseid, $groupid) {

    echo html_writer::start_div('locked_members_bar');
    $sgroup = new skills_group($groupid);
    $lockedstudents = $sgroup->get_members_list(true);
    if (count($lockedstudents) > 0) {
        echo html_writer::span(get_string('lockedmembers', BLOCK_SG_LANG_TABLE), 'label');
        $firstitem = true;
        foreach ($lockedstudents as $lockedstudent) {
            if ($firstitem = false) {
                echo ', ';
            } else {
                $firstitem = false;
            }
            echo html_writer::span($lockedstudent, 'name');
        }
    }
    echo html_writer::div('', 'clear');
    echo html_writer::end_div();
}

/**
 * This function is responsible for group members selector and also writing out
 * a list of members that have already locked in.
 *
 */
function display_group_selector() {

    echo html_writer::start_div('members_bar');
    echo html_writer::span(get_string('groupmembers', BLOCK_SG_LANG_TABLE), 'label');
    echo html_writer::empty_tag('input', array('type' => 'text', 'id' => 'groupmembers', 'placeholder' =>
                                get_string('groupplaceholder', BLOCK_SG_LANG_TABLE)));
    echo html_writer::div('', 'clear');
    echo html_writer::end_div();
}

/**
 * This function draws any additional settings.  Currently there is only one:
 * whether the user wishes to let others join.
 *
 * @param int $groupid The ID of the group that the user belongs to.
 *
 */
function display_settings($groupid) {

    $sgroup = new skills_group($groupid);

    echo html_writer::start_div('', array('id' => 'allowuserstojoinsetting'));
    $attributes = array('id' => 'allowuserstojoin', 'type' => 'checkbox');
    if ($sgroup->get_allow_others_to_join()) {
        $attributes['checked'] = 'checked';
    }
    echo html_writer::empty_tag('input', $attributes);
    echo get_string('allowuserstojoin', BLOCK_SG_LANG_TABLE);
    echo html_writer::end_div();
    echo html_writer::empty_tag('br');
}

/**
 * Draw the submit button and return to course button.  I have styled these buttons
 * according to the typical buttons at the bottom of a moodle form.
 *
 */
function display_buttons() {
    echo html_writer::start_div('', array('id' => 'fgroup_id_buttonar'));
    echo html_writer::empty_tag('input', array('type' => 'submit', 'id' => 'id_submitbutton',
                                'value' => get_string('submitbutton', BLOCK_SG_LANG_TABLE)));
    echo html_writer::empty_tag('input', array('type' => 'submit', 'id' => 'return',
                                'value' => get_string('returnbutton', BLOCK_SG_LANG_TABLE)));
    echo html_writer::end_div();
}

/**
 * This function loads the YUI modules that I have written.  I've elected to
 * load these last since that is generally safest.
 *
 * @param int $courseid The ID of the course being used.
 * @param int $groupid The ID of the group that the user belongs to.
 *
 */
function load_yui_modules($courseid, $groupid, $error = null) {
    global $PAGE;

    $params = array('courseid' => $courseid, 'groupid' => $groupid, 'errorstring' => $error);
    if ($error == null) {
        $sgsetting = new skills_group_setting($courseid);
        $sgrouping = new skills_grouping($courseid);
        $sgroup = new skills_group($groupid);
        // True max group size is: max group size - # of locked members.
        $params['maxgroupsize'] = $sgsetting->get_group_size() - count($sgroup->get_members_list(true));
        $potentialstudents = $sgrouping->get_potential_students();
        $params['availableids'] = array_keys($potentialstudents);
        $params['availablenames'] = array_values($potentialstudents);
        $unlockedstudents = $sgroup->get_members_list(false);
        $params['groupmemberids'] = array_keys($unlockedstudents);
        $params['groupmembernames'] = array_values($unlockedstudents);
    }
    $PAGE->requires->yui_module('moodle-block_skills_group-edit', 'M.block_skills_group.init_edit', array($params));
}