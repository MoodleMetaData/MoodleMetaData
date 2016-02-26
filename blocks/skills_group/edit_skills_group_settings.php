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
 * This is the settings editor for skills_groups administrators.
 *
 * @package    block_skills_group
 * @category   block
 * @copyright  2014 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__).'/../../config.php');
global $CFG;
require_once($CFG->dirroot . '/blocks/skills_group/locallib.php');
require_once($CFG->dirroot . '/blocks/skills_group/classes/edit_skills_group_settings_form.class.php');
require_once($CFG->dirroot . '/blocks/skills_group/classes/skills_group_setting.class.php');

global $OUTPUT, $PAGE, $USER;

$courseid = required_param('courseid', PARAM_INT);
if (!blocks_skills_group_verify_access('block/skills_group:canmanageskillsgroups', true)) {
    redirect(new moodle_url('/course/view.php', array('id' => $courseid)));
}
$url = new moodle_url('/blocks/skills_group/edit_skills_group_settings.php', array('id' => $courseid, 'sesskey' => $USER->sesskey));
block_skills_group_setup_page($courseid, $url, get_string('editsettingstitle', BLOCK_SG_LANG_TABLE));

$editform = new edit_skills_group_settings_form($courseid);
$toform['courseid'] = $courseid;
// Retrieve any previously used settings.
$sgs = new skills_group_setting($courseid);
if ($sgs->exists()) {
    $toform['feedbacks'] = $sgs->get_feedback_id();
    $toform['groupings'] = $sgs->get_grouping_id();
    $toform['maxsize'] = $sgs->get_group_size();
    $toform['threshold'] = $sgs->get_threshold();
    $toform['allownaming'] = $sgs->get_allownaming();
    if ($sgs->date_restriction()) {
        $toform['datecheck'] = 1;
        $toform['date'] = $sgs->get_date();
    } else {
        $toform['datecheck'] = 0;
        $toform['date'] = null;
    }
}
$editform->set_data($toform);

if ($editform->is_cancelled()) {
    $courseurl = new moodle_url('/course/view.php', array('id' => $courseid));
    redirect($courseurl);
} else if ($fromform = $editform->get_data()) {
    process_form($courseid, $fromform);
    $courseurl = new moodle_url('/course/view.php', array('id' => $courseid));
    redirect($courseurl);
} else {
    $site = get_site();
    echo $OUTPUT->header();
    $editform->display();
    echo $OUTPUT->footer();
}

/**
 * This function processes the submitted form by saving the IDs of the feedback activity
 * and the grouping.
 *
 * @param int $courseid The ID of the course.
 * @param object $submittedform Object holding the submitted data by the user.
 *
 */
function process_form($courseid, &$submittedform) {
    global $DB;

    $sgs = new skills_group_setting($courseid);
    $sgs->update_record($submittedform);
}