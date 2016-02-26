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
 * Configuration page for setting course start/end dates
 *
 * @package    block
 * @subpackage eclass course management
 * @author     Trevor Jones tdjones@ualberta.ca
 * @author     Joey Andres <jandres@ualberta.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__).'/locallib.php');
require_once(dirname(__FILE__).'/../../config.php');
require_once($CFG->dirroot . '/blocks/eclass_course_management/lib/edit_form.php');

global $CFG, $USER, $DB, $PAGE, $OUTPUT;

// Load data.
$id = required_param('course', PARAM_INT);

// Load course.
$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);
$coursecontext = context_course::instance($course->id);

require_login($course, false);
require_capability('moodle/course:update', $coursecontext);

$PAGE->set_context(context_course::instance($course->id));

$title = get_string('configurationtitle', 'block_eclass_course_management');

$pageurl = new moodle_url('/blocks/eclass_course_management/configure.php', array('course' => $course->id));

// Header.
$PAGE->navbar->add($title);
$PAGE->set_pagelayout('admin');
$PAGE->set_url($pageurl);
$PAGE->set_title($title);
$PAGE->set_heading($title);


$record = $DB->get_record('eclass_course_management', array('courseid' => $course->id));
$startstamp = time();
$endstamp = time() + 3600 * 48;
if ($record) {
    $startstamp = $record->startdate;
    $endstamp = $record->enddate;
}

// Instantiate course_management_form.
$mform =
    new course_management_form($startstamp, $endstamp, $pageurl, array('visibility' => $course->visible), 'post', '',
        array('id' => 'configure_form'));

// Form processing and displaying is done here.
if ($mform->is_cancelled()) {
    // Don't do anything.
    redirect(new moodle_url('/course/view.php', array('id' => $course->id)));
} else if ($fromform = $mform->get_data()) {
    // Acquire the old visibility prior to update/insertion of eclass_course_management row.
    $oldvisibility = intval($course->visible);

    // In this case you process validated data. $mform->get_data() returns data posted in form.
    $today = time();
    $vistoggle = false;
    if ($fromform->start <= $today) {
        // Change visibility.
        $course->visible = 1;
        $vistoggle = true;
    }
    if ($fromform->end < $today) {
        // Change visibility.
        $course->visible = 0;
        $vistoggle = true;
    }
    if ($fromform->start > $today) {
        // Change visibility.
        $course->visible = 0;
        $vistoggle = true;
    }

    if ($vistoggle) {
        $DB->update_record('course', $course);
    }

    if ($record) {
        // Update.
        $oldstartdate = $record->startdate;
        $oldenddate = $record->enddate;
        $newstartdate = $fromform->start;
        $newenddate = $fromform->end;

        $record->startdate = $fromform->start;
        $record->enddate = $fromform->end;
        $record->timemodified = time();
        $record->lastopened = $record->lastclosed = '';
        $DB->update_record('eclass_course_management', $record);

        $event = \block_eclass_course_management\event\course_open_close_date_change::create(array(
            'context' => $PAGE->context,
            'other' => array(
                'oldstartdate' => $oldstartdate,
                'oldenddate' => $oldenddate,
                'newstartdate' => $newstartdate,
                'newenddate' => $newenddate
            )
        ));
        $event->add_record_snapshot('course', $PAGE->course);
        $event->trigger();
    } else {
        // Insert.
        $record = new stdClass();
        $record->courseid = $course->id;
        $record->startdate = $fromform->start;
        $record->enddate = $fromform->end;
        $record->timemodified = time();
        $record->id = $DB->insert_record('eclass_course_management', $record);
    }

    // At this point, the eclass_course_management is updated. Trigger some events if any.
    $newvisibility = intval($course->visible);
    log_visibility_changes($oldvisibility, $newvisibility, $record);

    redirect($pageurl, get_string('successsave', 'block_eclass_course_management'));
}
echo $OUTPUT->header();
$mform->display();

$PAGE->requires->yui_module(
    'moodle-block_eclass_course_management-module',
    'M.blocks_eclasscoursemanagement.init');

echo $OUTPUT->footer();
