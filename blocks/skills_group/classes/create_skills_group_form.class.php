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
 * create_skills_group_form class
 *
 * This is the form definition for the create_skills_group.php page.  Strictly
 * speaking, it allows the user to create a new group, edit their existing group,
 * or remove themselves from their current group.
 *
 * @package    block_skills_group
 * @copyright  2014 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class create_skills_group_form extends moodleform {

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
        $mform->addElement('header', 'header', get_string('creategroupheader', BLOCK_SG_LANG_TABLE));

        $sgs = new skills_group_setting($this->courseid);

        if ($sgs->exists()) {
            $sgrouping = new skills_grouping($this->courseid);
            $groupid = $sgrouping->check_for_user_in_grouping($USER->id);
            if ($groupid !== false) {
                $sgroup = new skills_group($groupid);
                $mform->addElement('static', 'existinggroup', get_string('existinggroup', BLOCK_SG_LANG_TABLE),
                                   $sgroup->get_group_name());
                $mform->addElement('hidden', 'groupid', $groupid);
                $mform->setType('groupid', PARAM_INT);
                if ($sgs->date_restriction() && time() > $sgs->get_date()) {
                    $mform->addElement('static', 'dateexpired', get_string('dateexpiredleft', BLOCK_SG_LANG_TABLE),
                                       get_string('dateexpiredright', BLOCK_SG_LANG_TABLE));
                    $mform->addElement('hidden', 'type', 'expired');
                    $mform->setType('type', PARAM_TEXT);
                } else {
                    $mform->addElement('advcheckbox', 'editmembers', get_string('editmembers', BLOCK_SG_LANG_TABLE), null,
                                       null, array(0, 1));
                    // Student can only leave if not already locked.
                    $student = new skills_group_student($this->courseid, $USER->id);
                    if ($student->get_lock_choice() === false) {
                        $mform->addElement('advcheckbox', 'leavegroup', get_string('leavegroup', BLOCK_SG_LANG_TABLE), null,
                                           null, array(0, 1));
                        $mform->disabledIf('leavegroup', 'editmembers', 'checked');
                        $mform->disabledIf('editmembers', 'leavegroup', 'checked');
                    }
                    $mform->addElement('hidden', 'type', 'edit');
                    $mform->setType('type', PARAM_TEXT);
                }
                $mform->addElement('advcheckbox', 'allowjoincheck', get_string('groupsearchable', BLOCK_SG_LANG_TABLE),
                                   null, null, array(0, 1));
                $mform->disabledIf('allowjoincheck', 'leavegroup', 'checked');
            } else {
                $mform->addElement('static', 'existinggroup', get_string('existinggroup', BLOCK_SG_LANG_TABLE),
                                   get_string('nogroup', BLOCK_SG_LANG_TABLE));
                if ($sgs->date_restriction() && time() > $sgs->get_date()) {
                    $mform->addElement('static', 'dateexpired', get_string('dateexpiredleft', BLOCK_SG_LANG_TABLE),
                                       get_string('dateexpiredright', BLOCK_SG_LANG_TABLE));
                    $mform->addElement('hidden', 'type', 'expired');
                    $mform->setType('type', PARAM_TEXT);
                } else {
                    if ($sgs->get_allownaming() == true) {
                        $pagegroup = array();
                        $pagegroup[] = $mform->createElement('advcheckbox', 'creategroupcheck', null, null, null, array(0, 1));
                        $pagegroup[] = $mform->createElement('text', 'creategroup', null, array());
                        $mform->setType('creategroup', PARAM_TEXT);
                        $mform->disabledIf('creategroup', 'creategroupcheck');
                        $mform->addGroup($pagegroup, 'create', get_string('creategroup', BLOCK_SG_LANG_TABLE), null, false);
                    } else {
                        // Create element outside of group so that form spacing is correct.
                        $mform->addElement('advcheckbox', 'creategroupcheck', get_string('creategroup', BLOCK_SG_LANG_TABLE),
                                           null, null, array(0, 1));
                    }
                    $mform->addElement('hidden', 'type', 'create');
                    $mform->setType('type', PARAM_TEXT);
                    $mform->addElement('advcheckbox', 'allowjoincheck', get_string('groupsearchable', BLOCK_SG_LANG_TABLE),
                                       null, null, array(0, 1));
                }
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