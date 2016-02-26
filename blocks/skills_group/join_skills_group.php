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
 * From what I recall, the /group/index.php checks the "course:managegroups" capability, which
 * a student would not have.  I think the call to index.php is in /group/clientlib.js in the
 * function starting on line 144.  The ajax bit starts on line 187.
 *
 * I think I would have to override a role's capability in the block for it to work, which is
 * not what one would expect.  I'm worried it might cause hard-to-track bugs in the future.
 *
 * NOTE: this file simply draws the UI and sets up the javascript.  The joining and such is handled
 * in AJAX, so the calls to moodle logging functions are contained there instead.
 *
 * @package    block_skills_group
 * @category   block
 * @copyright  2014 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG;
require_once($CFG->dirroot.'/blocks/skills_group/locallib.php');
require_once($CFG->dirroot.'/blocks/skills_group/classes/skills_grouping.class.php');
require_once($CFG->dirroot.'/blocks/skills_group/classes/skills_group_setting.class.php');
require_once($CFG->dirroot.'/local/yuigallerylibs/module_info.php');

global $PAGE, $OUTPUT, $USER;

$courseid = required_param('courseid', PARAM_INT);
if (!blocks_skills_group_verify_access('block/skills_group:cancreateorjoinskillsgroups', true)) {
    redirect(new moodle_url('/course/view.php', array('id' => $courseid)));
}
$url = new moodle_url('/blocks/skills_group/join_skills_group.php', array('courseid' => $courseid, 'sesskey' => $USER->sesskey));
block_skills_group_setup_page($courseid, $url, get_string('joingroup', BLOCK_SG_LANG_TABLE), 'base');

$error = null;
$groupingid = 0;
$sgs = new skills_group_setting($courseid);
// In case user tries to manually access page - check that settings exist.
if (!$sgs->exists()) {
    $error = get_string('notconfigured', BLOCK_SG_LANG_TABLE);
} else if ($sgs->date_restriction() && time() > $sgs->get_date()) {
    $error = get_string('dateexpired', BLOCK_SG_LANG_TABLE);
} else {
    $groupingid = $sgs->get_grouping_id();
    $sgrouping = new skills_grouping($courseid);
    // If user is in group - display error.
    if ($sgrouping->check_for_user_in_grouping($USER->id) !== false) {
        $error = get_string('alreadyingroup', BLOCK_SG_LANG_TABLE);
    }
}

set_header();
echo $OUTPUT->header();
display_header();
display_group_view();
display_buttons();
load_yui_modules($courseid, $groupingid, $error);
echo $OUTPUT->footer();

/**
 * This function set the page header -> JS/CSS includes.
 *
 */
function set_header() {
    global $PAGE;

    $PAGE->requires->css('/blocks/skills_group/css/skills_group.css');
    $PAGE->requires->js_module(get_js_module_info('gallery-datatable-selection'));
    $PAGE->requires->js_module(get_js_module_info('gallery-datatable-paginator'));
    $PAGE->requires->js_module(get_js_module_info('gallery-paginator-view'));
    $PAGE->requires->strings_for_js(array('groupsloading', 'emptygroups', 'groupsloaderror',
            'groupjoinsuccess', 'groupjoinerror'), BLOCK_SG_LANG_TABLE);
}

/**
 * This function draws the header and the status text area.
 *
 */
function display_header() {
    echo html_writer::nonempty_tag('h2', get_string('joingroup', BLOCK_SG_LANG_TABLE));
    echo html_writer::div('', '', array('id' => 'statustext'));
}

/**
 * This function sets up the skeleton for the datatable that lists all groups
 *
 */
function display_group_view() {

    echo html_writer::start_div('', array('id' => 'groups'));
    echo html_writer::div('', '', array('id' => 'availablegroups'));
    echo html_writer::div('', 'yui3-pagview-bar', array('id' => 'groupspaginator'));
    echo '<script type="text/x-template" id="paginator-template">
                <button data-pglink="first" class="{pageLinkClass}" title="First Page">First</button>
                <button data-pglink="prev" class="{pageLinkClass}" title="Prior Page">Prev</button>
                {pageLinks}
                <button data-pglink="next" class="{pageLinkClass}" title="Next Page">Next</button>
                <button data-pglink="last" class="{pageLinkClass}" title="Last Page">Last</button>
            </script>';
    echo html_writer::end_div();
}

/**
 * Draw the submit button and return to course button.  I have styled these buttons
 * according to the typical buttons at the bottom of a moodle form.
 *
 */
function display_buttons() {
    echo html_writer::start_div('', array('id' => 'fgroup_id_buttonar'));
    echo html_writer::empty_tag('input', array('type' => 'submit', 'id' => 'id_submitbutton',
                                'value' => get_string('joingroupbutton', BLOCK_SG_LANG_TABLE)));
    echo html_writer::empty_tag('input', array('type' => 'submit', 'id' => 'refresh',
                                'value' => get_string('refreshgroupsbutton', BLOCK_SG_LANG_TABLE)));
    echo html_writer::empty_tag('input', array('type' => 'submit', 'id' => 'return',
                                'value' => get_string('returnbutton', BLOCK_SG_LANG_TABLE)));
    echo html_writer::end_div();
}

/**
 * This function loads the YUI modules that I have written.  I've elected to
 * load these last since that is generally safest.
 *
 * @param int $courseid The ID of the course being used.
 * @param int $groupingid The ID of the grouping being used (needed for JS).
 * @param string $error Text description of error that has occurred.
 *
 */
function load_yui_modules($courseid, $groupingid = 0, $error = null) {
    global $PAGE;

    $PAGE->requires->yui_module('moodle-block_skills_group-join', 'M.block_skills_group.init_join',
                                array(array('courseid' => $courseid, 'groupingid' => $groupingid, 'errorstring' => $error)));
}