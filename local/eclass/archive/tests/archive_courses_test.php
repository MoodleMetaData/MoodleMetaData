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
 * The course archiving script unit tests
 *
 * @author     Anthony Radziszewski radzisze@ualberta.ca
 * @package    local/eclass/archive
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class archive_courses_testcase extends advanced_testcase {

    private $output;

    /**
     * Test the script.
     */

    public function test_archive_courses() {
        global $CFG;
        $this->resetAfterTest(true);

        // Create test file.
        $testdata =
            'Course ID,Course shortname,Course fullname,First name(s),Last name(s),Email(s),CCIDs,Opt-Out flag'.PHP_EOL.

            '2,TEST 2 short,TEST 2 fu11 0 instr,,,,,'.PHP_EOL.
            '3,TEST 3 short,TEST 3 fu11 1 instr opt-out,,,a@b.c,,0\n';
        file_put_contents('sample-archive-list.csv', $testdata);
        file_put_contents('empty-archive-list.csv', '');
        file_put_contents('invalid-archive-list.csv', 'blah blah blah');

        // Scenario: No input.
        $expectedoutput = array(
            'Please supply an archive list as input.',
            "Usage: php archive_courses.php https://eclass-dev.srv.ualberta.ca token archive-list.csv categoryid"
        );
        unset($this->output);
        exec('php ' . $CFG->dirroot . '/local/eclass/archive/archive_courses.php https://fake-testing-url.com ' .
            '35ccac64ccde335ace2c246557482d32 empty-archive-list.csv 3', $this->output);
        $this->assertEquals($expectedoutput, $this->output);

        // Scenario: Junk input.
        $expectedoutput = "Input does not seem to be a valid archive course list.  Has the format changed?";
        unset($this->output);
        exec('php ' . $CFG->dirroot . '/local/eclass/archive/archive_courses.php https://fake-testing-url.com ' .
            '35ccac64ccde335ace2c246557482d32 invalid-archive-list.csv 3 -n', $this->output);
        $this->assertEquals($expectedoutput, strval($this->output[0]));

        $expectedoutput = array("Moving course 2 to category 3.");
        unset($this->output);
        exec('php ' . $CFG->dirroot . '/local/eclass/archive/archive_courses.php https://fake-testing-url.com ' .
            '35ccac64ccde335ace2c246557482d32 sample-archive-list.csv 3 -n', $this->output);
        $this->assertEquals($expectedoutput, $this->output);
    }
}
