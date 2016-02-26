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
 * @license    http://www.gnu.org/copyleft/gpllib_.html GNU GPL v3 or later
 */

global $CFG, $DB;

require_once($CFG->dirroot . '/mod/certificate/lib.php');
require_once($CFG->dirroot . '/mod/certificate/locallib.php');
require_once($CFG->dirroot. '/mod/certificate/certificate_pdf_renderer.php');

/**
 * Unit tests for locallib
 * @group certificate
 */
class mod_certificate_locallib_testcase extends advanced_testcase
{
    protected function setUp()
    {
        $this->resetAfterTest(true);

        $category = $this->getDataGenerator()->create_category();
        $this->course = $this->getDataGenerator()->create_course(array('name' => 'Bobine University', 'category' => $category->id));
        $this->generator = $this->getDataGenerator()->get_plugin_generator('mod_certificate');
    }

    public function test_certificate_get_teachers()
    {
        global $DB;

        $certificate = $this->generator->create_instance(array('course' => $this->course->id));
        $coursemodule = get_coursemodule_from_instance('certificate', $certificate->id);

        $studentroleid = $DB->get_record('role', array('shortname' => 'student'), 'id')->id;
        $teacherroleid = $DB->get_record('role', array('shortname' => 'editingteacher'), 'id')->id;

        $teacheruserarray = array();
        for ($i = 0; $i < 10; $i++) {
            $teacheruserarray[] =
                $this->getDataGenerator()->create_user(array(
                    'email' => "teacherdoge$i@dogeversity.doge",
                    'username' => "Dr. doge$i",
                ));

            // Enrol the user as a teacher.
            $this->getDataGenerator()->enrol_user(end($teacheruserarray)->id, $this->course->id, $teacherroleid);
        }

        // Enrol a single student and issue his/her a certificate.
        $studentuser = $this->getDataGenerator()->create_user(array(
            'email' => "studentdoge@dogeversity.doge",
            'username' => "dogemanorwomen"
        ));

        $this->getDataGenerator()->enrol_user($studentuser->id, $this->course->id, $studentroleid);
        certificate_get_issue($this->course, $studentuser, $certificate, $coursemodule);

        $certificateteacherarray = certificate_get_teachers(null, $studentuser, $coursemodule, $coursemodule);

        // Acquire the ids (not all attributes are equal considering db transaction can have auto values).
        $teacheruserids = array_map(create_function('$t', 'return $t->id;'), $teacheruserarray);
        $certificateteacherids = array_map(create_function('$c', 'return $c->id;'), $certificateteacherarray);

        /**
         * Ensure that two arrays have one-to-one correspondence, that is each
         * is a subset of each other.
         */
        $emptyarray = array();
        $this->assertEquals(array_diff($teacheruserids, $certificateteacherids), $emptyarray);
    }

    public function test_certificate_email_teachers()
    {
        global $DB;

        $certificate = $this->generator->create_instance(array('course' => $this->course->id));
        $coursemodule = get_coursemodule_from_instance('certificate', $certificate->id);

        $studentroleid = $DB->get_record('role', array('shortname' => 'student'), 'id')->id;
        $teacherroleid = $DB->get_record('role', array('shortname' => 'editingteacher'), 'id')->id;

        $teacheruserarray = array();
        for ($i = 0; $i < 10; $i++) {
            $teacheruserarray[] =
                $this->getDataGenerator()->create_user(array(
                    'email' => "teacherdoge$i@dogeversity.doge",
                    'username' => "Dr. doge$i",
                ));

            // Enrol the user as a teacher.
            $this->getDataGenerator()->enrol_user(end($teacheruserarray)->id, $this->course->id, $teacherroleid);
        }

        // Enrol a single student and issue his/her a certificate.
        $studentuser = $this->getDataGenerator()->create_user(array(
            'email' => "studentdoge@dogeversity.doge",
            'username' => "dogemanorwomen"
        ));

        $this->getDataGenerator()->enrol_user($studentuser->id, $this->course->id, $studentroleid);
        $certificateissues = certificate_get_issue($this->course, $studentuser, $certificate, $coursemodule);
        $this->setUser($studentuser);

        unset_config('noemailever');
        $sink = $this->redirectEmails();

        $certificate->emailteachers = true;  // Temporarily make it true.
        certificate_email_teachers($this->course, $certificate, $certificateissues, $coursemodule);

        $messages = $sink->get_messages();
        $this->assertEquals(count($teacheruserarray), count($messages));

        // Verify to/from emails
        foreach ($messages as $message) {
            $this->assertEquals($studentuser->email, $message->from);
        }

        foreach ($teacheruserarray as $user) {
            $useremailisrecipient = false;
            foreach ($messages as $message) {
                if ($useremailisrecipient = ($message->to == $user->email)) {
                    break;
                }
            }

            $this->assertTrue($useremailisrecipient);
        }
    }

    public function test_certificate_email_others()
    {
        global $DB, $USER;

        $certificate = $this->generator->create_instance(array('course' => $this->course->id));
        $coursemodule = get_coursemodule_from_instance('certificate', $certificate->id);
        $certificateissues = certificate_get_issue($this->course, $USER, $certificate, $coursemodule);

        $certificate->emailothers = "joeshmoe@shmoe.shmoe, harperseallooksdogerthandoge@doge.doge";
        $emailarray = explode(',', $certificate->emailothers);

        unset_config('noemailever');
        $sink = $this->redirectEmails();

        certificate_email_others($this->course, $certificate, $certificateissues, $coursemodule);

        $messages = $sink->get_messages();
        $this->assertEquals(count($emailarray), count($messages));

        foreach ($emailarray as $email) {
            $emailisarecipient = false;
            foreach ($messages as $message) {
                if ($emailisarecipient = (trim($email) == trim($message->to))) {
                    break;
                }
            }

            $this->assertTrue($emailisarecipient);
        }
    }

    public function test_certificate_email_teachers_text()
    {
        global $CFG, $USER;

        $certificate = $this->generator->create_instance(array('course' => $this->course->id));
        $coursemodule = get_coursemodule_from_instance('certificate', $certificate->id);
        $certificateissues = certificate_get_issue($this->course, $USER, $certificate, $coursemodule);

        $info = new stdClass;
        $info->student = fullname($USER);
        $info->course = format_string($this->course->fullname, true);
        $info->certificate = format_string($certificate->name, true);
        $info->url = $CFG->wwwroot . '/mod/certificate/report.php?id=' . $coursemodule->id;

        // Just ensure that there is a message.
        $this->assertGreaterThan(0, strlen(certificate_email_teachers_text($info)));
    }

    public function test_certificate_email_teachers_html()
    {
        global $CFG, $USER;

        $certificate = $this->generator->create_instance(array('course' => $this->course->id));
        $coursemodule = get_coursemodule_from_instance('certificate', $certificate->id);
        certificate_get_issue($this->course, $USER, $certificate, $coursemodule);

        $info = new stdClass;
        $info->student = fullname($USER);
        $info->course = format_string($this->course->fullname, true);
        $info->certificate = format_string($certificate->name, true);
        $info->url = $CFG->wwwroot . '/mod/certificate/report.php?id=' . $coursemodule->id;

        // Just ensure that there is a message.
        $this->assertGreaterThan(0, strlen(certificate_email_teachers_html($info)));
    }

    public function test_certificate_email_student()
    {
        global $CFG, $USER;

        $certificate = $this->generator->create_instance(array('course' => $this->course->id));
        $coursemodule = get_coursemodule_from_instance('certificate', $certificate->id);
        $certificateissues = certificate_get_issue($this->course, $USER, $certificate, $coursemodule);

        $this->setAdminUser();

        require_once("$CFG->dirroot/mod/certificate/certificate_pdf_renderer.php");
        $cpr = new certificate_pdf_renderer($certificate, $this->course, $coursemodule);
        $pdf = $cpr->get_pdf();

        // PDF contents are now in $file_contents as a string.
        $filecontents = $pdf->Output('', 'S');

        $filename = certificate_get_certificate_filename($certificate, $coursemodule, $this->course) . '.pdf';

        $context = context_module::instance($coursemodule->id);

        unset_config('noemailever');
        $sink = $this->redirectEmails();

        certificate_email_student($this->course, $certificate, $certificateissues, $context, $filecontents, $filename);

        $messages = $sink->get_messages();
        $this->assertEquals(1, count($messages));
        $this->assertEquals($USER->email, $messages[0]->to);
    }

    public function test_certificate_save_pdf()
    {
        global $CFG, $USER;
        $this->setAdminUser();

        $certificate = $this->generator->create_instance(array('course' => $this->course->id));
        $coursemodule = get_coursemodule_from_instance('certificate', $certificate->id);
        $certificateissues = certificate_get_issue($this->course, $USER, $certificate, $coursemodule);
        $context = context_module::instance($coursemodule->id);

        require_once("$CFG->dirroot/mod/certificate/certificate_pdf_renderer.php");
        $cpr = new certificate_pdf_renderer($certificate, $this->course, $coursemodule);
        $pdf = $cpr->get_pdf();
        $pdfstring = $pdf->Output('', 'S');

        certificate_save_pdf($pdfstring, $certificateissues->id, 'dummyfile.pdf', $context->id);

        $fs = get_file_storage();
        $component = 'mod_certificate';
        $filearea = 'issue';
        $files = $fs->get_area_files($context->id, $component, $filearea, $certificateissues->id);
        $certfilenames = array();
        foreach ($files as $key => $file) {
            $validfile = trim($file->get_filename()) != ".";
            if ($validfile) {
                $certfilenames[] = $file->get_filename();
            }
        }

        $this->assertContains('dummyfile.pdf', $certfilenames);
    }

    public function test_certificate_print_user_files()
    {
        global $CFG, $USER;
        $this->setAdminUser();

        $certificate = $this->generator->create_instance(array('course' => $this->course->id));
        $coursemodule = get_coursemodule_from_instance('certificate', $certificate->id);
        $certificateissues = certificate_get_issue($this->course, $USER, $certificate, $coursemodule);
        $context = context_module::instance($coursemodule->id);

        require_once("$CFG->dirroot/mod/certificate/certificate_pdf_renderer.php");
        $cpr = new certificate_pdf_renderer($certificate, $this->course, $coursemodule);
        $pdf = $cpr->get_pdf();
        $pdfstring = $pdf->Output('', 'S');

        $filename = "dummyfile.pdf";
        certificate_save_pdf($pdfstring, $certificateissues->id, $filename, $context->id);

        $this->assertTrue(
            !!preg_match("/$filename/i", certificate_print_user_files($certificate, $USER->id, $context->id)));
    }

    public function test_certificate_get_issue()
    {
        global $USER, $DB;
        $this->setAdminUser();

        $certificate = $this->generator->create_instance(array('course' => $this->course->id));
        $coursemodule = get_coursemodule_from_instance('certificate', $certificate->id);
        $certificateissue = certificate_get_issue($this->course, $USER, $certificate, $coursemodule);

        $insertedcertificateissue = $DB->get_record('certificate_issues', array('certificateid' => $certificate->id));

        // Note that numbers of attributes in $insertedcertificateissue >= of $certifcate due to db
        // transactions default values.
        foreach ((array)$certificateissue as $attr => $attrval) {
            $this->assertEquals($attrval, $insertedcertificateissue->$attr);
        }
    }

    public function test_certificate_get_issues()
    {
        global $DB;

        $certificate = $this->generator->create_instance(array('course' => $this->course->id));
        $coursemodule = get_coursemodule_from_instance('certificate', $certificate->id);

        $studentroleid = $DB->get_record('role', array('shortname' => 'student'), 'id')->id;
        $teacherroleid = $DB->get_record('role', array('shortname' => 'editingteacher'), 'id')->id;

        // Have a single teacher.
        $teacheruser = $this->getDataGenerator()->create_user(array(
            'email' => "sendmemoneyforgrades@bobineuniversity.ca",
            'username' => "dr_money_face"
        ));

        $this->getDataGenerator()->enrol_user($teacheruser->id, $this->course->id, $teacherroleid);

        $this->setUser($teacheruser);

        $studentuserarray = array();
        for ($i = 0; $i < 10; $i++) {
            $studentuserarray[] =
                $this->getDataGenerator()->create_user(array(
                    'email' => "joeshmoethe$i@illuminati.shmoe",
                    'username' => "joeshmoethe$i",
                ));

            // Enrol the user as a student.
            $this->getDataGenerator()->enrol_user(end($studentuserarray)->id, $this->course->id, $studentroleid);

            // Issue a certificate.
            certificate_get_issue($this->course, end($studentuserarray), $certificate, $coursemodule);
        }

        $users = certificate_get_issues($certificate->id, "ci.timecreated ASC", false, $coursemodule);

        foreach ($users as $user) {
            $userfound = false;
            foreach ($studentuserarray as $student) {
                if ($userfound = ($student->id == $user->id)) {
                    break;
                }
            }

            $this->assertTrue($userfound);
        }
    }

    public function test_certificate_get_attempts()
    {
        global $DB;

        $certificate = $this->generator->create_instance(array('course' => $this->course->id));
        $coursemodule = get_coursemodule_from_instance('certificate', $certificate->id);

        $studentroleid = $DB->get_record('role', array('shortname' => 'student'), 'id')->id;
        $teacherroleid = $DB->get_record('role', array('shortname' => 'editingteacher'), 'id')->id;

        // Have a single teacher.
        $teacheruser = $this->getDataGenerator()->create_user(array(
            'email' => "sendmemoneyforgrades@bobineuniversity.ca",
            'username' => "dr_money_face"
        ));
        $this->getDataGenerator()->enrol_user($teacheruser->id, $this->course->id, $teacherroleid);

        $studentuser = $this->getDataGenerator()->create_user(array(
            'email' => "coolbeans@Iranoutofjokereference.ca",
            'username' => "joke_depletion"
        ));

        $this->getDataGenerator()->enrol_user($studentuser->id, $this->course->id, $studentroleid);

        // Prior to any issue, the certificate_get_attempts should return false.
        $this->setUser($studentuser);
        $this->assertFalse(certificate_get_attempts($certificate->id));

        // Issue a certificate multiple times.
        $certificateissue01 = certificate_get_issue($this->course, $studentuser, $certificate, $coursemodule);
        $certificateissue02 = certificate_get_issue($this->course, $studentuser, $certificate, $coursemodule);

        // Set the current user to student since certificate_get_attempts relies on the
        // global variable $USER.
        $this->setUser($studentuser);
        // This is inconsistent with certificate_get_issue which is the function
        // used to acquire issue for a user. The same function that ensure there is
        // only one certificate issued per user.
        $attempts = certificate_get_attempts($certificate->id);
        $this->assertEquals(1, count($attempts));
        foreach ($attempts as $attempt) {
            $this->assertContains($attempt->id, array($certificateissue01->id, $certificateissue02->id));
        }
    }

    public function test_certificate_get_course_time()
    {
        // TODO: This seems like its gonna take awhile. I'll save this for last.
    }

    public function test_certificate_get_mods()
    {
        global $COURSE;

        $this->setAdminUser();
        $this->generator->create_instance(array('course' => $this->course->id, 'section' => 1));

        // Since certificate_god_mods relies on $COURSE, we need to change the course
        // where we actually have a course module.
        $oldcourse = $COURSE;
        $COURSE = $this->course;
        // TODO: The user needs to have a graded activity (see the function code itself).
        //$this->assertEquals(1, count(certificate_get_mods()));
        $COURSE = $oldcourse;
    }

    public function test_certificate_get_ordinal_number_suffix()
    {
        $this->assertEquals('th', certificate_get_ordinal_number_suffix(0));
        $this->assertEquals('st', certificate_get_ordinal_number_suffix(1));
        $this->assertEquals('nd', certificate_get_ordinal_number_suffix(2));
        $this->assertEquals('rd', certificate_get_ordinal_number_suffix(3));
        $this->assertEquals('th', certificate_get_ordinal_number_suffix(12));
    }

    public function test_certificate_get_grade()
    {
        // TODO: related to test_certificate_get_mods, solve this and that is solve.
    }
}