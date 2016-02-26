<?php

// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Certificate module data generator.
 *
 * @package    mod_certificate
 * @category   test
 * @author     Joey Andres <jandres@ualberta.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $CFG, $DB;

require_once($CFG->dirroot . '/mod/certificate/lib.php');
require_once($CFG->dirroot . '/mod/certificate/locallib.php');
require_once($CFG->dirroot. '/mod/certificate/certificate_pdf_renderer.php');

/**
 * Unit tests for lib.php
 * @group certificate
 */
class mod_certificate_lib_testcase extends advanced_testcase {

    protected function setUp() {
        $this->resetAfterTest(true);

        $this->course = $this->getDataGenerator()->create_course();
        $this->generator = $this->getDataGenerator()->get_plugin_generator('mod_certificate');
    }

    public function test_certificate_add_instance() {
        global $DB;

        $certificateobject = $this->generator->create_dummy_instance(array('course' => $this->course->id));

        $this->assertEquals(0, $DB->count_records('certificate'));
        certificate_add_instance($certificateobject);
        $this->assertEquals(1, $DB->count_records('certificate'));
    }

    public function test_certificate_update_instance() {
        global $DB;

        $certificateobject = $this->generator->create_dummy_instance(array('course' => $this->course->id));

        $certificateobject->id = $certificate_id =
            certificate_add_instance($certificateobject);
        $this->assertEquals(1, $DB->count_records('certificate'));

        $certificateobject->orientation = 'P';
        certificate_update_instance($certificateobject);

        $inserted_certificate_object = $DB->get_record('certificate', array("id" => $certificate_id));

        $certificate_object_array = (array)$certificateobject;
        $insertedcertificateobjectarray = (array)$inserted_certificate_object;

        // To account for database insertion transaction's nature to fill in for default if any,
        // the following assertion is made.
        $this->assertGreaterThanOrEqual(sizeof($certificateobject), sizeof($insertedcertificateobjectarray));

        foreach ($certificate_object_array as $attr => $attr_val) {
            $this->assertEquals($certificate_object_array[$attr], $insertedcertificateobjectarray[$attr]);
        }
    }

    public function test_certificate_delete_instance_no_entry() {
        global $DB;

        $this->assertEquals(false, certificate_delete_instance(1));
    }

    public function test_certificate_delete_instance_with_entry_no_course_module() {
        // This should only insert to db and doesn't insert couse module.
        $certificateobject = $this->generator->create_dummy_instance(array('course' => $this->course->id));
        $certificateobject->id = certificate_add_instance($certificateobject);

        $this->assertFalse(certificate_delete_instance($certificateobject->id));
    }

    public function test_certificate_delete_instance_with_entry() {
        // This should create certificate db entry as well as course module entry.
        $certificate = $this->generator->create_instance(array('course' => $this->course->id));

        $this->assertTrue(certificate_delete_instance($certificate->id));
    }

    public function test_certificate_delete_instance_with_entry_and_associated_certificate_issues() {
        global $DB;

        // This should create certificate db entry as well as course module entry.
        $certificate = $this->generator->create_instance(array('course' => $this->course->id));
        $coursemodule = get_coursemodule_from_instance('certificate', $certificate->id);

        $userarray = array();
        for($i = 0; $i < 10; $i++) {
            $userarray[] = $this->getDataGenerator()->create_user();
            $this->assertNotEmpty(certificate_get_issue($this->course, end($userarray), $certificate, $coursemodule));
        }

        // We should now have a certificate issues for each user (except admin and guest).
        $this->assertEquals(sizeof($userarray), $DB->count_records('certificate_issues'));

        $this->assertTrue(certificate_delete_instance($certificate->id));

        // The certificate_delete_instance should also delete all certificate_issues.
        $this->assertEquals(0, $DB->count_records('certificate_issues'));
    }

    public function test_certificate_reset_userdata_nullify_old_recipients() {
        global $DB;

        // This should create certificate db entry as well as course module entry.
        $certificate = $this->generator->create_instance(array('course' => $this->course->id));
        $coursemodule = get_coursemodule_from_instance('certificate', $certificate->id);
        $context = context_module::instance($coursemodule->id);

        $userarray = array();
        for($i = 0; $i < 10; $i++) {
            $userarray[] = $this->getDataGenerator()->create_user();
            $certificateissue = certificate_get_issue($this->course, end($userarray), $certificate, $coursemodule);

            // Save the files along the way.
            $filename = certificate_get_certificate_filename($certificate, $coursemodule, $this->course) . '.pdf';

            $cpr = new certificate_pdf_renderer($certificate, $this->course, $coursemodule);
            $pdf = $cpr->get_pdf();

            // PDF contents are now in $file_contents as a string.
            $filecontents = $pdf->Output('', 'S');

            certificate_save_pdf($filecontents, $certificate->id, $filename, $context->id);
        }

        // Assert that only one file exist. @see certificate_save_pdf
        $fs = get_file_storage();
        $component = 'mod_certificate';
        $filearea = 'issue';
        $files = $fs->get_area_files($context->id, $component, $filearea, $certificate->id);
        $certfiles = array();
        foreach($files as $key => $file) {
            $validfile = trim($file->get_filename()) != ".";
            if ($validfile) {
                $certfiles[] = $file;
            }
        }
        $this->assertEquals(1, sizeof($certfiles));

        $data = new stdClass;
        $data->courseid = $this->course->id;
        $data->reset_certificate = true;
        $data->timeshift = 0;

        $componentstr = get_string('modulenameplural', 'certificate');
        $statusarray = certificate_reset_userdata($data);
        $expectedstatus =
            array('component' => $componentstr, 'item' => get_string('removecert', 'certificate'),
                'error' => false);

        // Ensure that we have the expected return status.
        $this->assertContains($expectedstatus, $statusarray);

        // Ensure that all certificate recpients are no longer a recipient.
        $this->assertEquals(0, $DB->count_records('certificate_issues', array('certificateid' => $certificate->id)));

        // Ensure that all saved pdf files are deleted.
        $files = $fs->get_area_files($context->id, $component, $filearea, $certificate->id);
        $certfiles = array();
        foreach($files as $key => $file) {
            $validfile = trim($file->get_filename()) != ".";
            if ($validfile) {
                $certfiles[] = $file;
            }
        }
        $this->assertEquals(0, sizeof($certfiles));
    }

    public function test_certificate_reset_userdata_new_start_date() {
        global $DB;

        // This should create certificate db entry as well as course module entry.
        $certificate = $this->generator->create_instance(array('course' => $this->course->id));
        $coursemodule = get_coursemodule_from_instance('certificate', $certificate->id);
        $context = context_module::instance($coursemodule->id);

        $data = new stdClass;
        $data->courseid = $this->course->id;
        $data->reset_certificate = null;
        $data->timeshift = 100;  // Time difference since the start date and new start date (after reset).

        $componentstr = get_string('modulenameplural', 'certificate');
        $statusarray = certificate_reset_userdata($data);
        $expectedstatus =  array('component' => $componentstr, 'item' => get_string('datechanged'), 'error' => false);

        // Ensure that we have the expected return status.
        $this->assertContains($expectedstatus, $statusarray);

        $certificateaftertimeshift =
            $DB->get_record('certificate', array('id' => $certificate->id));

        // The following is not found in install.xml thus must be unset.
        unset($certificate->cmid);

        // Assert that new start date don't concern this course module.
        $this->assertEquals($certificate, $certificateaftertimeshift);
    }

    public function test_certificate_user_outline() {
        // This should create certificate db entry as well as course module entry.
        $certificate = $this->generator->create_instance(array('course' => $this->course->id));
        $coursemodule = get_coursemodule_from_instance('certificate', $certificate->id);

        $user = $this->getDataGenerator()->create_user();
        certificate_get_issue($this->course, $user, $certificate, $coursemodule);

        $useroutline = certificate_user_outline($this->course, $user, null, $certificate);
        $this->assertEquals(get_string('issued', 'certificate'), $useroutline->info);
        $this->assertNotEmpty($useroutline->time);
    }

    public function test_certificate_user_outline_unissued_user() {
        // This should create certificate db entry as well as course module entry.
        $certificate = $this->generator->create_instance(array('course' => $this->course->id));
        $coursemodule = get_coursemodule_from_instance('certificate', $certificate->id);

        $user = $this->getDataGenerator()->create_user();

        $useroutline = certificate_user_outline($this->course, $user, null, $certificate);
        $this->assertEquals(get_string('notissued', 'certificate'), $useroutline->info);
    }

    public function test_certificate_user_complete() {
        $certificate = $this->generator->create_instance(array('course' => $this->course->id));
        $coursemodule = get_coursemodule_from_instance('certificate', $certificate->id);

        $user = $this->getDataGenerator()->create_user();
        certificate_get_issue($this->course, $user, $certificate, $coursemodule);

        // This should be tested in a behat test. I'm just running it to catch
        // any exception if any (allowing us to detect regression later).
    }

    public function test_certificate_get_participants() {
        // This should create certificate db entry as well as course module entry.
        $certificate = $this->generator->create_instance(array('course' => $this->course->id));
        $coursemodule = get_coursemodule_from_instance('certificate', $certificate->id);

        $userarray = array();
        for($i = 0; $i < 10; $i++) {
            $userarray[] = $this->getDataGenerator()->create_user();
            $this->assertNotEmpty(certificate_get_issue($this->course, end($userarray), $certificate, $coursemodule));
        }

        $expecteduserids = array();
        foreach($userarray as $user) {
            $expecteduserids[] = $user->id;
        }

        $participantobject = certificate_get_participants($certificate->id);
        $realuserids = array();
        foreach($participantobject as $key => $user) {
            $realuserids[] = $user->id;
        }

        foreach($expecteduserids as $uid) {
            $this->assertContains($uid, $realuserids);
        }

        foreach($realuserids as $uid) {
            $this->assertContains($uid, $expecteduserids);
        }
    }
}