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
 * block_course_message mail_viewed event.
 * 
 * @package    block_course_message
 * @copyright  2015 Dominik Royko
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_course_message\event;

defined('MOODLE_INTERNAL') || die();

/**
 * The mail_viewed event class.
 *
 * @since     Moodle 2.8
 **/


class mail_viewed extends \core\event\base {
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'course_message_mails';
    }

    public static function get_name() {
        return get_string('eventmailviewed', 'block_course_message');
    }

    public function get_description() {
        return "User {$this->userid} viewed mail message {$this->objectid} " .
               "from folder {$this->other['folder']}, with parent {$this->other['parentid']} " .
               "in course {$this->courseid}.";
    }

    public function get_url() {
        return new \moodle_url('/block/course_message/inbox.php',
                               array('courseid' => $this->courseid, 'mailid' => $this->objectid));
    }

    public function get_legacy_logdata() {
        return array($this->courseid, get_string('pluginname', BLOCK_CM_LANG_TABLE), 'mail viewed', '',
                     'viewed mail, mailid: ' . $this->objectid, $this->objectid, $this->contextinstanceid);
    }
}
