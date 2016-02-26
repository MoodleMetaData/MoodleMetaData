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
 * This is the block edit handler that the user is redirected to if they
 * wish to edit the image settings.
 *
 * @package    block_nurs_navigation
 * @category   block
 * @copyright  2012 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__).'/../../config.php');
require_once('edit_navigation_form.class.php');
global $CFG;
require_once($CFG->dirroot.'/blocks/nurs_navigation/locallib.php');
require_once('section_icon.php');

global $DB, $OUTPUT, $PAGE, $USER;

$courseid = required_param('courseid', PARAM_INT);
$blockid = required_param('blockid', PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_nurs_navigation', $courseid);
}
if (!isloggedin()) {
    echo get_string('loginrequired', BNN_LANG_TABLE);
    return;
}
if (!has_capability('block/nurs_navigation:caneditnursnavigation', context_course::instance($courseid))) {
    echo get_string('noaccess', BNN_LANG_TABLE);
    return;
}

$PAGE->set_url('/blocks/nurs_navigation/edit_navigation.php', array('id' => $courseid));
$PAGE->set_context(context_user::instance($USER->id));
$PAGE->set_title(get_string('editpagetitle', BNN_LANG_TABLE));
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('editpagetitle', BNN_LANG_TABLE));

$sectionheaders = array();
$numberofsections = get_section_titles($courseid, $sectionheaders);
$naveditform = new edit_navigation_form($numberofsections, $sectionheaders, $courseid);

// Saving the blockid and courseid allows the page to continue upon post.
$toform['courseid'] = $courseid;
$toform['blockid'] = $blockid;

// And setup icon checkboxes.
for ($i = 0; $i < $numberofsections; $i++) {
    $si = new section_icon($courseid, $sectionheaders[$i]);
    $noiconname = 'noicon_'."$i";
    $toform[$noiconname] = $si->get_icon_disable();
    $customlabelfield = 'customlabelfield_'."$i";
    $customlabelcheckbox = 'customlbelcheckbox_'."$i";
    if ($si->get_custom_label() != null) {
        $toform[$customlabelcheckbox] = true;
        $toform[$customlabelfield] = $si->get_custom_label();
    } else {
        $toform[$customlabelcheckbox] = false;
    }

}
$naveditform->set_data($toform);

if ($naveditform->is_cancelled()) {
    // Cancelled forms redirect to the course main page.
    $courseurl = new moodle_url('/course/view.php', array('id' => $courseid));
    redirect($courseurl);
} else if ($fromform = $naveditform->get_data()) {
    // Process form and redirect on completion.
    process_form($courseid, $blockid, $fromform, $sectionheaders, $numberofsections);
    $courseurl = new moodle_url('/course/view.php', array('id' => $courseid));
    redirect($courseurl);
} else {
    // Form didn't validate or this is the first display.
    $site = get_site();
    echo $OUTPUT->header();
    $naveditform->display();
    echo $OUTPUT->footer();
}

/**
 * This function processes the submitted form for all of the sections.  Most of the work
 * is completed via using the section_icon class and its associated routines.  The data
 * is pulled from the form, a check is made to see if any boxes are ticked, then the
 * desired result is processed.  Any draft files that need to be saved are saved in this
 * function.
 *
 * TODO: some logic should be added to outright delete a settings record if it has neither
 * a disable flag or custom label associated with it.
 *
 */
function process_form($courseid, $blockid,  & $submittedform,  & $sectionheaders, $numberofsections) {
    global $DB;

    for ($i = 0; $i < $numberofsections; $i++) {
        $filepickername = 'fileinfo_'."$i";
        $hiddenidname = 'hiddenid_'."$i";
        $masterupdatename = 'masterid_'."$i";
        $deleteiconname = 'deleteid_'."$i";
        $noiconname = 'noicon_'."$i";
        $newcourseid = $courseid;
        $customlabelfield = 'customlabelfield_'."$i";
        $customlabelcheckbox = 'customlbelcheckbox_'."$i";

        $si = new section_icon($courseid, $sectionheaders[$i]);

        // Update master via using courseid 0.
        if (!isset($submittedform->$masterupdatename)) {
            // Use courseid of 1 so we get the correct context.
            $newcourseid = 1;
            $context = context_course::instance($newcourseid);
        } else {
            // Save block context instead of the course context for course-specific records.
            $context = context_block::instance($blockid);
        }

        // Check for delete first.
        if (isset($submittedform->$deleteiconname)) {
            $si->delete_record();
            continue;
        }

        $draftitemid = file_get_submitted_draft_itemid($filepickername);
        $itemid = get_unused_file_id();
        file_save_draft_area_files($draftitemid, $context->id, BNN_BLOCK_SAVE_COMPONENT, BNN_BLOCK_SAVE_AREA, $itemid,
                                   array('subdirs' => 0, 'maxbytes' => BNN_MAX_BYTES, 'maxfiles' => BNN_MAX_FILES));
        // Only update if the user uploaded a file.
        if (check_draft_id($draftitemid)) {
            $si->update_icon_record($newcourseid, $itemid);
        }
        // The other IFs are separate cases because the user can do multiple things at once.
        if ($submittedform->$noiconname != $si->get_icon_disable()) {
            $si->update_disableicon($submittedform->$noiconname);
        }
        if ($submittedform->$customlabelcheckbox == true) {
            // Only write label if different from existing label.
            if ($si->get_custom_label() != $submittedform->$customlabelfield) {
                $si->update_label($submittedform->$customlabelfield);
            }
        } else {
            // Only write a null if the record exists and is not already null.
            if ($si->settings_exists() && ($si->get_custom_label() != null)) {
                $si->update_label(null);
            }
        }
    }
}