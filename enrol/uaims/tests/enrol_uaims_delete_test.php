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
 * Enrol uaims delete enrol method PHPunit tests
 *
 * @package    enrol_uaims
 * @category   phpunit
 * @copyright  2015 Greg Gibeau
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Moodle 2.4
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

class enrol_uaims_delete_testcase extends advanced_testcase
{
    /**
     * Test can_delete_instance
     */
    public function test_can_delete_instance() {
        global $DB, $CFG;
        require_once($CFG->dirroot . '/enrol/uaims/lib.php');

        $this->resetAfterTest();

        // Create users to enrol.
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        // User to use in IMS enrol.
        $user3 = $this->getDataGenerator()->create_user(array(
            'idnumber' => 'testuser3'
        ));

        $xmldoc = <<<DOC
<?xml version="1.0" encoding="utf-8"?>
<enterprise xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
<group recstatus="1"> <sourcedid> <source>AIS</source> <id>IDNUMBER</id> </sourcedid>
<grouptype> <typevalue>Course</typevalue> </grouptype>
<description> <short>IDNUMBER</short> <long>Long description</long> </description>
<org /> <timeframe> <begin>1420441200</begin> <end>1431237600</end> </timeframe> <enrollcontrol />
<relationship> <sourcedid> <source>AIS</source> <id>UOFAB-TEST</id> </sourcedid> </relationship>
<extension> <settings> <setting>visible</setting> <value>0</value> </settings>
<settings> <setting>format</setting> <value>topics</value> </settings> </extension></group>
<membership> <sourcedid> <source>AIS</source> <id>IDNUMBER</id> </sourcedid>
<member> <idtype>1</idtype> <sourcedid> <source>AIS</source> <id>testuser3</id> </sourcedid> <role recstatus="1" roletype="05">
<status>1</status> </role> </member> </membership></enterprise>
DOC;

        $category = new stdClass();
        $category->name = "test";
        $category->description = "UOFAB-TEST";
        $category->idnumber = "UOFAB-TEST";
        $category->timemodified = time();
        $DB->insert_record('course_categories', $category);

        $enrol = new enrol_uaims_plugin();

        // Make sure the course does not already exist.
        $course = $DB->get_record('course', array('idnumber' => "IDNUMBER"), $fields = '*', $strictness = IGNORE_MISSING);
        $this->assertEquals(false, $course);

        // Process the IMS Doc.
        $enrol->process_imsdoc($xmldoc);

        $course = $DB->get_record('course', array('idnumber' => "IDNUMBER"), $fields = '*', $strictness = MUST_EXIST);

        $manplugin = enrol_get_plugin('manual');

        $uaimsplugin = enrol_get_plugin('uaims');

        // Get the manager and teacher roles.
        $managerrole = $DB->get_record('role', array('shortname' => 'manager'));
        $this->assertNotEmpty($managerrole);
        $teacherrole = $DB->get_record('role', array('shortname' => 'teacher'));
        $this->assertNotEmpty($teacherrole);

        $maninstance1 = $DB->get_record('enrol', array('courseid' => $course->id, 'enrol' => 'manual'), '*', MUST_EXIST);
        $uaimsinstance1 = $DB->get_record('enrol', array('courseid' => $course->id, 'enrol' => 'uaims'), '*', MUST_EXIST);

        // Manually enrol users since the IMS plugin incorrectly assigns roles. CC does this as a separate process.
        $manplugin->enrol_user($maninstance1, $user1->id, $managerrole->id);
        $manplugin->enrol_user($maninstance1, $user2->id, $teacherrole->id);

        // Test that managers can delete the UAIM enrol method.
        $this->setUser($user1);
        $this->assertEquals(true, $uaimsplugin->can_delete_instance($uaimsinstance1));

        // Test that teachers can NOT delete the UAIM enrol method.
        $this->setUser($user2);
        $this->assertEquals(false, $uaimsplugin->can_delete_instance($uaimsinstance1));
    }
}