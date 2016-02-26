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
 * Prints a particular instance of iclickerregistration
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_iclickerregistration
 * @copyright  2015 Joey Andres <jandres@ualberta.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Replace iclickerregistration with the name of your module and remove this line.

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = optional_param('id', 0, PARAM_INT); // Course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // ... iclickerregistration instance ID - it should be named as the
                                          // first character of the module.

if ($id) {
    $cm = get_coursemodule_from_id('iclickerregistration', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $iclickerregistration  = $DB->get_record('iclickerregistration', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($n) {
    $iclickerregistration  = $DB->get_record('iclickerregistration', array('id' => $n), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $iclickerregistration->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('iclickerregistration', $iclickerregistration->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);

$event = \mod_iclickerregistration\event\course_module_viewed::create(array(
    'objectid' => $PAGE->cm->instance,
    'context' => $PAGE->context,
));
$event->add_record_snapshot('course', $PAGE->course);
$event->add_record_snapshot($PAGE->cm->modname, $iclickerregistration);
$event->trigger();

// Print the page header.

$PAGE->set_url('/mod/iclickerregistration/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($iclickerregistration->name));
$PAGE->set_heading(format_string($course->fullname));

/*
 * Other things you may want to set - remove if not needed.
 * $PAGE->set_cacheable(false);
 * $PAGE->set_focuscontrol('some-html-id');
 * $PAGE->add_body_class('iclickerregistration-'.$somevar);
 */

// Output starts here.
$PAGE->requires->js('/mod/iclickerregistration/js/iclickerregistration/public/js/main.min.js');
$PAGE->requires->css('/mod/iclickerregistration/js/iclickerregistration/public/css/main.min.css');
echo $OUTPUT->header();
$baseurl = $PAGE->url->get_path();
$registerurl = $CFG->wwwroot.'/mod/iclickerregistration/register.php';
$querystring = $PAGE->url->get_query_string();
$cfg = array(
    "cmid" => $cm->id,

    "base_url" => "$baseurl",
    "register_url" => "$registerurl",
    "query_string" => "$querystring",
);

$PAGE->requires->string_for_js('editbuttontext', "mod_iclickerregistration");
$PAGE->requires->string_for_js('updateinputplaceholdertext', "mod_iclickerregistration");
$PAGE->requires->string_for_js('updatebuttontext', "mod_iclickerregistration");
$PAGE->requires->string_for_js('cancelbuttontext', "mod_iclickerregistration");
$PAGE->requires->string_for_js('instructionheadingtext', "mod_iclickerregistration");
$PAGE->requires->string_for_js('instructiontext', "mod_iclickerregistration");
$PAGE->requires->string_for_js('registrationinputplaceholdertext', "mod_iclickerregistration");
$PAGE->requires->string_for_js('registrationbuttontext', "mod_iclickerregistration");
$PAGE->requires->string_for_js('youriclickeridis', "mod_iclickerregistration");
$PAGE->requires->string_for_js('noiclickeridregistered', "mod_iclickerregistration");
$PAGE->requires->string_for_js('noregisterediclickerprompt', "mod_iclickerregistration");
$PAGE->requires->string_for_js('orderby', "mod_iclickerregistration");
$PAGE->requires->string_for_js('idnumber', "mod_iclickerregistration");
$PAGE->requires->string_for_js('name', "mod_iclickerregistration");
$PAGE->requires->string_for_js('iclickerid', "mod_iclickerregistration");
$PAGE->requires->string_for_js('hideunregistered', "mod_iclickerregistration");
$PAGE->requires->string_for_js('paginationnext', "mod_iclickerregistration");
$PAGE->requires->string_for_js('paginationprevious', "mod_iclickerregistration");
$PAGE->requires->string_for_js('paginationfirst', "mod_iclickerregistration");
$PAGE->requires->string_for_js('paginationlast', "mod_iclickerregistration");
$PAGE->requires->string_for_js('operations', "mod_iclickerregistration");
$PAGE->requires->string_for_js('deletebuttontext', "mod_iclickerregistration");
$PAGE->requires->string_for_js('unregistered', "mod_iclickerregistration");
$PAGE->requires->string_for_js('accessdeniedheadertext', "mod_iclickerregistration");
$PAGE->requires->string_for_js('accessdeniedmessage', "mod_iclickerregistration");
$PAGE->requires->string_for_js('registrationsuccess', "mod_iclickerregistration");
$PAGE->requires->string_for_js('duplicateiclickeridinsamecourse', "mod_iclickerregistration");
$PAGE->requires->string_for_js('duplicateincourselabelglobal', "mod_iclickerregistration");
$PAGE->requires->string_for_js('duplicateincourselabelcourse', "mod_iclickerregistration");
$PAGE->requires->string_for_js('enrolledusersiclickerinfo', "mod_iclickerregistration");
$PAGE->requires->string_for_js('courseconflictlistheader', "mod_iclickerregistration");
$PAGE->requires->string_for_js('youraccountismanuallyenrolled', "mod_iclickerregistration");
$PAGE->requires->string_for_js('invalidiclickerid', "mod_iclickerregistration");
$PAGE->requires->string_for_js('iclickerlegendheadertext', "mod_iclickerregistration");
$PAGE->requires->string_for_js('iclickeridconflictlegendtext', "mod_iclickerregistration");
$PAGE->requires->string_for_js('searchplaceholder', "mod_iclickerregistration");
$PAGE->requires->string_for_js('unregisterbuttontext', "mod_iclickerregistration");
$PAGE->requires->string_for_js('deleteiclickerconfirmationdialogheader', "mod_iclickerregistration");
$PAGE->requires->string_for_js('deleteiclickerconfirmationdialogbody', "mod_iclickerregistration");
$PAGE->requires->string_for_js('filterconflitcs', "mod_iclickerregistration");
$PAGE->requires->string_for_js('teachertoolsheadertext', "mod_iclickerregistration");
$PAGE->requires->string_for_js('generateclassrosterbuttontext', "mod_iclickerregistration");
$PAGE->requires->string_for_js('generateclassrotsterhelp', "mod_iclickerregistration");

/*
 * Let us cascade downward from most security sensitive to
 * least security sensitive. Pick the role with most security sensitive stuff first.
 */
$isadmin = has_capability('moodle/site:config', context_module::instance($cm->id));
$viewenrolled = has_capability('mod/iclickerregistration:viewenrolled', context_module::instance($cm->id));
$viewowncapability = has_capability('mod/iclickerregistration:viewown', context_module::instance($cm->id));
$accessdenied = !$isadmin && !$viewenrolled && !$viewowncapability;

// Execute the appropriate main, and in effect, redirect to appropriate url.
if ($isadmin) {
    $PAGE->requires->js_init_code("$.event.trigger('user_type_change', { user_type: 'admin' })");
} else if ($viewenrolled) {
    $PAGE->requires->js_init_code("$.event.trigger('user_type_change', { user_type: 'teacher' })");
} else if ($viewowncapability) {
    $PAGE->requires->js_init_code("$.event.trigger('user_type_change', { user_type: 'student' })");
} else {
    // Access denied.
    $PAGE->requires->js_init_code("$.event.trigger('user_type_change', { user_type: 'access_denied' })");
}

// Conditions to show the intro can change to look for own settings or whatever.
if ($iclickerregistration->intro) {
    echo $OUTPUT->box(format_module_intro('iclickerregistration', $iclickerregistration, $cm->id),
        'generalbox mod_introbox', 'iclickerregistrationintro');
}

echo $OUTPUT->heading(get_string('modulenameformatted', "mod_iclickerregistration"));

echo <<<HTML
<div id="mod-iclickerregistration" class="container-fluid"
     ng-app="M.mod_iclickerregistration.iClickerRegistrationApp">
  <div ng-view></div>
</div>
HTML;

// Finish the page.
echo $OUTPUT->footer();