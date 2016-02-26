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

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/blocks/skills_group/classes/skills_group_setting.class.php');
require_once($CFG->dirroot.'/blocks/skills_group/classes/skills_grouping.class.php');
require_once($CFG->dirroot.'/blocks/skills_group/classes/skills_group.class.php');

/**
 * lock_choice_form class
 *
 * This is the form definition for the lock_choice.php page.  This is a very simple form
 * that allows a user to prevent further changes to their group.
 *
 * @package    block_skills_group
 * @copyright  2014 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class lock_choice_form extends moodleform {

    /** This is the ID of the course. */
    private $courseid;

    /**
     * This method saves the variables needed for later when the form is created.
     *
     * @param int $courseid The ID of the course to create the form for.
     *
     */
    public function __construct($courseid) {

        $this->courseid = $courseid;
        parent::__construct();
    }

    /**
     * Form definition with two possibilities:
     *
     * 1) User part of group -> allow user to edit or drop group.
     * 2) User not part of group -> allow user to create new group.
     *
     */
    public function definition() {
        global $DB, $USER;

        $mform = &$this->_form;
        $mform->addElement('header', 'header', get_string('lockchoiceheader', BLOCK_SG_LANG_TABLE));

        $sgs = new skills_group_setting($this->courseid);

        if ($sgs->exists()) {
            $sgrouping = new skills_grouping($this->courseid);
            $groupid = $sgrouping->check_for_user_in_grouping($USER->id);
            if ($groupid !== false) {
                $sgroup = new skills_group($groupid);
                $mform->addElement('static', 'existinggroup', get_string('existinggroup', BLOCK_SG_LANG_TABLE),
                                   $sgroup->get_group_name());
                // Only display checkbox if student's choice isn't already locked.
                $student = new skills_group_student($this->courseid, $USER->id);
                if ($student->get_lock_choice() === true) {
                    $mform->addElement('static', 'lockchoicewarning', get_string('status', BLOCK_SG_LANG_TABLE),
                                       get_string('choicelocked', BLOCK_SG_LANG_TABLE));
                } else {
                    $mform->addElement('advcheckbox', 'lockchoice', get_string('lockchoice', BLOCK_SG_LANG_TABLE), null,
                                                        null, array(0, 1));
                }
                $mform->addElement('static', 'lockchoicewarning', get_string('warning', BLOCK_SG_LANG_TABLE),
                                   get_string('lockchoicewarning', BLOCK_SG_LANG_TABLE));
            } else {
                $mform->addElement('static', 'existinggroup', get_string('existinggroup', BLOCK_SG_LANG_TABLE),
                                   get_string('nogroup', BLOCK_SG_LANG_TABLE));
            }
        } else {
            $mform->addElement('static', 'notconfigured', get_string('notconfiguredleft', BLOCK_SG_LANG_TABLE),
                               get_string('notconfiguredright', BLOCK_SG_LANG_TABLE));
        }

        // Hidden elements: courseid needed for posting.
        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);

        $this->add_action_buttons();
    }
}