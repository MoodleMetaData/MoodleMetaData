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
 * This is the settings edit handler that the user is redirected to if they
 * wish to edit the color or enable/disable settings.
 *
 * @package    collblct
 * @category   course/format
 * @copyright  2012 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__).'/../../../config.php');
require_once('settings_edit_form.php');
require_once('course_color_record.class.php');
require_once('course_section_record.class.php');
global $CFG;
require_once($CFG->dirroot.'/course/format/collblct/locallib.php');

global $DB, $OUTPUT, $PAGE;

$courseid = required_param('courseid', PARAM_INT);
if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'format_collblct', $courseid);
}
require_login($course);
$context = context_course::instance($courseid);
if (!has_capability('format/collblct:caneditcollapsedlabelcolors', $context)) {
    echo get_string('noaccess', FORMAT_CTWCL_LANG_TABLE);
    return;
}

$PAGE->set_url('/course/format/collblct/edit_colors.php', array('id' => $courseid));
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('editcolorspageheader', FORMAT_CTWCL_LANG_TABLE));
$PAGE->set_heading(get_string('editcolorspageheader', FORMAT_CTWCL_LANG_TABLE));

$settingseditform = new settings_edit_form($courseid);
$toform = set_values($courseid);
$settingseditform->set_data($toform);

if ($settingseditform->is_cancelled()) {
    // Cancelled forms redirect to the course main page.
    $courseurl = new moodle_url('/course/view.php', array('id' => $courseid));
    redirect($courseurl);
} else if ($fromform = $settingseditform->get_data()) {
    // Process form and redirect on completion.
    process_form($courseid, $fromform);
    $courseurl = new moodle_url('/course/view.php', array('id' => $courseid));
    redirect($courseurl);
} else {
    // Form didn't validate or this is the first display.
    $site = get_site();
    echo $OUTPUT->header();
    $settingseditform->display();
    echo $OUTPUT->footer();
}

/**
 * This function just grabs any pre-existing values for the form settings and
 * adds them to an array.
 *
 * @param int $courseid This is the course ID.
 * @return array pre-existing form values (use ->set_data()) to set.
 *
 */
function set_values($courseid) {
    $colorrecord = new course_color_record($courseid);

    // Saving the blockid and courseid allows the page to continue upon post.
    $toform['courseid'] = $courseid;
    $toform['backgroundcolor'] = $colorrecord->get_background_color();
    $toform['foregroundcolor'] = $colorrecord->get_foreground_color();

    $sectionheaders = array();
    $numberofsections = ctwcl_get_section_titles($courseid, $sectionheaders);
    $csr = new course_section_record($courseid);

    for ($i = 0; $i < $numberofsections; $i++) {
        $sectioncheckbox = 'sectioncheckbox_'."$i";
        // Moodle sections count 1->N, section 0 is the summary area at the top.
        $toform[$sectioncheckbox] = $csr->get_section_status($i + 1);
    }

    return $toform;
}

/**
 * This function processes the submitted form for the two requested colors.  Earlier
 * in this file, the default values are placed into the form, so I only handle updates
 * if the values have changed from the default.
 *
 */
function process_form($courseid, &$submittedform) {
    global $DB;

    if (isset($submittedform->returndefault)) {
        $colorrecord = new course_color_record($courseid);
        $colorrecord->delete_record();
        return;
    }

    /* NOTE: I've written these as separate cases to try to only write new colors and preserve
     * the code falling back to the defaults as best as I can. */
    if ($submittedform->foregroundcolor != DEFAULT_FOREGROUND) {
        $colorrecord = new course_color_record($courseid);
        $colorrecord->set_foreground_color($submittedform->foregroundcolor);
    }
    if ($submittedform->backgroundcolor != DEFAULT_BACKGROUND) {
        $colorrecord = new course_color_record($courseid);
        $colorrecord->set_background_color($submittedform->backgroundcolor);
    }

    // Now check each of the sections.
    $sectionheaders = array();
    $numberofsections = ctwcl_get_section_titles($courseid, $sectionheaders);
    $csr = new course_section_record($courseid);

    for ($i = 0; $i < $numberofsections; $i++) {
        $sectionfield = 'sectionfield_'."$i";
        $sectioncheckbox = 'sectioncheckbox_'."$i";
        // Moodle sections count 1->N, section 0 is the summary area at the top.
        $csr->update_section_record($i + 1, $submittedform->$sectioncheckbox);
    }

}