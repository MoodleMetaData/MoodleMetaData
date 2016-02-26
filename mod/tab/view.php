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
 * This file is responsible for displaying the TAB
 **/

require("../../config.php");
require_once("lib.php");
require_once("locallib.php");
require_once($CFG->dirroot . '/lib/resourcelib.php');
require_once($CFG->dirroot . '/lib/completionlib.php');

$id = optional_param('id', 0, PARAM_INT); // Course Module ID, or
$a = optional_param('a', 0, PARAM_INT); // tab ID.

if ($id) {
    if (!$cm = get_coursemodule_from_id("tab", $id)) {
        print_error("Course Module ID was incorrect");
    }

    if (!$tab = $DB->get_record("tab", array("id" => $cm->instance))) {
        print_error("Course module is incorrect");
    }

} else {
    if (!$tab = $DB->get_record("tab", array("id" => $a))) {
        print_error("Course module is incorrect");
    }

    if (!$cm = get_coursemodule_from_instance("tab", $tab->id, $course->id)) {
        print_error("Course Module ID was incorrect");
    }
}

$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
$coursecontext = context_course::instance($course->id);

require_capability('mod/tab:view', $context);
\mod_tab\event\course_module_viewed::create_from_tab($tab, $context)->trigger();

// Update 'viewed' state if required by completion system.
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

// Print the page header.

$PAGE->set_url('/mod/tab/view.php', array('id' => $cm->id));
$PAGE->set_title($tab->name);
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_activity_record($tab);

$PAGE->requires->strings_for_js(array(
    'updatethis',
    'edittabmoduletooltip'
), 'mod_tab');

// Gather javascripts and css.
$PAGE->requires->js('/mod/tab/js/tab/public/js/main.min.js');
$PAGE->requires->css('/mod/tab/js/tab/public/css/main.min.css');
echo $OUTPUT->header();


if (!!$cm->name) {
    $modheading = $OUTPUT->heading(format_string($cm->name), 2);
} else {
    $modheading = $OUTPUT->heading(get_string('modulename', "mod_tab"));
}

/*
 * Hide if display menu is set, since redundant.
 */
if (!$tab->displaymenu) {
    echo $modheading;
}

echo <<<HTML
<div id="mod-tab-content" ng-app="M.mod_tab.TabApp">
  <div ng-view class="no-side-padding container-fluid"></div>
</div>
HTML;

echo $OUTPUT->footer();

