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
require_once(dirname(__FILE__).'/../../config.php');
global $CFG;
require_once($CFG->dirroot.'/blocks/skills_group/locallib.php');
require_once($CFG->dirroot.'/blocks/skills_group/classes/skills_grouping.class.php');
require_once($CFG->dirroot.'/blocks/skills_group/classes/skills_group_setting.class.php');

/**
 * Skills group block class.
 *
 * @package    block_skills_group
 * @copyright  2014 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_skills_group extends block_base {

    /**
     * This function sets the title of the block.
     *
     */
    public function init() {
        $this->title = get_string('pluginname', BLOCK_SG_LANG_TABLE);
    }

    /**
     * This function tells moodle to process the admin settings.
     *
     */
    public function has_config() {
        return true;
    }

    /**
     * This function restricts the block to only courses and mods, preventing
     * access to it on the front page.
     *
     */
    public function applicable_formats() {
        return array('course-view' => true,
            'mod' => true,
            'my' => false);
    }

    /**
     *
     * This function draws links to the necessary functions depending on the user's
     * capability (defined by role).
     *
     * Instructor -> {Edit Group Settings}
     * Student -> {Create Group, Join Group}
     *
     */
    public function get_content() {
        global $COURSE, $USER;

        if ($this->content !== null) {
            return $this->content;
        }
        $this->content = new stdClass();
        $this->content->footer = '';

        $context = context_course::instance($COURSE->id);
        if (has_capability('block/skills_group:canmanageskillsgroups', $context)) {
            $url = new moodle_url('/blocks/skills_group/edit_skills_group_settings.php', array('courseid' => $COURSE->id,
                                  'sesskey' => $USER->sesskey));
            $this->content->footer .= html_writer::link($url, get_string('editgroupsettings', BLOCK_SG_LANG_TABLE));
            $this->content->footer .= html_writer::empty_tag('br');
        } else if (has_capability('block/skills_group:cancreateorjoinskillsgroups', $context)) {
            $sgs = new skills_group_setting($COURSE->id);
            if ($sgs->exists()) {
                if ($sgs->date_restriction() && time() > $sgs->get_date()) {
                    $this->content->footer .= html_writer::nonempty_tag('p', get_string('groupexpired', BLOCK_SG_LANG_TABLE));
                } else {
                    $url = new moodle_url('/blocks/skills_group/create_skills_group.php', array('courseid' => $COURSE->id,
                                          'sesskey' => $USER->sesskey));
                    $this->content->footer .= html_writer::link($url, get_string('createskillsgroup', BLOCK_SG_LANG_TABLE));
                    $this->content->footer .= html_writer::empty_tag('br');
                }
                $sgrouping = new skills_grouping($COURSE->id);
                // Only display option to join if user is not already part of a group.
                if ($sgrouping->check_for_user_in_grouping($USER->id) === false) {
                    if (!$sgs->date_restriction() || time() < $sgs->get_date()) {
                        $url = new moodle_url('/blocks/skills_group/join_skills_group.php', array('courseid' => $COURSE->id,
                                              'sesskey' => $USER->sesskey));
                        $this->content->footer .= html_writer::link($url, get_string('joinskillsgroup', BLOCK_SG_LANG_TABLE));
                        $this->content->footer .= html_writer::empty_tag('br');
                    }
                } else {
                    $url = new moodle_url('/blocks/skills_group/lock_choice.php', array('courseid' => $COURSE->id,
                                          'sesskey' => $USER->sesskey));
                    $this->content->footer .= html_writer::link($url, get_string('lockgrouplink', BLOCK_SG_LANG_TABLE));
                    $this->content->footer .= html_writer::empty_tag('br');
                    $url = new moodle_url('/blocks/skills_group/view_group.php', array('courseid' => $COURSE->id,
                                          'sesskey' => $USER->sesskey));
                    $this->content->footer .= html_writer::link($url, get_string('viewskillsgroup', BLOCK_SG_LANG_TABLE));
                    $this->content->footer .= html_writer::empty_tag('br');
                }
            } else {
                $this->content->footer .= html_writer::nonempty_tag('p', get_string('notconfigured', BLOCK_SG_LANG_TABLE));
            }
        }
        return $this->content;
    }

}