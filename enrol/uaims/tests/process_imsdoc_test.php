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
global $DB, $CFG;
require_once("{$CFG->dirroot}/config.php");
require_once("{$CFG->dirroot}/enrol/uaims/lib.php");

/**
 * Tests for process_imsdoc.
 *
 * This tests to see if process_imsdoc function is working correctly
 *
 * @package    uaims
 * @copyright  2014 Anthony Radziszewski radzisze@ualberta.ca
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class process_imsdoc_test extends advanced_testcase
{

    public function test_process_imsdoc_with_real_startend_dates() {
        global $DB, $CFG;
        $this->resetAfterTest(true);

        $xmldoc = <<<DOC
<?xml version="1.0" encoding="utf-8"?>
<enterprise xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
<group recstatus="1"> <sourcedid> <source>AIS</source> <id>IDNUMBER</id> </sourcedid>
<grouptype> <typevalue>Course</typevalue> </grouptype>
<description> <short>IDNUMBER</short> <long>Long description</long> </description>
<org /> <timeframe> <begin>1420106400</begin> <end>1430816400</end> </timeframe> <enrollcontrol />
<relationship> <sourcedid> <source>AIS</source> <id>UOFAB-TEST</id> </sourcedid> </relationship>
<extension> <settings> <setting>visible</setting> <value>0</value> </settings>
<settings> <setting>format</setting> <value>topics</value> </settings> </extension>  </group></enterprise>
DOC;

        $category = new stdClass();
        $category->name = "test";
        $category->description = "UOFAB-TEST";
        $category->idnumber = "UOFAB-TEST";
        $category->timemodified = time();
        $DB->insert_record('course_categories', $category);

        $enrol = new enrol_uaims_plugin();
        $enrol->process_imsdoc($xmldoc);

        $getcategory = $DB->get_record('course_categories', array('idnumber' => "UOFAB-TEST"), $fields = '*',
            $strictness = IGNORE_MISSING);
        $this->assertEquals($getcategory->name, "test");

        $course = $DB->get_record('course', array('idnumber' => "IDNUMBER"), $fields = '*', $strictness = IGNORE_MISSING);
        $this->assertEquals($course->fullname, "Long description");

        // Test the storage of the start and end dates.
        $coursemanage = $DB->get_record('eclass_course_management', array('courseid' => $course->id), $fields = '*',
            $strictness = IGNORE_MISSING);
        $this->assertEquals('1420106400', $coursemanage->startdate);
        $this->assertEquals('1430816400', $coursemanage->enddate);
    }

    /**
     * IMS docs cannot have a 0 begin-date if it has a non-zero end-date
     * @expectedException     Exception
     * @expectedExceptionMessage UAIMS: Course Creation without valid start or end date
     */
    public function test_process_imsdoc_with_0_start_date() {
        global $DB, $CFG;
        $this->resetAfterTest(true);

        $xmldoc = <<<DOC
<?xml version="1.0" encoding="utf-8"?>
<enterprise xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
<group recstatus="1"> <sourcedid> <source>AIS</source> <id>IDNUMBER</id> </sourcedid>
<grouptype> <typevalue>Course</typevalue> </grouptype>
<description> <short>IDNUMBER</short> <long>Long description</long> </description>
<org /> <timeframe> <begin>0</begin> <end>1420106400</end> </timeframe> <enrollcontrol />
<relationship> <sourcedid> <source>AIS</source> <id>UOFAB-TEST</id> </sourcedid> </relationship>
<extension> <settings> <setting>visible</setting> <value>0</value> </settings>
<settings> <setting>format</setting> <value>topics</value> </settings> </extension>  </group></enterprise>
DOC;

        $category = new stdClass();
        $category->name = "test";
        $category->description = "UOFAB-TEST";
        $category->idnumber = "UOFAB-TEST";
        $category->timemodified = time();
        $DB->insert_record('course_categories', $category);

        $enrol = new enrol_uaims_plugin();

        $course = $DB->get_record('course', array('idnumber' => "IDNUMBER"), $fields = '*', $strictness = IGNORE_MISSING);
        $this->assertEquals(false, $course);

        $enrol->process_imsdoc($xmldoc);
    }

    /**
     * IMS docs cannot have a 0 end-date if it has a non-zero start-date
     * @expectedException     Exception
     * @expectedExceptionMessage UAIMS: Course Creation without valid start or end date
     */
    public function test_process_imsdoc_with_0_end_date() {
        global $DB, $CFG;
        $this->resetAfterTest(true);

        $xmldoc = <<<DOC
<?xml version="1.0" encoding="utf-8"?>
<enterprise xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
<group recstatus="1"> <sourcedid> <source>AIS</source> <id>IDNUMBER</id> </sourcedid>
<grouptype> <typevalue>Course</typevalue> </grouptype>
<description> <short>IDNUMBER</short> <long>Long description</long> </description>
<org /> <timeframe> <begin>1420106400</begin> <end>0</end> </timeframe> <enrollcontrol />
<relationship> <sourcedid> <source>AIS</source> <id>UOFAB-TEST</id> </sourcedid> </relationship>
<extension> <settings> <setting>visible</setting> <value>0</value> </settings>
<settings> <setting>format</setting> <value>topics</value> </settings> </extension>  </group></enterprise>
DOC;

        $category = new stdClass();
        $category->name = "test";
        $category->description = "UOFAB-TEST";
        $category->idnumber = "UOFAB-TEST";
        $category->timemodified = time();
        $DB->insert_record('course_categories', $category);

        $enrol = new enrol_uaims_plugin();

        $course = $DB->get_record('course', array('idnumber' => "IDNUMBER"), $fields = '*', $strictness = IGNORE_MISSING);
        $this->assertEquals(false, $course);

        $enrol->process_imsdoc($xmldoc);
    }

    /**
     * If queue runner enabled, update the start/end dates when both provided and visibility.
     * @expectedException     dml_write_exception
     */
    public function test_process_imsdoc_with_invalid_startend_date() {
        global $DB, $CFG;
        $this->resetAfterTest(true);

        $xmldoc = <<<DOC
<?xml version="1.0" encoding="utf-8"?>
<enterprise xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
<group recstatus="1"> <sourcedid> <source>AIS</source> <id>IDNUMBER</id> </sourcedid>
<grouptype> <typevalue>Course</typevalue> </grouptype>
<description> <short>IDNUMBER</short> <long>Long description</long> </description>
<org /> <timeframe> <begin>1970-01-01</begin> <end>1970-12-31</end> </timeframe> <enrollcontrol />
<relationship> <sourcedid> <source>AIS</source> <id>UOFAB-TEST</id> </sourcedid> </relationship>
<extension> <settings> <setting>visible</setting> <value>0</value> </settings>
<settings> <setting>format</setting> <value>topics</value> </settings> </extension>  </group></enterprise>
DOC;

        $category = new stdClass();
        $category->name = "test";
        $category->description = "UOFAB-TEST";
        $category->idnumber = "UOFAB-TEST";
        $category->timemodified = time();
        $DB->insert_record('course_categories', $category);

        $enrol = new enrol_uaims_plugin();

        $course = $DB->get_record('course', array('idnumber' => "IDNUMBER"), $fields = '*', $strictness = IGNORE_MISSING);
        $this->assertEquals(false, $course);

        $enrol->process_imsdoc($xmldoc);
    }

    /**
     * If queue runner not enabled, do not update the start/end dates when both provided and visibility.
     */
    public function test_process_imsdoc_should_not_update_startend_dates() {
        global $DB, $CFG;
        $this->resetAfterTest(true);

        $xmldoc = <<<DOC
<?xml version="1.0" encoding="utf-8"?>
<enterprise xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
<group recstatus="1"> <sourcedid> <source>AIS</source> <id>IDNUMBER</id> </sourcedid>
<grouptype> <typevalue>Course</typevalue> </grouptype>
<description> <short>IDNUMBER</short> <long>Long description</long> </description>
<org /> <timeframe> <begin>1420106400</begin> <end>1430816400</end> </timeframe> <enrollcontrol />
<relationship> <sourcedid> <source>AIS</source> <id>UOFAB-TEST</id> </sourcedid> </relationship>
<extension> <settings> <setting>visible</setting> <value>0</value> </settings>
<settings> <setting>format</setting> <value>topics</value> </settings> </extension>  </group></enterprise>
DOC;
        $xmldoc2 = <<<DOC2
<?xml version="1.0" encoding="utf-8"?>
<enterprise xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
<group recstatus="1"> <sourcedid> <source>AIS</source> <id>IDNUMBER</id> </sourcedid>
<grouptype> <typevalue>Course</typevalue> </grouptype>
<description> <short>IDNUMBER</short> <long>Long description2</long> </description>
<org /> <timeframe> <begin>2420106400</begin> <end>2430816400</end> </timeframe> <enrollcontrol />
<relationship> <sourcedid> <source>AIS</source> <id>UOFAB-TEST</id> </sourcedid> </relationship>
<extension> <settings> <setting>visible</setting> <value>1</value> </settings>
<settings> <setting>format</setting> <value>topics</value> </settings> </extension>  </group></enterprise>
DOC2;
        $enrol = new enrol_uaims_plugin();
        $oldsetting = $enrol->get_config('enableqrvisibilitytoggle');

        // Enable updates from UAIMS documents.
        $enrol->set_config('enableqrvisibilitytoggle', true);

        $category = new stdClass();
        $category->name = "test";
        $category->description = "UOFAB-TEST";
        $category->idnumber = "UOFAB-TEST";
        $category->timemodified = time();
        $DB->insert_record('course_categories', $category);

        $enrol->process_imsdoc($xmldoc);

        $getcategory = $DB->get_record('course_categories', array('idnumber' => "UOFAB-TEST"), $fields = '*',
            IGNORE_MISSING);
        $this->assertEquals($getcategory->name, "test");
        $course = $DB->get_record('course', array('idnumber' => "IDNUMBER"), $fields = '*', IGNORE_MISSING);
        $this->assertEquals($course->fullname, "Long description");
        // Check the visibility has not been set.
        $this->assertEquals('0', $course->visible);
        // Test the storage of the start and end dates.
        $coursemanage = $DB->get_record('eclass_course_management', array('courseid' => $course->id), $fields = '*',
            IGNORE_MISSING);
        $this->assertEquals('1420106400', $coursemanage->startdate);
        $this->assertEquals('1430816400', $coursemanage->enddate);

        // Disable updates from UAIMS documents.
        $enrol->set_config('enableqrvisibilitytoggle', false);

        // Process the UAIMS document.
        $enrol->process_imsdoc($xmldoc2);

        $course = $DB->get_record('course', array('idnumber' => "IDNUMBER"), $fields = '*', IGNORE_MISSING);
        $this->assertEquals($course->fullname, "Long description2");

        // Check the visibility has not been updated.
        $this->assertEquals('0', $course->visible);

        // Test the storage of the start and end dates.
        $coursemanage = $DB->get_record('eclass_course_management', array('courseid' => $course->id), $fields = '*',
            IGNORE_MISSING);
        $this->assertEquals('1420106400', $coursemanage->startdate);
        $this->assertEquals('1430816400', $coursemanage->enddate);

        if ($oldsetting) {
            $enrol->set_config('enableqrvisibilitytoggle', $oldsetting);
        }
    }

    /**
     * If queue runner not enabled, do not update the start/end dates when neither provided and with visibility,
     * but still update course.
     */
    public function test_process_imsdoc_should_update_course_when_no_startend_dates() {
        global $DB, $CFG;
        $this->resetAfterTest(true);

        $xmldoc = <<<DOC
<?xml version="1.0" encoding="utf-8"?>
<enterprise xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
<group recstatus="1"> <sourcedid> <source>AIS</source> <id>IDNUMBER</id> </sourcedid>
<grouptype> <typevalue>Course</typevalue> </grouptype>
<description> <short>IDNUMBER</short> <long>Long description</long> </description>
<org /> <timeframe> <begin>1420106400</begin> <end>1430816400</end> </timeframe> <enrollcontrol />
<relationship> <sourcedid> <source>AIS</source> <id>UOFAB-TEST</id> </sourcedid> </relationship>
<extension> <settings> <setting>visible</setting> <value>0</value> </settings>
<settings> <setting>format</setting> <value>topics</value> </settings> </extension>  </group></enterprise>
DOC;
        $xmldoc2 = <<<DOC2
<?xml version="1.0" encoding="utf-8"?>
<enterprise xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
<group recstatus="1"> <sourcedid> <source>AIS</source> <id>IDNUMBER</id> </sourcedid>
<grouptype> <typevalue>Course</typevalue> </grouptype>
<description> <short>IDNUMBER</short> <long>Long description2</long> </description>
<org /> <enrollcontrol />
<relationship> <sourcedid> <source>AIS</source> <id>UOFAB-TEST</id> </sourcedid> </relationship>
<extension> <settings> <setting>visible</setting> <value>1</value> </settings>
<settings> <setting>format</setting> <value>topics</value> </settings> </extension>  </group></enterprise>
DOC2;
        $enrol = new enrol_uaims_plugin();
        $oldsetting = $enrol->get_config('enableqrvisibilitytoggle');

        // Disable updates from UAIMS documents.
        $enrol->set_config('enableqrvisibilitytoggle', false);

        $category = new stdClass();
        $category->name = "test";
        $category->description = "UOFAB-TEST";
        $category->idnumber = "UOFAB-TEST";
        $category->timemodified = time();
        $DB->insert_record('course_categories', $category);

        $enrol->process_imsdoc($xmldoc);

        $getcategory = $DB->get_record('course_categories', array('idnumber' => "UOFAB-TEST"), $fields = '*',
            IGNORE_MISSING);
        $this->assertEquals($getcategory->name, "test");

        $course = $DB->get_record('course', array('idnumber' => "IDNUMBER"), $fields = '*', IGNORE_MISSING);
        $this->assertEquals($course->fullname, "Long description");

        // Check the visibility has been set to 0.
        $this->assertEquals('0', $course->visible);

        // Test the storage of the start and end dates.
        $coursemanage = $DB->get_record('eclass_course_management', array('courseid' => $course->id), $fields = '*',
            IGNORE_MISSING);
        $this->assertEquals('1420106400', $coursemanage->startdate);
        $this->assertEquals('1430816400', $coursemanage->enddate);

        // Process the UAIMS document.
        $enrol->process_imsdoc($xmldoc2);

        $course = $DB->get_record('course', array('idnumber' => "IDNUMBER"), $fields = '*', IGNORE_MISSING);
        $this->assertEquals($course->fullname, "Long description2");

        // Check the visibility has not been updated.
        $this->assertEquals('0', $course->visible);

        // Test the storage of the start and end dates.
        $coursemanage = $DB->get_record('eclass_course_management', array('courseid' => $course->id), $fields = '*',
            IGNORE_MISSING);
        $this->assertEquals('1420106400', $coursemanage->startdate);
        $this->assertEquals('1430816400', $coursemanage->enddate);

        if ($oldsetting) {
            $enrol->set_config('enableqrvisibilitytoggle', $oldsetting);
        }
    }

    /**
     * Should update with bare minimum IMS doc
     */
    public function test_process_imsdoc_should_update_course_with_minimal_imsdoc() {
        global $DB, $CFG;
        $this->resetAfterTest(true);

        $xmldoc = <<<DOC
<?xml version="1.0" encoding="utf-8"?>
<enterprise xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
<group recstatus="1"> <sourcedid> <source>AIS</source> <id>IDNUMBER</id> </sourcedid>
<grouptype> <typevalue>Course</typevalue> </grouptype>
<description> <short>IDNUMBER</short> <long>Long description</long> </description>
<relationship> <sourcedid> <source>AIS</source> <id>UOFAB-TEST</id> </sourcedid> </relationship>
<extension> <settings> <setting>format</setting> <value>topics</value> </settings> </extension>  </group></enterprise>
DOC;
        $xmldoc2 = <<<DOC2
<?xml version="1.0" encoding="utf-8"?>
<enterprise xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
<group recstatus="1"> <sourcedid> <source>AIS</source> <id>IDNUMBER</id> </sourcedid>
<grouptype> <typevalue>Course</typevalue> </grouptype>
<description> <short>IDNUMBER</short> <long>Long description2</long> </description>
<relationship> <sourcedid> <source>AIS</source> <id>UOFAB-TEST</id> </sourcedid> </relationship>
<extension> </extension> </group></enterprise>
DOC2;
        $xmldoc3 = <<<DOC3
<?xml version="1.0" encoding="utf-8"?>
<enterprise xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
<group recstatus="1"> <sourcedid> <source>AIS</source> <id>IDNUMBER</id> </sourcedid>
<grouptype> <typevalue>Course</typevalue> </grouptype>
<description> <short>IDNUMBER</short> <long>Long description3</long> </description>
<relationship> <sourcedid> <source>AIS</source> <id>UOFAB-TEST</id> </sourcedid> </relationship>
<extension> </extension> </group></enterprise>
DOC3;
        $enrol = new enrol_uaims_plugin();
        $oldsetting = $enrol->get_config('enableqrvisibilitytoggle');

        // Disable updates from UAIMS documents.
        $enrol->set_config('enableqrvisibilitytoggle', false);

        $category = new stdClass();
        $category->name = "test";
        $category->description = "UOFAB-TEST";
        $category->idnumber = "UOFAB-TEST";
        $category->timemodified = time();
        $DB->insert_record('course_categories', $category);

        $enrol->process_imsdoc($xmldoc);

        $getcategory = $DB->get_record('course_categories', array('idnumber' => "UOFAB-TEST"), $fields = '*',
            IGNORE_MISSING);
        $this->assertEquals($getcategory->name, "test");

        $course = $DB->get_record('course', array('idnumber' => "IDNUMBER"), $fields = '*', IGNORE_MISSING);
        $this->assertEquals($course->fullname, "Long description");

        // Check the visibility has been set to 0.
        $this->assertEquals('0', $course->visible);

        // Test the storage of the start and end dates.
        $coursemanage = $DB->get_record('eclass_course_management', array('courseid' => $course->id), $fields = '*',
            IGNORE_MISSING);
        $this->assertFalse($coursemanage);

        // Process the UAIMS document.
        $enrol->process_imsdoc($xmldoc2);

        $course = $DB->get_record('course', array('idnumber' => "IDNUMBER"), $fields = '*', IGNORE_MISSING);
        $this->assertEquals($course->fullname, "Long description2");

        // Check the visibility has not been updated.
        $this->assertEquals('0', $course->visible);

        // Test the storage of the start and end dates.
        $coursemanage = $DB->get_record('eclass_course_management', array('courseid' => $course->id), $fields = '*',
            IGNORE_MISSING);
        $this->assertFalse($coursemanage);

        // Enable updates from UAIMS documents.
        $enrol->set_config('enableqrvisibilitytoggle', true);

        // Process the final IMS Doc.
        $enrol->process_imsdoc($xmldoc3);
        $course = $DB->get_record('course', array('idnumber' => "IDNUMBER"), $fields = '*', IGNORE_MISSING);
        $this->assertEquals($course->fullname, "Long description3");

        // Check the visibility is still set to 0.
        $this->assertEquals('0', $course->visible);

        // Test the storage of the start and end dates.
        $coursemanage = $DB->get_record('eclass_course_management', array('courseid' => $course->id), $fields = '*',
            IGNORE_MISSING);

        $this->assertFalse($coursemanage);

        if ($oldsetting) {
            $enrol->set_config('enableqrvisibilitytoggle', $oldsetting);
        }
    }

    /**
     * If queue runner enabled, update the start/end dates when both provided and visibility.
     */
    public function test_process_imsdoc_should_update_startend_dates() {
        global $DB, $CFG;
        $this->resetAfterTest(true);

        $xmldoc = <<<DOC
<?xml version="1.0" encoding="utf-8"?>
<enterprise xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
<group recstatus="1"> <sourcedid> <source>AIS</source> <id>IDNUMBER</id> </sourcedid>
<grouptype> <typevalue>Course</typevalue> </grouptype>
<description> <short>IDNUMBER</short> <long>Long description</long> </description>
<org /> <timeframe> <begin>1420106400</begin> <end>1430816400</end> </timeframe> <enrollcontrol />
<relationship> <sourcedid> <source>AIS</source> <id>UOFAB-TEST</id> </sourcedid> </relationship>
<extension> <settings> <setting>visible</setting> <value>0</value> </settings>
<settings> <setting>format</setting> <value>topics</value> </settings> </extension>  </group></enterprise>
DOC;
        $xmldoc2 = <<<DOC2
<?xml version="1.0" encoding="utf-8"?>
<enterprise xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
<group recstatus="1"> <sourcedid> <source>AIS</source> <id>IDNUMBER</id> </sourcedid>
<grouptype> <typevalue>Course</typevalue> </grouptype>
<description> <short>IDNUMBER</short> <long>Long description2</long> </description>
<org /> <timeframe> <begin>2420106400</begin> <end>2430816400</end> </timeframe> <enrollcontrol />
<relationship> <sourcedid> <source>AIS</source> <id>UOFAB-TEST</id> </sourcedid> </relationship>
<extension> <settings> <setting>visible</setting> <value>0</value> </settings>
<settings> <setting>format</setting> <value>topics</value> </settings> </extension>  </group></enterprise>
DOC2;
        $enrol = new enrol_uaims_plugin();
        $oldsetting = $enrol->get_config('enableqrvisibilitytoggle');

        // Enable updates from UAIMS documents.
        $enrol->set_config('enableqrvisibilitytoggle', true);

        $category = new stdClass();
        $category->name = "test";
        $category->description = "UOFAB-TEST";
        $category->idnumber = "UOFAB-TEST";
        $category->timemodified = time();
        $DB->insert_record('course_categories', $category);

        $enrol->process_imsdoc($xmldoc);

        $getcategory = $DB->get_record('course_categories', array('idnumber' => "UOFAB-TEST"), $fields = '*');
        $this->assertEquals($getcategory->name, "test");

        $course = $DB->get_record('course', array('idnumber' => "IDNUMBER"), $fields = '*');
        $this->assertEquals($course->fullname, "Long description");

        // Test the storage of the start and end dates.
        $coursemanage = $DB->get_record('eclass_course_management', array('courseid' => $course->id), $fields = '*', MUST_EXIST);
        $this->assertEquals('1420106400', $coursemanage->startdate);
        $this->assertEquals('1430816400', $coursemanage->enddate);

        // Process the UAIMS document.
        $enrol->process_imsdoc($xmldoc2);

        $course = $DB->get_record('course', array('idnumber' => "IDNUMBER"), $fields = '*');
        $this->assertEquals($course->fullname, "Long description2");

        // Test the storage of the start and end dates.
        $coursemanage = $DB->get_record('eclass_course_management', array('courseid' => $course->id), $fields = '*', MUST_EXIST);
        $this->assertEquals('2420106400', $coursemanage->startdate);
        $this->assertEquals('2430816400', $coursemanage->enddate);

        if ($oldsetting) {
            $enrol->set_config('enableqrvisibilitytoggle', $oldsetting);
        }
    }

    /**
     * If queue runner enabled, update the start/end dates when both provided, and visibility not provided.
     */
    public function test_process_imsdoc_should_update_startend_dates_without_visibility() {
        global $DB, $CFG;
        $this->resetAfterTest(true);

        $xmldoc = <<<DOC
<?xml version="1.0" encoding="utf-8"?>
<enterprise xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
<group recstatus="1"> <sourcedid> <source>AIS</source> <id>IDNUMBER</id> </sourcedid>
<grouptype> <typevalue>Course</typevalue> </grouptype>
<description> <short>IDNUMBER</short> <long>Long description</long> </description>
<org /> <timeframe> <begin>1420106400</begin> <end>1430816400</end> </timeframe> <enrollcontrol />
<relationship> <sourcedid> <source>AIS</source> <id>UOFAB-TEST</id> </sourcedid> </relationship>
<extension> <settings> <setting>format</setting> <value>topics</value> </settings> </extension>  </group></enterprise>
DOC;
        $xmldoc2 = <<<DOC2
<?xml version="1.0" encoding="utf-8"?>
<enterprise xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
<group recstatus="1"> <sourcedid> <source>AIS</source> <id>IDNUMBER</id> </sourcedid>
<grouptype> <typevalue>Course</typevalue> </grouptype>
<description> <short>IDNUMBER</short> <long>Long description2</long> </description>
<org /> <timeframe> <begin>2420106400</begin> <end>2430816400</end> </timeframe> <enrollcontrol />
<relationship> <sourcedid> <source>AIS</source> <id>UOFAB-TEST</id> </sourcedid> </relationship>
<extension> <settings> <setting>format</setting> <value>topics</value> </settings> </extension>  </group></enterprise>
DOC2;
        $enrol = new enrol_uaims_plugin();
        $oldsetting = $enrol->get_config('enableqrvisibilitytoggle');

        // Enable updates from UAIMS documents.
        $enrol->set_config('enableqrvisibilitytoggle', true);

        $category = new stdClass();
        $category->name = "test";
        $category->description = "UOFAB-TEST";
        $category->idnumber = "UOFAB-TEST";
        $category->timemodified = time();
        $DB->insert_record('course_categories', $category);

        $enrol->process_imsdoc($xmldoc);

        $getcategory = $DB->get_record('course_categories', array('idnumber' => "UOFAB-TEST"), $fields = '*');
        $this->assertEquals($getcategory->name, "test");

        $course = $DB->get_record('course', array('idnumber' => "IDNUMBER"), $fields = '*');
        $this->assertEquals($course->fullname, "Long description");

        // Test the storage of the start and end dates.
        $coursemanage = $DB->get_record('eclass_course_management', array('courseid' => $course->id), $fields = '*', MUST_EXIST);
        $this->assertEquals('1420106400', $coursemanage->startdate);
        $this->assertEquals('1430816400', $coursemanage->enddate);

        // Process the UAIMS document.
        $enrol->process_imsdoc($xmldoc2);

        $course = $DB->get_record('course', array('idnumber' => "IDNUMBER"), $fields = '*');
        $this->assertEquals($course->fullname, "Long description2");

        // Test the storage of the start and end dates.
        $coursemanage = $DB->get_record('eclass_course_management', array('courseid' => $course->id), $fields = '*', MUST_EXIST);
        $this->assertEquals('2420106400', $coursemanage->startdate);
        $this->assertEquals('2430816400', $coursemanage->enddate);

        if ($oldsetting) {
            $enrol->set_config('enableqrvisibilitytoggle', $oldsetting);
        }
    }

    /**
     * IMS docs cannot have an empty end-date if it has a non-zero start-date
     * @expectedException     Exception
     * @expectedExceptionMessage UAIMS: Course Creation without valid start or end date
     */
    public function test_process_imsdoc_cant_lack_enddate() {
        global $DB, $CFG;
        $this->resetAfterTest(true);

        $xmldoc = <<<DOC
<?xml version="1.0" encoding="utf-8"?>
<enterprise xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
<group recstatus="1"> <sourcedid> <source>AIS</source> <id>IDNUMBER</id> </sourcedid>
<grouptype> <typevalue>Course</typevalue> </grouptype>
<description> <short>IDNUMBER</short> <long>Long description</long> </description>
<org /> <timeframe> <begin>1420106400</begin> </timeframe> <enrollcontrol />
<relationship> <sourcedid> <source>AIS</source> <id>UOFAB-TEST</id> </sourcedid> </relationship>
<extension> <settings> <setting>visible</setting> <value>0</value> </settings>
<settings> <setting>format</setting> <value>topics</value> </settings> </extension>  </group></enterprise>
DOC;

        $enrol = new enrol_uaims_plugin();
        $oldsetting = $enrol->get_config('enableqrvisibilitytoggle');

        // Enable updates from UAIMS documents.
        $enrol->set_config('enableqrvisibilitytoggle', true);

        $category = new stdClass();
        $category->name = "test";
        $category->description = "UOFAB-TEST";
        $category->idnumber = "UOFAB-TEST";
        $category->timemodified = time();
        $DB->insert_record('course_categories', $category);

        // Process missing end date.
        $enrol->process_imsdoc($xmldoc);
        if ($oldsetting) {
            $enrol->set_config('enableqrvisibilitytoggle', $oldsetting);
        }
    }

    /**
     * IMS docs cannot have an empty start-date if it has a non-zero end-date
     * @expectedException     Exception
     * @expectedExceptionMessage UAIMS: Course Creation without valid start or end date
     */
    public function test_process_imsdoc_cant_lack_startdate() {
        global $DB, $CFG;
        $this->resetAfterTest(true);

        $xmldoc2 = <<<DOC2
<?xml version="1.0" encoding="utf-8"?>
<enterprise xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
<group recstatus="1"> <sourcedid> <source>AIS</source> <id>IDNUMBER</id> </sourcedid>
<grouptype> <typevalue>Course</typevalue> </grouptype>
<description> <short>IDNUMBER</short> <long>Long description2</long> </description>
<org /> <timeframe> <end>2430816400</end> </timeframe> <enrollcontrol />
<relationship> <sourcedid> <source>AIS</source> <id>UOFAB-TEST</id> </sourcedid> </relationship>
<extension> <settings> <setting>visible</setting> <value>0</value> </settings>
<settings> <setting>format</setting> <value>topics</value> </settings> </extension>  </group></enterprise>
DOC2;
        $enrol = new enrol_uaims_plugin();
        $oldsetting = $enrol->get_config('enableqrvisibilitytoggle');

        // Enable updates from UAIMS documents.
        $enrol->set_config('enableqrvisibilitytoggle', true);

        $category = new stdClass();
        $category->name = "test";
        $category->description = "UOFAB-TEST";
        $category->idnumber = "UOFAB-TEST";
        $category->timemodified = time();
        $DB->insert_record('course_categories', $category);

        // Process missing start date.
        $enrol->process_imsdoc($xmldoc2);

        if ($oldsetting) {
            $enrol->set_config('enableqrvisibilitytoggle', $oldsetting);
        }
    }


    /**
     * Tests for the enrol_uaims process_cohort_group_node function.
     *
     * Test to make sure that the cohort is created in the correct
     * category context.
     *
     * @package    enrol_uaims
     * @copyright  2014 Anthony Radziszewski radzisze@ualberta.ca
     * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
     */
    public function test_process_cohort_group_node() {
        global $DB, $CFG;
        $this->resetAfterTest(true);

        $xmldoc = <<<DOC
<?xml version="1.0" encoding="utf-8"?>
<enterprise xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
<group recstatus="1">    <sourcedid>      <id>test_cohort_id</id>    </sourcedid>    <grouptype>
<typevalue>Cohort</typevalue>    </grouptype>       <description>      <short>Test Cohort</short>
<long>Test Cohort</long>    </description>    <org />    <timeframe>      <begin />      <end />
</timeframe>    <enrollcontrol />    <relationship>      <sourcedid>        <id>UOFAB-AU</id>
</sourcedid>    </relationship>  </group></enterprise>
DOC;
        $enrol = new enrol_uaims_plugin();

        $category = new stdClass();
        $category->name = "test";
        $category->description = "UOFAB-AU";
        $category->idnumber = "UOFAB-AU";
        $category->timemodified = time();
        $category->id = $DB->insert_record('course_categories', $category);
        $classname = context_helper::get_class_for_level(CONTEXT_COURSECAT);
        $category->context = $classname::instance($category->id, IGNORE_MISSING);
        $category->context->mark_dirty();
        $DB->update_record('course_categories', $category);
        fix_course_sortorder();

        $enrol->process_imsdoc($xmldoc);

        // Make sure the cohort exists.
        $getcohort = $DB->get_record('cohort', array('idnumber' => "test_cohort_id"), $fields = '*');
        $this->assertEquals($getcohort->idnumber,  "test_cohort_id");
        $this->assertEquals($getcohort->visible,  0);

        // Make sure the cohort contextid matches the id from the context table for the specified category.
        $getcategory = $DB->get_record('course_categories', array('idnumber' => "UOFAB-AU"), $fields = '*');
        $this->assertEquals($getcategory->description, "UOFAB-AU");
        $getcontext = $DB->get_record('context', array('contextlevel' => 40, 'instanceid' => $getcategory->id), $fields = '*');
        $this->assertEquals($getcontext->instanceid, $getcategory->id);
        $this->assertEquals($getcontext->id, $getcohort->contextid);
    }

    /**
     * Test the case in which the course is created outside uaims (doesn't create a corresponding eclass_course_management
     * entry) then process_imsdoc is then called with rec=1 (new course), this should instead update the existing course.
     */
    public function test_process_course_created_outside_uaims_and_have_no_eclass_course_management_entry_ims_rec_1() {
        global $DB, $CFG;
        $this->resetAfterTest(true);

        $xmldoc = <<<DOC
<?xml version="1.0" encoding="utf-8"?>
<enterprise xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
<group recstatus="1"> <sourcedid> <source>AIS</source> <id>IDNUMBER</id> </sourcedid>
<grouptype> <typevalue>Course</typevalue> </grouptype>
<description> <short>IDNUMBER</short> <long>Long description</long> </description>
<org /> <timeframe> <begin>1420106400</begin> <end>1420106410</end> </timeframe> <enrollcontrol />
<relationship> <sourcedid> <source>AIS</source> <id>UOFAB-TEST</id> </sourcedid> </relationship>
<extension> <settings> <setting>visible</setting> <value>0</value> </settings>
<settings> <setting>format</setting> <value>topics</value> </settings> </extension>  </group></enterprise>
DOC;

        // Dummy category.
        $category = new stdClass();
        $category->name = "test";
        $category->description = "UOFAB-TEST";
        $category->idnumber = "UOFAB-TEST";
        $category->timemodified = time();
        $category->id = $DB->insert_record('course_categories', $category);

        // Dummy course.
        $course = new stdClass();
        $course->category = $category->id;
        $course->sortorder = 0;
        $course->fullname = "Long description";
        $course->shortname = "IDNUMBER-HARPER-SEAL-IS-THE-DOGEST-SPECIES";
        $course->idnumber = "IDNUMBER";
        $course->summary = "summaryformat";
        $course->showgrades = 1;
        $course->format = 'topics';
        $course->visible = 0;
        $course->timecreated = time();
        $course->startdate = time();
        $course->sortorder = 0;
        $course->timemodified = 0;
        $course->id = $DB->insert_record('course', $course);

        // Process missing end date.
        $enrol = new enrol_uaims_plugin();
        $enrol->process_imsdoc($xmldoc);

        // Confirm that a corresponding eclass_course_management entry is created.
        $coursemanagementexist =
            $DB->get_record('eclass_course_management', array('courseid' => $course->id), $fields = 'id',
            $strictness = IGNORE_MISSING) != false;

        // Although rec=1 means insert, this will default to update in the case of
        // course create outside uaims. Thus, other values in $xmldoc should update the course.
        $updatedcourse = $DB->get_record('course', array('id' => $course->id));
        $this->assertEquals('IDNUMBER', $updatedcourse->shortname);
        $this->assertEquals('Long description', $updatedcourse->fullname);
        $this->assertEquals('IDNUMBER', $updatedcourse->idnumber);

        $this->assertTrue($coursemanagementexist);
    }
}