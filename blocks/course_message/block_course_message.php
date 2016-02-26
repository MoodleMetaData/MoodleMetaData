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
require_once($CFG->dirroot.'/blocks/course_message/locallib.php');
require_once('contact_list.class.php');

/**
 * Course message block class.
 *
 * This is the class definition for the course_message block.  Most of what follows below is standard
 * Moodle requirements.  I've split a lot of the operations into smaller functions so the code is easier
 * to follow and modify if needed.
 *
 * @package    block_course_message
 * @copyright  2012 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_course_message extends block_base{

    /**
     * This function sets the title of the block.
     *
     */
    public function init() {
        $this->title = get_string('pluginname', BLOCK_CM_LANG_TABLE);
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
     * acess to it on the front page.
     *
     */
    public function applicable_formats() {
        return array('course-view' => true,
            'mod' => true,
            'my' => false);
    }

    /**
     *
     * This is the main function that Moodle requires to draw in the block.  The footer content is set and all
     * includes are setup.  The mail block is setup mostly via defining a bunch of divs, that way they can be
     * turned off/on as needed.  The more detailed inbox window is setup as an iFrame.
     *
     */
    public function get_content() {
        global $CFG, $COURSE, $PAGE, $USER;

        if ($this->content !== null) {
            return $this->content;
        }
        $this->content = new stdClass();
        if (isguestuser()) {
            $this->content = get_string('guestnomail', BLOCK_CM_LANG_TABLE);
            return $this->content;
        }
        $this->content->footer = '';

        $this->set_header();
        $this->draw_block();
        $this->draw_dialogs();

        $inboxtitle = get_string('pluginname', BLOCK_CM_LANG_TABLE).": ".$COURSE->fullname;

        // Load iframe only if requested by user.
        if (block_course_message_get_display_preference($USER->id) == BLOCK_CM_IN_IFRAME) {
            $this->content->footer .= '<iframe id="inboxIframe" title="'.$inboxtitle.'" src="'.
            '/blocks/course_message/inbox.php?courseid='.$COURSE->id.'" style="display:none"></iframe>';
        }
        return $this->content;
    }

    /**
     * This function set the page header -> JS/CSS includes.
     *
     */
    private function set_header() {
        global $CFG, $COURSE, $PAGE, $USER;

        // It would be nice to put this code in its own function.
        $contactlist = new contact_list($COURSE->id, $USER->id);
        $groups = array();
        $groupids = array();
        $contacts = array();
        $contactids = array();
        $contactlist->contacts_as_array($contacts, $contactids, $groups, $groupids);

        $PAGE->requires->css('/blocks/course_message/css/inbox.css');
        $PAGE->requires->yui_module('moodle-block_course_message-mail_lib', '(function(){})');
        $PAGE->requires->yui_module('moodle-block_course_message-send_from_block', 'M.block_course_message.init_send_from_block',
            array(array('courseid' => $COURSE->id, 'contacts' => $contacts, 'contactids' => $contactids, 'groups' => $groups,
                        'groupids' => $groupids)));
        $PAGE->requires->strings_for_js(array('nocontactswarning', 'nosubjectwarning', 'nomessagewarning', 'mailnotsent'),
                                        BLOCK_CM_LANG_TABLE);

    }

    /**
     * This method is responsible for drawing the block on the screen.  For example, the "to" field,
     * message area, and buttons at the bottom are drawn.
     *
     */
    private function draw_block() {

        $this->draw_unread_mail_indicator();
        $this->draw_message_area();
    }

    /**
     * This method draws the unread mail indicator on the screen and sets the div to display depending on
     * whether or not the user has unread mail using the has_unread_mail() method.
     *
     */
    private function draw_unread_mail_indicator() {
        global $CFG, $COURSE, $USER;

        $this->content->footer .= '<div id="bar" class="unreadbar" ';

        $courseid = $COURSE->id;
        if (block_course_message_has_unread_mail($courseid) > 0) {
            $this->content->footer .= 'style="display:show;">';
        } else {
            $this->content->footer .= 'style="display:none;">';
        }

        $this->content->footer .= get_string('unreadbartext', BLOCK_CM_LANG_TABLE).'</div>';
        // Also store the user's display preference.
        $this->content->footer .= '<input type="hidden" id="displaypreference" msg="'.
                                  block_course_message_get_display_preference($USER->id).'"/>';
    }

    /**
     * This method draws the subject and message areas on the screen and also appends the inbox and send
     * buttons at the bottom.
     *
     */
    private function draw_message_area() {
        $this->content->footer .= '
                        <input type="text" id="contactfromblock" class="block_input setwidthmax" size="24" placeholder="'.
                        get_string('contactslabel', BLOCK_CM_LANG_TABLE).'">
                        <input type="text" id="subjectfromblock" size="24" placeholder="'.
                        get_string('subjectlabel', BLOCK_CM_LANG_TABLE).'" class="block_input setwidthmax"/>
                        <textarea id="messagefromblock" class="setwidthmax"></textarea>';

        $this->draw_buttons();

    }

    /**
     * This method draws the inbox and send buttons at the bottom of the block.  Notice that the button
     * function changes depending on the user preference setting.
     *
     */
    private function draw_buttons() {
        global $USER, $COURSE;

        $this->content->footer .= '<div><button type="button" id="sendmailfromblock">'.
                                  get_string('sendbutton', BLOCK_CM_LANG_TABLE).'</button>';
        if (block_course_message_get_display_preference($USER->id) == BLOCK_CM_ON_PAGE) {
            $this->content->footer .= '<button type="button" onclick="window.open(\'/blocks/course_message/inbox.php?courseid='.
                                      $COURSE->id.'\', \'inbox\');">'.get_string('openinboxbutton', BLOCK_CM_LANG_TABLE).
                                      '</button>';
        } else {
            $this->content->footer .= '<button type="button" id="dispInbox">'.
                                      get_string('openinboxbutton', BLOCK_CM_LANG_TABLE).'</button>';
        }
        $this->content->footer .= '</div>';

        $this->content->footer .= '<div style="font-style:italic;">'.get_string('useinbox', BLOCK_CM_LANG_TABLE).'</div>';
    }

    /**
     * This method draws an empty div that will be used to give/receive information.
     *
     */
    private function draw_dialogs() {
        $this->content->footer .= '<div id="alertpanel"><div id="alerttext"></div></div>';
    }
}