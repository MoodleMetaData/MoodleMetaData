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
require_once($CFG->dirroot.'/blocks/course_message/folder_records.class.php');
require_once($CFG->dirroot.'/blocks/course_message/tests/mailunittest.php');

// TODO: these tests need to be updated.

/**
 * This is the unittest class for folder_records.class.php.
 *
 *	The following functions are checked:
 * 1) folder_records -> constructor
 * 2) folder_records -> get_table_rows()
 * 3) inbox_params -> constructor()
 * 4) inbox_params -> calc_table_params()
 *
 * @package    block_course_message
 * @group      block_course_message_tests
 * @copyright  2012 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class test_folderrecords extends mail_unit_test {

    /**
     * This function tests the constructor and also the total_records() method.  Both need to be
     * functioning properly for the test to work.  This probably is not the best practice, but the
     * total_records() method is a simple accessor method that contains a single line.
     *
     */
    public function test_constructor() {

        $this->setUser($this->craig);

        // Test that the right number of mail is pulled from the inbox.
        $folder = 'inbox';
        $fr = new folder_records($folder, $this->testcourseid);
        $numberofrecords = $fr->total_records();
        $this->assertEquals($numberofrecords, 1);

        // Test that the right number of mail is pulled from the sent messages folder.
        $folder = 'sent';
        $fr = new folder_records($folder, $this->testcourseid);
        $numberofrecords = $fr->total_records();
        $this->assertEquals($numberofrecords, 4);

        // Test with invalid (not 'sent' or 'inbox') folder.
        $folder = 'invalid';
        $fr = new folder_records($folder, $this->testcourseid);
        $numberofrecords = $fr->total_records();
        $this->assertEquals($numberofrecords, 0);
    }

    /**
     * This function checks get_table_rows().  It validates that the correct information was pulled
     * from the DB and also that it's formatting (in an array) is correct so that it can be JSON
     * encoded and sent to javascript.
     *
     */
    public function test_get_table_rows() {

        $this->setUser($this->friend);
        $folder = 'inbox';

        $tablerows = array();
        $fr = new folder_records($folder, $this->testcourseid);
        $fr->get_table_rows($tablerows, 0);

        $indices = array();
        $mailids = array($this->mailids[self::GENERICMAILID], $this->mailids[self::DELETEMAILID],
                         $this->mailids[self::PARENTMAILID]);
        $this->find_indices($tablerows, $mailids, $indices);

        // Testing 3 of 4 (all unread -> 0).
        $this->check_message($tablerows[$indices[0]], $mailids[0], "Craig Jamieson", 'Test Mail 1', 0, $folder);
        $this->check_message($tablerows[$indices[1]], $mailids[1], "Craig Jamieson", 'Delete Mail 1', 0, $folder);
        $this->check_message($tablerows[$indices[2]], $mailids[2], "Craig Jamieson", 'Threaded Message Test', 0, $folder);

        $this->setUser($this->craig);
        $folder = 'sent';
        $tablerows = array();

        $fr = new folder_records($folder, $this->testcourseid);
        $fr->get_table_rows($tablerows, 0);

        $indices = array();
        $mailids = array($this->mailids[self::DELETEMAILID], $this->mailids[self::PARENTMAILID], $this->mailids[self::LASTMAILID]);
        $this->find_indices($tablerows, $mailids, $indices);

        // Testing 3 of 4 again: status field is unused, set to 1 in all cases.
        $this->check_message($tablerows[$indices[0]], $mailids[0], "Craig's Friend", 'Delete Mail 1', 1, $folder);
        $this->check_message($tablerows[$indices[1]], $mailids[1], "Craig's Friend", 'Threaded Message Test', 1, $folder);
        $this->check_message($tablerows[$indices[2]], $mailids[2], "Craig's Friend", 'Threaded Message Test', 1, $folder);
    }

    /**********************************************************************
     * Helper functions are below:
     **********************************************************************/
    /**
     * The logic in this function should be fairly straightforward, it is looking for a set of indices
     * in a table that are match the values stored in mailids.  Its purpose, however, may be unclear.
     * I do not want to hardcode the indices checked in test_get_table_rows(), because , while it works
     * for the postgress database I test with, it may not work for another database if the records
     * are returned in a different order.
     *
     * @param 2darray $table This is the 2d array to search for the ids in.
     * @param array $mailids This is a message ids to look for.
     * @param array $indices The locations of the mailids will be appended to this matrix.
     *
     */
    private function find_indices(&$table, &$mailids, &$indices) {
        for ($i = 0; $i < count($mailids); $i++) {
            for ($j = 0; $j < count($table); $j++) {
                if ($table[$j]['id'] == $mailids[$i]) {
                    $indices[] = $j;
                }
            }
        }
    }

    /**
     * This function was wrote to avoid duplicate code, but it looks fairly ugly.  It checks a table row
     * for the proper formatting to be sent to javascript.  Implicity it checks the database retrieval as well
     * since the fields are validated against what should have come from the database.
     *
     * @param mixed $tablerow This is table row to check for info
     * @param mixed $mailid The mail ID
     * @param mixed $tofrom The contents of the to or from field (depending on $folder)
     * @param mixed $subject The subject field
     * @param mixed $status The status field (read|unread)
     * @param mixed $folder The folder that is being checked (inbox checks from, sent checks to)
     *
     */
    private function check_message($tablerow, $mailid, $tofrom, $subject, $status, $folder) {
        $this->assertEquals($tablerow["id"], $mailid);
        if ($folder == 'inbox') {
            $this->assertEquals($tablerow["from"], $tofrom);
            $this->assertEquals($tablerow["status"], $status);
        } else {
            $this->assertEquals($tablerow["to"], $tofrom);
        }
        $this->assertEquals($tablerow["subject"], $subject);
    }
}