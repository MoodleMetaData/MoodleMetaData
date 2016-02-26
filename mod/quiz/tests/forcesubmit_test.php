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
 * Unit tests for (some of) mod/quiz/report/overview/report.php
 *
 * @package   mod_quiz
 * @category  phpunit
 * @copyright 2015 Trevor Jones
 * @license   http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/quiz/report/overview/report.php');
require_once($CFG->dirroot . '/mod/quiz/attemptlib.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');

class quiz_overview_report_testable extends quiz_overview_report
{
    public function forcesubmit_attempts($quiz, $dryrun = false,
                                         $groupstudents = array(), $attemptids = array()) {
        parent::forcesubmit_attempts($quiz, $dryrun, $groupstudents, $attemptids);
    }
}

/**
 * This class contains the test cases for the functions in overview/report.php.
 *
 * @copyright 2015 Trevor Jones tdjones@ualberta.ca
 * @license   http://www.gnu.org/copyleft/gpl.html GNU Public License
 */
class mod_quiz_overview_report_testcase extends advanced_testcase
{
    public function test_quiz_report_overview_report_forcesubmit_single_attempt() {
        global $DB;
        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $quizgenerator = $generator->get_plugin_generator('mod_quiz');
        $questiongenerator = $generator->get_plugin_generator('core_question');

        // Make a user to do the quiz.
        $user1 = $generator->create_user();
        $user2 = $generator->create_user();

        // Create our course.
        $course = $generator->create_course(array('visible' => true));

        // Create the quiz.
        $quiz = $quizgenerator->create_instance(array('course' => $course->id, 'visible' => true,
            'questionsperpage' => 0, 'grade' => 100.0,
            'sumgrades' => 2));

        // Create two questions.
        $cat = $questiongenerator->create_question_category();
        $saq = $questiongenerator->create_question('shortanswer', null, array('category' => $cat->id));
        $numq = $questiongenerator->create_question('numerical', null, array('category' => $cat->id));

        // Add the questions to the quiz.
        quiz_add_quiz_question($saq->id, $quiz);
        quiz_add_quiz_question($numq->id, $quiz);

        // Get a quiz object with user access overrides.
        $quizobj = quiz::create($quiz->id, $user1->id);
        $quizobj2 = quiz::create($quiz->id, $user2->id);

        // Start the attempt.
        $quba = question_engine::make_questions_usage_by_activity('mod_quiz', $quizobj->get_context());
        $quba->set_preferred_behaviour($quizobj->get_quiz()->preferredbehaviour);
        $quba2 = question_engine::make_questions_usage_by_activity('mod_quiz', $quizobj2->get_context());
        $quba2->set_preferred_behaviour($quizobj2->get_quiz()->preferredbehaviour);

        // Create a quiz attempt.
        $timenow = time();
        $attempt = quiz_create_attempt($quizobj, 1, false, $timenow, false, $user1->id);
        $attempt2 = quiz_create_attempt($quizobj2, 1, false, $timenow, false, $user2->id);

        // Start the attempt.
        quiz_start_new_attempt($quizobj, $quba, $attempt, 1, $timenow);
        quiz_attempt_save_started($quizobj, $quba, $attempt);
        quiz_start_new_attempt($quizobj2, $quba2, $attempt2, 1, $timenow);
        quiz_attempt_save_started($quizobj2, $quba2, $attempt2);

        // Answer first question and set it overdue.
        $tosubmit = array(1 => array('answer' => 'frog'));
        $tosubmit2 = array(1 => array('answer' => 'tiger'));

        $attemptobj = quiz_attempt::create($attempt->id);
        $attemptobj->process_submitted_actions($timenow, true, $tosubmit);
        $attemptobj2 = quiz_attempt::create($attempt2->id);
        $attemptobj2->process_submitted_actions($timenow, true, $tosubmit2);

        // Finish the attempt.
        $attemptobj = quiz_attempt::create($attempt->id);
        $this->assertTrue($attemptobj->has_response_to_at_least_one_graded_question());
        $attemptobj->process_abandon($timenow, false);

        // Re-load quiz attempt2 data.
        $attemptobj = quiz_attempt::create($attempt->id);
        $attemptobj2 = quiz_attempt::create($attempt2->id);

        // Check that the state of the attempt is as expected.
        $this->assertEquals(1, $attemptobj->get_attempt_number());
        $this->assertEquals(quiz_attempt::ABANDONED, $attemptobj->get_state());
        $this->assertEquals($user1->id, $attemptobj->get_userid());
        $this->assertTrue($attemptobj->has_response_to_at_least_one_graded_question());

        // Check that the state of the attempt2 is as expected.
        $this->assertEquals(1, $attemptobj2->get_attempt_number());
        $this->assertEquals(quiz_attempt::OVERDUE, $attemptobj2->get_state());
        $this->assertEquals($user2->id, $attemptobj2->get_userid());
        $this->assertTrue($attemptobj2->has_response_to_at_least_one_graded_question());

        // Force submit the attempts.
        $overviewreport = new quiz_overview_report_testable();
        $overviewreport->forcesubmit_attempts($quiz);

        // Check that it is now finished.
        $attemptobj = quiz_attempt::create($attempt->id);
        $this->assertEquals(quiz_attempt::FINISHED, $attemptobj->get_state());
        $attemptobj2 = quiz_attempt::create($attempt2->id);
        $this->assertEquals(quiz_attempt::FINISHED, $attemptobj2->get_state());
    }

    public function test_quiz_report_overview_report_forcesubmit_multiple_attempts() {
        global $DB;
        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $quizgenerator = $generator->get_plugin_generator('mod_quiz');
        $questiongenerator = $generator->get_plugin_generator('core_question');

        // Make a user to do the quiz.
        $user1 = $generator->create_user();

        // Create our course.
        $course = $generator->create_course(array('visible' => true));

        // Create the quiz.
        $quiz = $quizgenerator->create_instance(array('course' => $course->id, 'visible' => true,
            'questionsperpage' => 0, 'grade' => 100.0,
            'sumgrades' => 2));

        // Create two questions.
        $cat = $questiongenerator->create_question_category();
        $saq = $questiongenerator->create_question('shortanswer', null, array('category' => $cat->id));
        $numq = $questiongenerator->create_question('numerical', null, array('category' => $cat->id));

        // Add the questions to the quiz.
        quiz_add_quiz_question($saq->id, $quiz);
        quiz_add_quiz_question($numq->id, $quiz);

        // Get a quiz object with user access overrides.
        $quizobj = quiz::create($quiz->id, $user1->id);

        // Start the attempt.
        $quba = question_engine::make_questions_usage_by_activity('mod_quiz', $quizobj->get_context());
        $quba->set_preferred_behaviour($quizobj->get_quiz()->preferredbehaviour);

        // Create first quiz attempt.
        $timenow = time();
        $attempt = quiz_create_attempt($quizobj, 1, false, $timenow, false, $user1->id);

        // Start the attempt.
        quiz_start_new_attempt($quizobj, $quba, $attempt, 1, $timenow);
        quiz_attempt_save_started($quizobj, $quba, $attempt);

        // Answer first question and set it overdue.
        $tosubmit = array(1 => array('answer' => 'frog'));
        $attemptobj = quiz_attempt::create($attempt->id);
        $attemptobj->process_submitted_actions($timenow, false, $tosubmit);

        // Finish the attempt.
        $attemptobj = quiz_attempt::create($attempt->id);
        $this->assertTrue($attemptobj->has_response_to_at_least_one_graded_question());
        $attemptobj->process_finish($timenow, false);

        // Re-load quiz attempt data.
        $attemptobj = quiz_attempt::create($attempt->id);

        // Check that the state of the attempt is as expected.
        $this->assertEquals(1, $attemptobj->get_attempt_number());
        $this->assertEquals(quiz_attempt::FINISHED, $attemptobj->get_state());
        $this->assertEquals($user1->id, $attemptobj->get_userid());
        $this->assertTrue($attemptobj->has_response_to_at_least_one_graded_question());

        // Create second quiz attempt.
        $timenow = time();
        $attempt2 = quiz_create_attempt($quizobj, 2, $attempt, $timenow, false, $user1->id);

        // Start the attempt.
        quiz_start_new_attempt($quizobj, $quba, $attempt2, 2, $timenow);
        quiz_attempt_save_started($quizobj, $quba, $attempt2);

        // Answer first question and set it overdue.
        $tosubmit = array(1 => array('answer' => 'tiger'));
        $attemptobj2 = quiz_attempt::create($attempt2->id);
        $attemptobj2->process_submitted_actions($timenow, true, $tosubmit);

        // Finish the attempt.
        $attemptobj2 = quiz_attempt::create($attempt2->id);
        $this->assertTrue($attemptobj2->has_response_to_at_least_one_graded_question());

        // Re-load quiz attempt data.
        $attemptobj2 = quiz_attempt::create($attempt2->id);

        // Check that the state of the attempt is as expected.
        $this->assertEquals(2, $attemptobj2->get_attempt_number());
        $this->assertEquals(quiz_attempt::OVERDUE, $attemptobj2->get_state());
        $this->assertEquals($user1->id, $attemptobj2->get_userid());
        $this->assertTrue($attemptobj2->has_response_to_at_least_one_graded_question());

        // Force submit the attempts.
        $overviewreport = new quiz_overview_report_testable();
        $overviewreport->forcesubmit_attempts($quiz);

        // Check that it is now finished.
        $attemptobj = quiz_attempt::create($attempt->id);
        $this->assertEquals(quiz_attempt::FINISHED, $attemptobj->get_state());

        // Check that it is still finished.
        $attemptobj2 = quiz_attempt::create($attempt2->id);
        $this->assertEquals(quiz_attempt::FINISHED, $attemptobj2->get_state());

    }

    public function test_quiz_report_overview_report_forcesubmit_specific_attempt() {
        global $DB;
        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $quizgenerator = $generator->get_plugin_generator('mod_quiz');
        $questiongenerator = $generator->get_plugin_generator('core_question');

        // Make a user to do the quiz.
        $user1 = $generator->create_user();
        $user2 = $generator->create_user();
        $user3 = $generator->create_user();

        // Create our course.
        $course = $generator->create_course(array('visible' => true));

        // Create the quiz.
        $quiz = $quizgenerator->create_instance(array('course' => $course->id, 'visible' => true,
            'questionsperpage' => 0, 'grade' => 100.0,
            'sumgrades' => 2));

        // Create two questions.
        $cat = $questiongenerator->create_question_category();
        $saq = $questiongenerator->create_question('shortanswer', null, array('category' => $cat->id));
        $numq = $questiongenerator->create_question('numerical', null, array('category' => $cat->id));

        // Add the questions to the quiz.
        quiz_add_quiz_question($saq->id, $quiz);
        quiz_add_quiz_question($numq->id, $quiz);

        // Get a quiz object with user access overrides.
        $quizobj = quiz::create($quiz->id, $user1->id);
        $quizobj2 = quiz::create($quiz->id, $user2->id);
        $quizobj3 = quiz::create($quiz->id, $user3->id);

        // Start the attempt.
        $quba = question_engine::make_questions_usage_by_activity('mod_quiz', $quizobj->get_context());
        $quba->set_preferred_behaviour($quizobj->get_quiz()->preferredbehaviour);
        $quba2 = question_engine::make_questions_usage_by_activity('mod_quiz', $quizobj2->get_context());
        $quba2->set_preferred_behaviour($quizobj2->get_quiz()->preferredbehaviour);
        $quba3 = question_engine::make_questions_usage_by_activity('mod_quiz', $quizobj3->get_context());
        $quba3->set_preferred_behaviour($quizobj3->get_quiz()->preferredbehaviour);

        // Create a quiz attempt.
        $timenow = time();
        $attempt = quiz_create_attempt($quizobj, 1, false, $timenow, false, $user1->id);
        $attempt2 = quiz_create_attempt($quizobj2, 1, false, $timenow, false, $user2->id);
        $attempt3 = quiz_create_attempt($quizobj3, 1, false, $timenow, false, $user3->id);

        // Start the attempt.
        quiz_start_new_attempt($quizobj, $quba, $attempt, 1, $timenow);
        quiz_attempt_save_started($quizobj, $quba, $attempt);
        quiz_start_new_attempt($quizobj2, $quba2, $attempt2, 1, $timenow);
        quiz_attempt_save_started($quizobj2, $quba2, $attempt2);
        quiz_start_new_attempt($quizobj3, $quba3, $attempt3, 1, $timenow);
        quiz_attempt_save_started($quizobj3, $quba3, $attempt3);

        // Answer first question and set it overdue.
        $tosubmit = array(1 => array('answer' => 'frog'));
        $tosubmit2 = array(1 => array('answer' => 'tiger'));
        $tosubmit3 = array(1 => array('answer' => 'tiger'));

        $attemptobj = quiz_attempt::create($attempt->id);
        $attemptobj->process_submitted_actions($timenow, true, $tosubmit);
        $attemptobj2 = quiz_attempt::create($attempt2->id);
        $attemptobj2->process_submitted_actions($timenow, true, $tosubmit2);
        $attemptobj3 = quiz_attempt::create($attempt3->id);
        $attemptobj3->process_submitted_actions($timenow, true, $tosubmit3);

        // Finish the attempt.
        $attemptobj = quiz_attempt::create($attempt->id);
        $this->assertTrue($attemptobj->has_response_to_at_least_one_graded_question());
        $attemptobj->process_abandon($timenow, false);

        // Re-load quiz attempt2 data.
        $attemptobj = quiz_attempt::create($attempt->id);
        $attemptobj2 = quiz_attempt::create($attempt2->id);
        $attemptobj3 = quiz_attempt::create($attempt3->id);

        // Check that the state of the attempt is as expected.
        $this->assertEquals(1, $attemptobj->get_attempt_number());
        $this->assertEquals(quiz_attempt::ABANDONED, $attemptobj->get_state());
        $this->assertEquals($user1->id, $attemptobj->get_userid());
        $this->assertTrue($attemptobj->has_response_to_at_least_one_graded_question());

        // Check that the state of the attempt2 is as expected.
        $this->assertEquals(1, $attemptobj2->get_attempt_number());
        $this->assertEquals(quiz_attempt::OVERDUE, $attemptobj2->get_state());
        $this->assertEquals($user2->id, $attemptobj2->get_userid());
        $this->assertTrue($attemptobj2->has_response_to_at_least_one_graded_question());

        // Check that the state of the attempt3 is as expected.
        $this->assertEquals(1, $attemptobj3->get_attempt_number());
        $this->assertEquals(quiz_attempt::OVERDUE, $attemptobj3->get_state());
        $this->assertEquals($user3->id, $attemptobj3->get_userid());
        $this->assertTrue($attemptobj3->has_response_to_at_least_one_graded_question());

        // Force submit the attempts.
        $overviewreport = new quiz_overview_report_testable();
        $overviewreport->forcesubmit_attempts($quiz, false, array(),
            array($attempt->id, $attempt3->id));

        // Check that it is now finished.
        $attemptobj = quiz_attempt::create($attempt->id);
        $this->assertEquals(quiz_attempt::FINISHED, $attemptobj->get_state());
        $attemptobj2 = quiz_attempt::create($attempt2->id);
        $this->assertEquals(quiz_attempt::OVERDUE, $attemptobj2->get_state());
        $attemptobj3 = quiz_attempt::create($attempt3->id);
        $this->assertEquals(quiz_attempt::FINISHED, $attemptobj3->get_state());
    }
}
