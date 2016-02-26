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

/**
 * This is the contacts list class.  It generates a contacts list and displays
 * the information back in the appropriate format.
 *
 * @package    block_course_message
 * @copyright  2012 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class contact_list{
    /** This is the course ID */
    public $courseid;
    /** This is the user's ID */
    public $userid;

    /**
     * This function constructs an empty contacts list.
     *
     * @param int $courseid This is the course ID.
     * @param int $userid This is the user ID who needs a contact list.
     *
     */
    public function __construct($courseid, $userid) {
        $this->courseid = $courseid;
        $this->userid = $userid;
    }

    /**
     * This function writes out the contact list in one of three formats.
     * This now only has a single format -> used in the block, since 'dual_list' and
     * 'chzn' were removed with the re-write to YUI.
     *
     * @param string $contacttype Format to display ('block' | 'dual_list' | 'chzn')
     * @param object $outputbuffer When writing in 'block' format, the outputbuffer must
     * be passed so it can be written to.  For the other two formats, just pass any variable
     * that has been allocated (it will not be changed).
     *
     */
    public function display_contacts(&$outputbuffer) {
        global $DB;

        $context = context_course::instance($this->courseid);
        $params = array($context->id, $this->userid);
        $query = "SELECT DISTINCT u.id as id, firstname, lastname, picture, imagealt, email
                  FROM {role_assignments} as a, {user} as u
                  WHERE contextid = ? and a.userid=u.id and u.id <> ?
                  ORDER BY firstname";
        $namerecords = $DB->get_records_sql($query, $params);

        $grouprecords = $DB->get_records('groups', array('courseid' => $this->courseid));

        // All instructors code removed (potential privacy issue).  Check older commits if needed.
        $outputbuffer .= '<div class="contactList"  id="s1" first="'.get_string('allstudents', BLOCK_CM_LANG_TABLE)
                        .'" last="">';
        $outputbuffer .= get_string('allstudents', BLOCK_CM_LANG_TABLE).' </div>';
        foreach ($namerecords as $namerecord) {
            $outputbuffer .= '<div class="contactList"  id="'.$namerecord->id.'" first="'.$namerecord->firstname
                        .'" last="'.$namerecord->lastname.'">';
            $outputbuffer .= $namerecord->firstname.' '.$namerecord->lastname.'</div>';
        }
        foreach ($grouprecords as $gr) {
            $outputbuffer .= '<div class="contactList"  id="g'.$gr->id.'" first="'.$gr->name.'" last="">';
            // Note the extra space before the closing div tag -> removing it will cause a test to fail.
            $outputbuffer .= $gr->name.' </div>';
        }
    }

    /**
     * This function gives the YUI code for inbox.php the proper list of contacts.
     *
     * @param array $contacts List of contacts in the course (fname lname)
     * @param array $contactids User ID of each contact in the $contacts list
     * @param array $groups List of groups in the course (name)
     * @param array $groupids Group ID for each of the groups in the $groups list
     *
     */
    public function contacts_as_array(&$contacts, &$contactids, &$groups, &$groupids) {
        global $DB,
        $OUTPUT;

        $context = context_course::instance($this->courseid);
        $params = array($context->id, $this->userid);
        $query = "SELECT DISTINCT u.id as id, firstname, lastname, picture, imagealt, email
                  FROM {role_assignments} as a, {user} as u
                  WHERE contextid = ? and a.userid=u.id and u.id <> ?
                  ORDER BY firstname";
        $namerecords = $DB->get_records_sql($query, $params);

        foreach ($namerecords as $namerecord) {
            $contacts[] = $namerecord->firstname.' '.$namerecord->lastname;
            $contactids[] = "$namerecord->id";
        }

        $grouprecords = $DB->get_records('groups', array('courseid' => $this->courseid), 'name');

        foreach ($grouprecords as $grouprecord) {
            $groups[] = $grouprecord->name;
            $groupids[] = "g$grouprecord->id";
        }
    }
}