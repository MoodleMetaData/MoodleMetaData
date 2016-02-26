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
global $CFG;
require_once($CFG->dirroot.'/group/lib.php');
require_once($CFG->dirroot.'/blocks/skills_group/locallib.php');
require_once($CFG->dirroot.'/blocks/skills_group/classes/skills_group_setting.class.php');
require_once($CFG->dirroot.'/blocks/skills_group/classes/skills_group_student.class.php');

/**
 * This is the master table list for block_skills_group testing.
 *
 * I created a derived class here (from advanced_testcase) which all my test classes are
 * then derived from, so they all use the same setUp() routine.  This does slow down the
 * tests a bit but tests should not be that time sensitive anyway.
 *
 * @package    block_skills_group
 * @copyright  2014 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class skills_group_unit_test extends advanced_testcase {

    /** This is the course ID of the test course that is setup */
    protected $courseid;
    /** This is the grouping ID of the test group that is setup */
    protected $groupingid;
    /** This is the name of the test grouping */
    protected $groupingname;
    /** This is the feedback ID of the test group that is setup */
    protected $feedbackid;
    /** This is the name of the feedback activity */
    protected $feedbackname;
    /** This is the list of group IDs for the test groups */
    protected $groupids = array();
    /** This is the list of feedback IDs for the test feedback activity */
    protected $feedbackids = array();
    /** Test users */
    protected $users = array();
    /** Number of users */
    const NUMBEROFUSERS = 10;
    /** Number of users */
    const NUMBEROFGROUPS = 2;
    /** Maximum number of members in group */
    const MAXGROUPSIZE = 3;
    /** Number of items on feedback activity */
    const FEEDBACKITEMS = 3;

    /**
     * The setup function is used to place the DB in its initial state.
     *
     */
    protected function setUp() {

        // This needs to be here for the dummy test below.
        $this->resetAfterTest(true);
        // Create all of the test data.
        $this->create_course();
        $this->create_grouping();
        $this->create_groups();
        $this->create_feedback();
        $this->create_feedback_items();
        $this->answer_feedback();

        parent::setUp();
    }

    /**
     * Dummy test to avoid a warning.
     *
     */
    public function test_dummy() {
        $somevariable = 1;
    }

    /**********************************************************************
     * Helper functions are below:
     **********************************************************************/
    /**
     * This function creates the course that is used for testing.  The course is created,
     * along with a number of test users.  These test users are all enrolled in the course.
     *
     */
    protected function create_course() {

        $course = $this->getDataGenerator()->create_course();
        $this->courseid = $course->id;

        for ($i = 0; $i < self::NUMBEROFUSERS; $i++) {
            $this->users[] = $this->getDataGenerator()->create_user();
        }
        $this->enroll_users();
    }

    /**
     * This function enrolls the test users.
     *
     */
    protected function enroll_users() {
        global $DB;

        // Get role IDs by shortname.
        $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $this->assertNotEmpty($studentrole);
        // Enroll users.
        foreach ($this->users as $user) {
            $this->getDataGenerator()->enrol_user($user->id, $this->courseid, $studentrole->id, 'manual');
        }
    }

    /**
     * This function creates the grouping to be used while testing.
     *
     */
    protected function create_grouping() {

        $grouping = $this->getDataGenerator()->create_grouping(array('courseid' => $this->courseid));
        $this->groupingid = $grouping->id;
        $this->groupingname = $grouping->name;
    }

    /**
     * This function creates the groups for testing.  We enroll the last users first, rather than
     * the ones at the start.  This way I can use user0 for testing.
     *
     */
    protected function create_groups() {
        for ($i = 0; $i < self::NUMBEROFGROUPS; $i++) {
            $group = $this->getDataGenerator()->create_group(array('courseid' => $this->courseid));
            groups_assign_grouping($this->groupingid, $group->id);
            $this->groupids[] = $group->id;
            groups_add_member($group->id, $this->users[self::NUMBEROFUSERS - 2 * $i - 1]->id);
            groups_add_member($group->id, $this->users[self::NUMBEROFUSERS - 2 * $i - 2]->id);
        }
    }

    /**
     * This helper function returns the users array for students that weren't in the auto created groups.
     *
     */
    protected function get_ungrouped_studentids() {
        return array_slice($this->users, 0, self::NUMBEROFUSERS - 2 * self::NUMBEROFGROUPS);
    }

    /**
     * This function creates a feedback activity to be used while testing.
     *
     */
    protected function create_feedback() {

        $feedback = $this->getDataGenerator()->create_module('feedback', array('course' => $this->courseid));
        $this->feedbackid = $feedback->id;
        $this->feedbackname = $feedback->name;
    }

    /**
     * This function manually creates a few items for the feedback activity.  I dislike having to manually
     * create the records, but the feedback libraries and documentation are very poorly written.
     *
     * I tried feedback_create_item() in lib.php, but the postupdate() function won't work here.
     *
     */
    protected function create_feedback_items() {
        global $DB;

        for ($i = 1; $i <= self::FEEDBACKITEMS; $i++) {
            $data = new stdClass();
            $data->feedback = $this->feedbackid;
            $data->name = 'skill ' . $i . '?';
            // This is multiple choie with values 1 to 5.
            $data->presentation = 'r>>>>>1|2|3|4|5';
            $data->typ = 'multichoice';
            $data->hasvalue = 1;
            $data->position = $i;
            $this->feedbackids[] = $DB->insert_record('feedback_item', $data);
        }
    }

    /**
     * Similar to the above, this generates a set of respones to the feedback for all of the users.
     * First student answers {1, 1, 1}, second {2, 2, 2} and so on.  Results wrap at {5, 5, 5} so
     * that sixth student answers {1, 1, 1} and so on.
     *
     * This is the same approach as what is taken in mod/feedback/tests/events_test.php
     *
     */
    protected function answer_feedback() {
        global $DB;

        for ($i = 0; $i < self::NUMBEROFUSERS; $i++) {
            $response = new stdClass();
            $response->feedback = $this->feedbackid;
            $response->userid = $this->users[$i]->id;
            $response->anonymous_response = FEEDBACK_ANONYMOUS_NO;
            $completedid = $DB->insert_record('feedback_completed', $response);

            for ($j = 1; $j <= self::FEEDBACKITEMS; $j++) {
                $value = new stdClass();
                $value->course_id = $this->courseid;
                $value->item = $this->feedbackids[$j - 1];
                $value->completed = $completedid;
                $value->value = $i % 5 + 1;
                $valueid = $DB->insert_record('feedback_value', $value);
            }
        }
    }

    /**
     * This function configures the skills_group_setting class for further testing.  I do not call this
     * on the setUp() function here, but derived testing classes can do so as needed.
     *
     * @param      integer  $threshold   low/high score threshold
     * @param      integer  $date        date in unix epoch format
     * @param      integer  $allownaming whether students can name their groups (1) or not (0)
     */
    protected function configure_settings($threshold = 1, $date = null, $allownaming = 1) {

        $sgs = new skills_group_setting($this->courseid);
        $settings = $this->get_skills_group_settings($threshold, $date, $allownaming);
        $sgs->update_record($settings);

        // Set last student to have choice locked.
        $student = new skills_group_student($this->courseid, $this->users[self::NUMBEROFUSERS - 1]->id);
        $student->set_lock_choice(true);
    }

    /**
     * This function configures the skills group settings record for testing.  It is
     * possible to change the threshold and date, while the other settings are fixed
     * at compile time.
     *
     * @param      integer  $threshold   low/high score threshold
     * @param      integer  $date        date in unix epoch format
     * @param      integer  $allownaming whether students can name their groups (1) or not (0)
     * @return     object settings record with date added
     */
    protected function get_skills_group_settings($threshold = 1, $date = null, $allownaming = 1) {
        $settings = new stdClass;
        $settings->feedbacks = $this->feedbackid;
        $settings->groupings = $this->groupingid;
        $settings->maxsize = self::MAXGROUPSIZE;
        $settings->threshold = $threshold;
        $settings->datecheck = ($date === null) ? 0 : 1;
        $settings->date = $date;
        $settings->allownaming = $allownaming;
        return $settings;
    }

}