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
 * Tests for ip address review restrictions core change.
 *
 * @package    mod_quiz
 * @category   phpunit
 * @copyright  2014 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . '/mod/quiz/locallib.php');
require_once($CFG->dirroot . '/mod/quiz/accessmanager.php');

/**
 * Unit tests for ip address review restriction.
 *
 * @package    mod_quiz
 * @category   phpunit
 * @copyright  2014 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_quiz_ipreview extends advanced_testcase {

    /** This is the user object for the student */
    private $student;
    /** This is the user object for the teacher */
    private $teacher;
    /** This is the course object that holds the quiz */
    private $course;

    /**
     * Creates a quiz, completes an attempt, and then checks to see that the
     * ip address is properly restricting the quiz access.
     *
     */
    public function test_ip_review() {

        $this->resetAfterTest(true);

        $this->setup_course();
        list($attemptid, $quizid) = $this->create_quiz_attempt($this->course, $this->student);

        // Case 1: no ip restriction, student tries to review, should return false.
        $this->assertFalse($this->check_ipaddress_review($this->student, $attemptid));
        // Case 2: student IP not in subnet, should return message indicating access is restricted.
        $this->change_quiz_ipaddress($quizid, '129.128.99');
        $this->assertEquals($this->check_ipaddress_review($this->student, $attemptid),
                            get_string('subnetwrong', 'quizaccess_ipaddress'));
        // Case 3: teacher not in subnet, should return false: instructors bypass restriction.
        $this->assertFalse($this->check_ipaddress_review($this->teacher, $attemptid));

        /* It does not appear that I can test for an IP address in the subnet because the phpunit
         * client does not get a valid IP address.  It defaults to 0.0.0.0, which moodle functions
         * assume is invalid.  A behat test (when it's up and running) would be better.
         */
    }

    /**
     * This function sets up the course and enrolls a student and teacher.
     *
     */
    private function setup_course() {
        global $DB;

        $this->course = $this->getDataGenerator()->create_course();
        $this->teacher = $this->getDataGenerator()->create_user();
        $this->student = $this->getDataGenerator()->create_user();
        $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $this->assertNotEmpty($studentrole);
        $teacherrole = $DB->get_record('role', array('shortname' => 'editingteacher'));
        $this->assertNotEmpty($teacherrole);
        $this->getDataGenerator()->enrol_user($this->teacher->id, $this->course->id, $teacherrole->id, 'manual');
        $this->getDataGenerator()->enrol_user($this->student->id, $this->course->id, $studentrole->id, 'manual');
    }

    /**
     * This function is a copy of code taken from attempt_walkthrough_test.php
     * that creates a sample quiz and has a student complete the quiz.
     *
     * If the test suddenly stops working, grab the new code from that file, then
     * adjust to required inputs/outputs.
     *
     * @param object $course The course object to create the quiz in
     * @param object $student The student object that takes the quiz
     * @return array {attempt ID (int), quiz ID (int)}
     *
     */
    private function create_quiz_attempt($course, $student) {

        // Make a quiz.
        $quizgenerator = $this->getDataGenerator()->get_plugin_generator('mod_quiz');

        $quiz = $quizgenerator->create_instance(array('course' => $course->id, 'questionsperpage' => 0, 'grade' => 100.0,
                                                      'sumgrades' => 2));

        // Create a couple of questions.
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');

        $cat = $questiongenerator->create_question_category();
        $saq = $questiongenerator->create_question('shortanswer', null, array('category' => $cat->id));
        $numq = $questiongenerator->create_question('numerical', null, array('category' => $cat->id));

        // Add them to the quiz.
        quiz_add_quiz_question($saq->id, $quiz);
        quiz_add_quiz_question($numq->id, $quiz);

        $quizobj = quiz::create($quiz->id, $student->id);

        // Start the attempt.
        $quba = question_engine::make_questions_usage_by_activity('mod_quiz', $quizobj->get_context());
        $quba->set_preferred_behaviour($quizobj->get_quiz()->preferredbehaviour);

        $timenow = time();
        $attempt = quiz_create_attempt($quizobj, 1, false, $timenow, false, $student->id);

        quiz_start_new_attempt($quizobj, $quba, $attempt, 1, $timenow);

        quiz_attempt_save_started($quizobj, $quba, $attempt);

        // Process some responses from the student.
        $attemptobj = quiz_attempt::create($attempt->id);
        $prefix1 = $quba->get_field_prefix(1);
        $prefix2 = $quba->get_field_prefix(2);

        $tosubmit = array(1 => array('answer' => 'frog'),
                          2 => array('answer' => '3.14'));

        $attemptobj->process_submitted_actions($timenow, false, $tosubmit);

        // Finish the attempt.
        $attemptobj = quiz_attempt::create($attempt->id);
        $attemptobj->process_finish($timenow, false);

        // Re-load quiz attempt data.
        $attemptobj = quiz_attempt::create($attempt->id);

        return array($attempt->id, $quiz->id);
    }

    /**
     * This method changes the subnet restriction for a particular quiz.
     *
     * @param int $quizid The ID of the quiz to change the subnet for
     * @param string $subnet subnet string for valid IP range
     *
     */
    private function change_quiz_ipaddress($quizid, $subnet) {
        global $DB;

        $quizrecord = $DB->get_record('quiz', array('id' => $quizid));
        $quizrecord->subnet = $subnet;
        $DB->update_record('quiz', $quizrecord);
    }

    /**
     * This method changes to the desired user and attempts to review
     * the quiz.  The message is returned to the caller.
     *
     * @param object $user The user object for the person attempting to review
     * @param int $attemptid The ID of the attempt to review
     * @return mixed {false|message indicating reason}
     *
     */
    private function check_ipaddress_review($user, $attemptid) {

        $this->setUser($user);
        // Create new attempt object from ID to reload from database.
        $attemptobj = quiz_attempt::create($attemptid);
        $accessmanager = $attemptobj->get_access_manager(time());
        return $accessmanager->prevent_review_ipaddress();
    }

}