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

require_once('../../../config.php');
require_once($CFG->dirroot.'/grade/export/lib.php');
require_once('grade_export_uag.php');
require_once('uag_export_form.php');

$id                = required_param('id', PARAM_INT); // Course id.
$PAGE->set_url('/grade/export/uag/export.php', array('id' => $id));

if (!$course = $DB->get_record('course', array('id' => $id))) {
    print_error('nocourseid');
}

require_login($course);
$context = context_course::instance($id);
$groupid = groups_get_course_group($course, true);

require_capability('moodle/grade:export', $context);
require_capability('gradeexport/uag:view', $context);

// We need to call this method here before any print otherwise the menu won't display.
// If you use this method without this check, will break the direct grade exporting (without publishing).
$key = optional_param('key', '', PARAM_RAW);
if (!empty($CFG->gradepublishing) && !empty($key)) {
    print_grade_page_head($COURSE->id, 'export', 'uag', get_string('exportto', 'grades') . ' ' .
        get_string('pluginname', 'gradeexport_uag'));
}

if (groups_get_course_groupmode($COURSE) == SEPARATEGROUPS and !has_capability('moodle/site:accessallgroups', $context)) {
    if (!groups_is_member($groupid, $USER->id)) {
        print_error('cannotaccessgroup', 'grades');
    }
}

$params = array(
    'includeseparator' => true,
    'publishing' => true,
    'simpleui' => true,
    'multipledisplaytypes' => true
);
$mform = new uag_export_form(null, $params);
$data = $mform->get_data();
$data->itemids["$data->whattograde"] = 1;
if (!$data->gradeboundaryreview) {
    $url = new moodle_url('/grade/export/uag/index.php', array('id' => $course->id, 'boundaryreview' => 1));
    redirect($url);
}


$export = new grade_export_uag($course, $groupid, $data);
// If the gradepublishing is enabled and user key is selected print the grade publishing link.
if (!empty($CFG->gradepublishing) && !empty($key)) {
    groups_print_course_menu($course, 'index.php?id='.$id);
    echo $export->get_grade_publishing_url();
    echo $OUTPUT->footer();
} else {
    $export->print_grades();
}
