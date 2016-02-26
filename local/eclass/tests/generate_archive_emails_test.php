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
 * The generate archive emails script unit tests
 *
 * @package    local/eclass/
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

class generate_archive_emails_testcase extends advanced_testcase {

    private $output;

    /**
     * Test the script.
     */
    public function test_generate_archive_emails() {
        global $CFG;

        // Create test file.
        $testdata =
            'Course ID,Course shortname,Course fullname,First name(s),Last name(s),Email(s),CCIDs,Opt-Out flag'.PHP_EOL.
            '70,TEST 70 short,TEST 70 full 1 instr,,,royko@ualberta.ca,,'.PHP_EOL.
            '71,TEST 71 short,TEST 71 fu11 0 instr,,,,,'.PHP_EOL.
            '72,TEST 72 short,TEST 72 fu11 1 instr opt-out,,,royko@ualberta.ca,,0'.PHP_EOL.
            '73,TEST 73 short,TEST 73 fu11 2 instr,"Dominik,Dom","Royko,Royko",'.
                '"dominik.royko@ualberta.ca,royko@ualberta.ca","dominik.royko,royko",'.PHP_EOL.
            '74,TEST 74 short,TEST 74 fu11 2 instr opt-out,"Dominik,Dom","Royko,Royko",'.
                '"dominik.royko@ualberta.ca,royko@ualberta.ca","dominik.royko,royko",X\n';
        file_put_contents('sample-archive-list.csv', $testdata);

        // Scenario: No input.
        $expectedoutput = array(
            'Please supply an archive list as input.',
            "Usage: php generate_archive_emails.php < 'archive-list.csv'"
        );
        exec('echo | php ' . $CFG->dirroot . '/local/eclass/landing/generate_archive_emails.php', $this->output);
        $this->assertEquals($expectedoutput, $this->output);

        // Scenario: No records.
        $expectedoutput = "No courses to be archived.  No mail to send.";
        unset($this->output);
        exec('echo "No records found." | php ' . $CFG->dirroot . '/local/eclass/landing/generate_archive_emails.php',
             $this->output);
        $this->assertEquals($expectedoutput, strval($this->output[0]));

        // Scenario: Junk input.
        $expectedoutput = "Input does not seem to be a valid archive course list.  Has the format changed?";
        unset($this->output);
        exec('echo "blah,blah,blah" | php ' . $CFG->dirroot . '/local/eclass/landing/generate_archive_emails.php', $this->output);
        $this->assertEquals($expectedoutput, strval($this->output[0]));

        // Scenario: Success!
        $expectedoutput = array(
            'Course "TEST 72 fu11 1 instr opt-out" is opted out of archiving (0).',
            'Course "TEST 74 fu11 2 instr opt-out" is opted out of archiving (X\n).',
            'From: eclass@ualberta.ca',
            'To: royko@ualberta.ca',
            'Subject: eClass course archiving',
            'Hello Dom Royko,',
            '',
            'The following eClass course(s) in which you are enrolled as an instructor are scheduled for archiving within 2 weeks:',
            '',
            'TEST 70 full 1 instr',
            'TEST 73 fu11 2 instr',
            '',
            'After archiving, you will still be able to access the course contents through the eClass Section Management system.',
            '',
            'If you would like to opt-out of archiving to leave the courses live on eClass, please contact eclass@ualberta.ca.',
            '',
            'Thank you,',
            'The eClass Team',
            '.',
            'From: eclass@ualberta.ca',
            'To: dominik.royko@ualberta.ca',
            'Subject: eClass course archiving',
            'Hello Dominik Royko,',
            '',
            'The following eClass course(s) in which you are enrolled as an instructor are scheduled for archiving within 2 weeks:',
            '',
            'TEST 73 fu11 2 instr',
            '',
            'After archiving, you will still be able to access the course contents through the eClass Section Management system.',
            '',
            'If you would like to opt-out of archiving to leave the courses live on eClass, please contact eclass@ualberta.ca.',
            '',
            'Thank you,',
            'The eClass Team',
            '.'
        );
        unset($this->output);
        exec('php ' . $CFG->dirroot . '/local/eclass/landing/generate_archive_emails.php -n < sample-archive-list.csv',
             $this->output);
        $this->assertEquals($expectedoutput, $this->output);
    }
}
