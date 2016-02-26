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
 * course_management steps definitions.
 *
 * @package    block_eclass_course_management
 * @category   test
 * @copyright  2014 Trevor Jones
 * @copyright  Joey Andres <jandres@ualberta.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use Behat\Behat\Context\Step\Given as Given,
    Behat\Mink\Exception\ElementNotFoundException as ElementNotFoundException;
use Behat\Gherkin\Node\TableNode as TableNode;

/**
 * Steps definitions to deal with eclass_course_management.
 *
 * @package    block_eclass_course_management
 * @category   test
 * @copyright  2014 Trevor Jones
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_eclass_course_management extends behat_base
{
    /**
     * @Given /^the following eclass_course_management values exist:$/
     */
    public function thefollowingeclasscoursemanagementvaluessexist(TableNode $table) {
        global $DB;
        $data = $table->getRows();
        $record = new stdClass();
        $record->courseid = $data[1][0];
        $record->startdate = $data[1][1];
        $record->enddate = $data[1][2];
        $record->timemodified = $data[1][2];
        $record->lastopened = $data[1][2];
        $record->lastclosed = $data[1][2];
        $DB->insert_record('eclass_course_management', $record);
    }

    /**
     * @Given /^Update the following eclass_course_management values:$/
     */
    public function updatethefollowingeclasscoursemanagementvalues(TableNode $table) {
        global $DB;
        $data = $table->getRows();
        $record = new stdClass();
        $record->id = $data[1][0];
        $record->courseid = $data[1][0];
        $record->startdate = $data[1][1];
        $record->enddate = $data[1][2];
        $record->timemodified = $data[1][2];
        $record->lastopened = $data[1][2];
        $record->lastclosed = $data[1][2];
        $DB->update_record('eclass_course_management', $record);
    }

    /**
     * @Given /^I set start date fields "([-+][0-9]*)" days from today$/
     */
    public function isetstartdatefieldsxdaysfromtoday($x) {
        $currenttime = time();
        $transformedtime = strtotime($x.' day', $currenttime);

        $month = date("F", $transformedtime );
        $dayofmonth = date("j", $transformedtime );
        $year = date("Y", $transformedtime );

        return array(
            new Given("I set the field \"start[day]\" to \"$dayofmonth\""),
            new Given("I set the field \"start[month]\" to \"$month\""),
            new Given("I set the field \"start[year]\" to \"$year\"")
        );
    }

    /**
     * @Given /^I set end date fields "([-+][0-9]*)" days from today$/
     */
    public function isetenddatefieldsxdaysfromtoday($x) {
        $currenttime = time();
        $transformedtime = strtotime($x.' day', $currenttime);

        $month = date("F", $transformedtime);
        $dayofmonth = date("j", $transformedtime);
        $year = date("Y", $transformedtime);

        return array(
            new Given("I set the field \"end[day]\" to \"$dayofmonth\""),
            new Given("I set the field \"end[month]\" to \"$month\""),
            new Given("I set the field \"end[year]\" to \"$year\"")
        );
    }

    /**
     * Written since original "I should see" can't handle wrapping string argument.
     *
     * @Given /^I should see:$/
     */
    public function ishouldsee($markdown) {
        $processedarg = preg_replace("/\n/", ' ', $markdown->getRaw());

        $nomatch = preg_match("/".$processedarg."/", $this->find("css", "body")->getHtml()) != 1;

        if ($nomatch) {
            throw new Exception("$markdown didn't match any string in the page.");
        }
    }

    /**
     * This is a debugging method and is used to dump html to terminal. Use this when
     * debugging something.
     * @Given /^Dump eclass html$/
     */
    public function dumphtml() {
        echo $this->find("css", "*")->getHtml();
    }
}