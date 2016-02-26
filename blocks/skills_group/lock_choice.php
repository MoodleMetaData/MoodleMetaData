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

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG;
require_once($CFG->dirroot.'/blocks/skills_group/locallib.php');
require_once($CFG->dirroot.'/blocks/skills_group/classes/lock_choice_form.class.php');

global $OUTPUT, $PAGE, $USER;

$courseid = required_param('courseid', PARAM_INT);
if (!blocks_skills_group_verify_access('block/skills_group:cancreateorjoinskillsgroups', true)) {
    redirect(new moodle_url('/course/view.php', array('id' => $courseid)));
}
$url = new moodle_url('/blocks/skills_group/create_skills_group.php', array('id' => $courseid, 'sesskey' => $USER->sesskey));
block_skills_group_setup_page($courseid, $url, get_string('lockchoicetitle', BLOCK_SG_LANG_TABLE));

$lockchoiceform = new lock_choice_form($courseid);
$toform['courseid'] = $courseid;
$lockchoiceform->set_data($toform);

if ($lockchoiceform->is_cancelled()) {
    $courseurl = new moodle_url('/course/view.php', array('id' => $courseid));
    redirect($courseurl);
} else if ($fromform = $lockchoiceform->get_data()) {
    $url = process_form($courseid, $fromform);
    redirect($url);
} else {
    $site = get_site();
    echo $OUTPUT->header();
    $lockchoiceform->display();
    echo $OUTPUT->footer();
}

/**
 * This function determines the if user has locked in choice and updates database.
 *
 * @param int $courseid The ID of the course.
 * @param object $submittedform The object contains the results of the form when changes were saved.
 * @return string The url to redirect the user to.
 *
 */
function process_form($courseid, &$submittedform) {
    global $USER;

    if (isset($submittedform->lockchoice)) {
        if ($submittedform->lockchoice) {
            $student = new skills_group_student($courseid, $USER->id);
            $student->set_lock_choice(true);
            $url = new moodle_url('/course/view.php', array('id' => $courseid));
        }
    } else {
        $url = new moodle_url('/course/view.php', array('id' => $courseid));
    }
    return $url;
}