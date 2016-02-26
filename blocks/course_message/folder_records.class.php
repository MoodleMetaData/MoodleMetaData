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

// Status for read emails.
define('STATUS_READ', 1);

/**
 * This is the folder records class for the course message tool.  It retrives a list of mails from
 * a particular folder {'inbox'|'sent'} and JSON encodes them so they can be parsed in javascript.
 *
 * @package    block_course_message
 * @copyright  2012 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class folder_records{
    /** This is the mail folder to retrieve mails from */
    private $folder;
    /** This is the list of records */
    private $records;
    /** This is the sort parameter (field) - NOT USED */
    private $sortparam = 'timesent';
    /** This is the sort modified {ASC|DESC} - NOT USED */
    private $sorttype = 'DESC';

    /**
     * This is the constructor, the folder is setup and the records are retrieved.
     *
     * @param string $folder This is the folder to get the records from {'inbox'|'sent'}
     * @param int $courseid This is the course ID
     *
     */
    public function __construct($folder, $courseid) {
        global $DB, $USER;

        // Explicit check for valid folders here: update if custom folders are later permitted.
        if ($folder == 'inbox' || $folder == 'sent') {
            $this->folder = $folder;
            $this->courseid = $courseid;

            $params = array($USER->id, $courseid, $folder);
            $query = "SELECT a.id, useridfrom, recipients, subject, timesent, timeread
                        FROM {course_message_mails} a inner join {course_message_mail_map} b ON a.id = b.mailid
                        WHERE b.userid = ? AND a.courseid = ? AND b.folder = ?
                        ORDER BY timesent DESC";

            $this->records = $DB->get_records_sql($query, $params);
        }
    }

    /**
     * This function takes a list of records and adds them to an array that is later processed in javascript.
     *
     * @param array $tablerows This array will be appended with all of the requested records (passed by ref).
     * @param int $start This is the record we should begin processing at (wasteful, should be changed).
     *
     */
    public function get_table_rows(&$tablerows, $start) {
        global $DB;

        foreach ($this->records as $row) {
            $ids = ($this->folder == 'sent') ? $row->recipients : $row->useridfrom;
            $names = block_course_message_map_ids_to_names($ids, $this->folder);
            // Time format is handled in javascript.
            $timestring = $row->timesent;
            $status = ($row->timeread != 0) ? 1 : 0;

            if ($this->folder == 'sent') {
                $tablerows[] = array('id' => $row->id, 'to' => $names, 'subject' => $row->subject,
                        'sent' => $timestring, 'status' => STATUS_READ);
            } else {
                $tablerows[] = array('id' => $row->id, 'from' => $names, 'subject' => $row->subject,
                        'received' => $timestring, 'status' => $status);
            }
        }
    }

    /**
     * This method returns the total number of records
     *
     * @return int Total number of records
     *
     */
    public function total_records() {
        return count($this->records);
    }
}