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

global $CFG;

require_once($CFG->dirroot . '/mod/certificate/certificate_pdf_renderer.php');

/**
 * Unit tests for certificate_pdf_renderer
 * @group certificate
 */
class mod_certificate_certificate_pdf_renderer_testcase extends advanced_testcase {

    protected function setUp() {
        // Although preview in itself don't utilize any db transaction, we create
        // a course.
        $this->resetAfterTest(true);

        $this->course = $this->getDataGenerator()->create_course();
        $this->generator = $this->getDataGenerator()->get_plugin_generator('mod_certificate');
    }

    public function test_certificate_and_null_certificate_issues() {
        global $CFG;
        $this->setAdminUser();
        $certificate = $this->generator->create_instance(array('course' => $this->course->id,
            'borderstyle' => 'Fancy2-brown.jpg', 'certificatetype' => "A4_non_embedded"));

        $coursemodule = get_coursemodule_from_instance('certificate', $certificate->id);
        $certificaterenderer = new certificate_pdf_renderer($certificate, $this->course, $coursemodule);

        $this->assertNotEmpty($certificaterenderer);
    }


    public function test_certificate_and_certificate_issues() {
        $certificate = $this->generator->create_instance(array('course' => $this->course->id));

        $this->setAdminUser();
        global $USER;

        // For certificateissues specific test @see locallib_test.php, we will only test for existence.
        $coursemodule = get_coursemodule_from_instance('certificate', $certificate->id);
        $certificateissues = certificate_get_issue($this->course, $USER, $certificate, $coursemodule);
        $this->assertNotEmpty($coursemodule);
        $this->assertNotEmpty($certificateissues);

        $certificaterenderer = new certificate_pdf_renderer($certificate, $this->course, $coursemodule, $certificateissues);
        $this->assertNotEmpty($certificaterenderer);
    }

    public function test_certificate_print_teacher_names() {
        global $DB;

        $studentroleid = $DB->get_record('role', array('shortname' => 'student'), 'id')->id;
        $teacherroleid = $DB->get_record('role', array('shortname' => 'editingteacher'), 'id')->id;

        // Have a single teacher.
        $teacheruser = $this->getDataGenerator()->create_user(array(
            'email' => "sendmemoneyforgrades@bobineuniversity.ca",
            'username' => "dr_money_face"
        ));
        $this->getDataGenerator()->enrol_user($teacheruser->id, $this->course->id, $teacherroleid);

        // Have a single student.
        $studentuser = $this->getDataGenerator()->create_user(array(
            'email' => "coolbeans@Iranoutofjokereference.ca",
            'username' => "joke_depletion"
        ));

        $this->getDataGenerator()->enrol_user($studentuser->id, $this->course->id, $studentroleid);

        $certificate = $this->generator->create_instance(array('course' => $this->course->id,
            'printteacher' => 1));

        $this->setUser($studentuser);

        // For certificateissues specific test @see locallib_test.php, we will only test for existence.
        $coursemodule = get_coursemodule_from_instance('certificate', $certificate->id);
        $certificateissues = certificate_get_issue($this->course, $studentuser, $certificate, $coursemodule);

        $this->assertNotEmpty($coursemodule);
        $this->assertNotEmpty($certificateissues);

        $certificaterenderer = new certificate_pdf_renderer($certificate, $this->course, $coursemodule, $certificateissues);
        $this->assertNotEmpty($certificaterenderer);
    }

    /**
     * The following tests the certificate_type subclasses.
     *
     * This aids in the development of certificate_type class. Run this when developing a certificate_type plugin
     * to avoid regression.
     */

    public function test_certificate_type_a4_non_embedded() {
        $this->setAdminUser();
        $certificate = $this->generator->create_instance(array('course' => $this->course->id,
            'borderstyle' => 'Fancy2-brown.jpg', 'certificatetype' => "A4_non_embedded"));

        $coursemodule = get_coursemodule_from_instance('certificate', $certificate->id);
        $certificaterenderer = new certificate_pdf_renderer($certificate, $this->course, $coursemodule);

        $this->assertNotEmpty($certificaterenderer->get_pdf());
    }


    public function test_certificate_type_letter_non_embedded() {
        $this->setAdminUser();
        $certificate = $this->generator->create_instance(array('course' => $this->course->id,
            'borderstyle' => 'Fancy2-brown.jpg', 'certificatetype' => "letter_non_embedded"));

        $coursemodule = get_coursemodule_from_instance('certificate', $certificate->id);
        $certificaterenderer = new certificate_pdf_renderer($certificate, $this->course, $coursemodule);

        $this->assertNotEmpty($certificaterenderer->get_pdf());
    }

    /**
     * The following tests the certificate_type subclasses while printteacher attribute in certificate is true.
     *
     * This aids in the development of certificate_type class. Run this when developing a certificate_type plugin
     * to avoid regression.
     */


    public function test_certificate_type_a4_non_embedded_printteacher() {
        $this->setAdminUser();
        $certificate = $this->generator->create_instance(array('course' => $this->course->id,
            'borderstyle' => 'Fancy2-brown.jpg', 'certificatetype' => "A4_non_embedded", 'printteacher' => 1));

        $coursemodule = get_coursemodule_from_instance('certificate', $certificate->id);
        $certificaterenderer = new certificate_pdf_renderer($certificate, $this->course, $coursemodule);

        $this->assertNotEmpty($certificaterenderer->get_pdf());
    }

    public function test_certificate_type_letter_non_embedded_printteacher() {
        $this->setAdminUser();
        $certificate = $this->generator->create_instance(array('course' => $this->course->id,
            'borderstyle' => 'Fancy2-brown.jpg', 'certificatetype' => "letter_non_embedded", 'printteacher' => 1));

        $coursemodule = get_coursemodule_from_instance('certificate', $certificate->id);
        $certificaterenderer = new certificate_pdf_renderer($certificate, $this->course, $coursemodule);

        $this->assertNotEmpty($certificaterenderer->get_pdf());
    }
}