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
 * Unit tests for email criteria search library.
 *
 * @package    local
 * @category   eclass/tests
 * @author     Anthony Radziszewski radzisze@ualberta.ca
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once(dirname(dirname(__FILE__)) . '/lib/email_criteria_search.php');
global $DB, $CFG;

class email_criteria_search_testcase extends advanced_testcase {

    public function test_email_search() {
        global $DB, $CFG;
        $this->resetAfterTest(true);

        // Create test data.
        $firstcourse = $this->getDataGenerator()->create_course();
        $secondcourse = $this->getDataGenerator()->create_course();

        $firstcategory = $this->getDataGenerator()->create_category();
        $secondcategory = $this->getDataGenerator()->create_category();

        $firstuser = $this->getDataGenerator()->create_user();
        $seconduser = $this->getDataGenerator()->create_user();

        $firstcohort = $this->getDataGenerator()->create_cohort();
        $secondcohort = $this->getDataGenerator()->create_cohort();

        $firstcourse->category = $firstcategory->id;
        $DB->update_record('course', $firstcourse);

        $secondcourse->category = $secondcategory->id;
        $DB->update_record('course', $secondcourse);

        $firstcohort->idnumber = 1400.99999;
        $DB->update_record('cohort', $firstcohort);

        $secondcohort->idnumber = 1402.99999;
        $DB->update_record('cohort', $secondcohort);

        $firstcohortenrol = new stdClass();
        $firstcohortenrol->courseid = $firstcourse->id;
        $firstcohortenrol->customint1 = $firstcohort->id;
        $DB->insert_record('enrol', $firstcohortenrol);

        $secondcohortenrol = new stdClass();
        $secondcohortenrol->courseid = $secondcourse->id;
        $secondcohortenrol->customint1 = $secondcohort->id;
        $DB->insert_record('enrol', $secondcohortenrol);

        $this->getDataGenerator()->enrol_user($firstuser->id, $firstcourse->id);
        $this->getDataGenerator()->enrol_user($seconduser->id, $secondcourse->id);

        $firstuser->lastaccess = 1417630663;
        $firstuser->email = "firstuser@email.com";
        $DB->update_record('user', $firstuser);

        $seconduser->lastaccess = 1417630889;
        $seconduser->email = "seconduser@email.com";
        $DB->update_record('user', $seconduser);

        // Test just category.
        $searcher = new email_search();
        $searcher->addcategory($firstcourse->category);
        $result = $searcher->getemails();
        $resultarray = $this->recordset_to_array($result);
        $this->assertEquals(1, count($resultarray));
        $this->assertEquals($firstuser->email, $resultarray[0]);
        $result->close();

        $searcher = new email_search();
        $searcher->addcategory($firstcourse->category);
        $searcher->addcategory($secondcourse->category);
        $result = $searcher->getemails();
        $resultarray = $this->recordset_to_array($result);
        $this->assertEquals(2, count($resultarray));
        $this->assertEquals($firstuser->email, $resultarray[0]);
        $this->assertEquals($seconduser->email, $resultarray[1]);
        $result->close();

        // Test just role.
        $searcher = new email_search();
        $roleids = $DB->get_record('role_assignments', array('userid' => $firstuser->id));
        $roleid = $roleids->roleid;
        $searcher->addrole($roleid);
        $result = $searcher->getemails();
        $resultarray = $this->recordset_to_array($result);
        $this->assertEquals($firstuser->email, $resultarray[0]);
        $result->close();

        $searcher = new email_search();
        $roleids = $DB->get_record('role_assignments', array('userid' => $firstuser->id));
        $roleid = $roleids->roleid;
        $searcher->addrole($roleid);
        $roleids = $DB->get_record('role_assignments', array('userid' => $seconduser->id));
        $roleid = $roleids->roleid;
        $searcher->addrole($roleid);
        $result = $searcher->getemails();
        $resultarray = $this->recordset_to_array($result);
        $this->assertEquals($firstuser->email, $resultarray[0]);
        $this->assertEquals($seconduser->email, $resultarray[1]);
        $result->close();

        // Test just courses.
        $searcher = new email_search();
        $searcher->addcourse($firstcourse->id);
        $result = $searcher->getemails();
        $resultarray = $this->recordset_to_array($result);
        $this->assertEquals(1, count($resultarray));
        $this->assertEquals($firstuser->email, $resultarray[0]);
        $result->close();

        $searcher = new email_search();
        $searcher->addcourse($firstcourse->id);
        $searcher->addcourse($secondcourse->id);
        $result = $searcher->getemails();
        $resultarray = $this->recordset_to_array($result);
        $this->assertEquals(2, count($resultarray));
        $this->assertEquals($firstuser->email, $resultarray[0]);
        $this->assertEquals($seconduser->email, $resultarray[1]);
        $result->close();

        // Test just lastaccess.
        $searcher = new email_search();
        $searcher->setlastaccess(1417630888);
        $result = $searcher->getemails();
        $resultarray = $this->recordset_to_array($result);
        $this->assertEquals(1, count($resultarray));
        $this->assertEquals($seconduser->email, $resultarray[0]);
        $result->close();

        $searcher = new email_search();
        $searcher->setlastaccess(1417630889);
        $result = $searcher->getemails();
        $resultarray = $this->recordset_to_array($result);
        $this->assertEquals(1, count($resultarray));
        $this->assertEquals($seconduser->email, $resultarray[0]);
        $result->close();

        $searcher = new email_search();
        $searcher->setlastaccess(1417630600);
        $result = $searcher->getemails();
        $resultarray = $this->recordset_to_array($result);
        $this->assertEquals(2, count($resultarray));
        $this->assertEquals($firstuser->email, $resultarray[0]);
        $this->assertEquals($seconduser->email, $resultarray[1]);
        $result->close();

        $searcher = new email_search();
        $searcher->setlastaccess(1517630999);
        $result = $searcher->getemails();
        $resultarray = $this->recordset_to_array($result);
        $this->assertEquals(0, count($resultarray));
        $result->close();

        $searcher = new email_search();
        $searcher->setlastaccess(0);
        $result = $searcher->getemails();
        $resultarray = $this->recordset_to_array($result);
        $this->assertEquals(2, count($resultarray));
        $this->assertEquals($firstuser->email, $resultarray[0]);
        $this->assertEquals($seconduser->email, $resultarray[1]);
        $result->close();

        $searcher = new email_search();
        $searcher->setlastaccess(1);
        $result = $searcher->getemails();
        $resultarray = $this->recordset_to_array($result);
        $this->assertEquals(2, count($resultarray));
        $this->assertEquals($firstuser->email, $resultarray[0]);
        $this->assertEquals($seconduser->email, $resultarray[1]);
        $result->close();

        // Test just term.
        $searcher = new email_search();
        $searcher->addterm(1400);
        $result = $searcher->getemails();
        $resultarray = $this->recordset_to_array($result);
        $this->assertEquals(1, count($resultarray));
        $this->assertEquals($firstuser->email, $resultarray[0]);
        $result->close();

        $searcher = new email_search();
        $searcher->addterm(1400);
        $searcher->addterm(1402);
        $result = $searcher->getemails();
        $resultarray = $this->recordset_to_array($result);
        $this->assertEquals(2, count($resultarray));
        $this->assertEquals($firstuser->email, $resultarray[0]);
        $this->assertEquals($seconduser->email, $resultarray[1]);
        $result->close();

        // All criteria together.
        $searcher = new email_search();
        $searcher->addterm(1400);
        $searcher->setlastaccess(1417630888);
        $roleids = $DB->get_record('role_assignments', array('userid' => $firstuser->id));
        $roleid = $roleids->roleid;
        $searcher->addrole($roleid);
        $searcher->addcategory($firstcourse->category);
        $searcher->addcourse($firstcourse->id);
        $result = $searcher->getemails();
        $resultarray = $this->recordset_to_array($result);
        $this->assertEquals(0, count($resultarray));
        $result->close();

        $searcher = new email_search();
        $searcher->addterm(1400);
        $searcher->setlastaccess(1417630663);
        $roleids = $DB->get_record('role_assignments', array('userid' => $firstuser->id));
        $roleid = $roleids->roleid;
        $searcher->addrole($roleid);
        $searcher->addcategory($firstcourse->category);
        $searcher->addcourse($firstcourse->id);
        $result = $searcher->getemails();
        $resultarray = $this->recordset_to_array($result);
        $this->assertEquals(1, count($resultarray));
        $this->assertEquals($firstuser->email, $resultarray[0]);
        $result->close();

        $searcher = new email_search();
        $searcher->setlastaccess(1417630663);
        $searcher->addterm(1400);
        $searcher->addterm(1402);
        $roleids = $DB->get_record('role_assignments', array('userid' => $firstuser->id));
        $roleid = $roleids->roleid;
        $searcher->addrole($roleid);
        $roleids = $DB->get_record('role_assignments', array('userid' => $seconduser->id));
        $roleid = $roleids->roleid;
        $searcher->addrole($roleid);
        $searcher->addcategory($firstcourse->category);
        $searcher->addcategory($secondcourse->category);
        $searcher->addcourse($firstcourse->id);
        $searcher->addcourse($secondcourse->id);
        $result = $searcher->getemails();
        $resultarray = $this->recordset_to_array($result);
        $this->assertEquals(2, count($resultarray));
        $this->assertEquals($firstuser->email, $resultarray[0]);
        $this->assertEquals($seconduser->email, $resultarray[1]);
        $result->close();
    }

    private function recordset_to_array($recordset) {
        $recordarray = array();
        foreach ($recordset as $record) {
            array_push($recordarray, $record->email);
        }
        return ($recordarray);
    }
}
