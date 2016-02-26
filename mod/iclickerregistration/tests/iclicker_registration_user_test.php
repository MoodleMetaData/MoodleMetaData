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
 * Certificate module data generator.
 *
 * @package    mod_certificate
 * @category   test
 * @author     Joey Andres <jandres@ualberta.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $CFG, $DB;

require_once($CFG->dirroot . '/mod/iclickerregistration/lib.php');
require_once($CFG->dirroot . '/mod/iclickerregistration/locallib.php');

$testiclickerid = "14131E19";

/**
 * Unittest for the class iclicker_registration_user.
 * @group iclickerregistration
 */
class mod_iclicker_registration_user_testcase extends advanced_testcase {
    protected function setUp() {
        $this->resetAfterTest(true);

        $category = $this->getDataGenerator()->create_category();
        $this->course1 = $this->getDataGenerator()->create_course(array('name' => 'JoeSatrianiPhase',
            'category' => $category->id));
        $this->course2 = $this->getDataGenerator()->create_course(array('name' => 'EarlyMorning80sPhase',
            'category' => $category->id));
        $this->generator = $this->getDataGenerator()->get_plugin_generator('mod_iclickerregistration');
        $this->iclickerregistration1 = $this->generator->create_instance(array('course' => $this->course1->id));
        $this->iclickerregistration2 = $this->generator->create_instance(array('course' => $this->course2->id));
    }

    protected function enrol_student_user($course,
                                          $idnumber = "me",
                                          $email = "imuniqueandbeatiful@narcissist.narc") {
        global $DB;
        $username = $idnumber;

        // Enrol a single student and issue his/her a certificate.
        $studentroleid = $DB->get_record('role', array('shortname' => 'student'), 'id')->id;
        $studentuser = $this->getDataGenerator()->create_user(array(
            'email' => $email,
            'username' => $username,
            'idnumber' => $idnumber
        ));
        $this->getDataGenerator()->enrol_user($studentuser->id, $course->id, $studentroleid);

        return $studentuser;
    }

    /**
     * Helper for registering iclicker.
     * @param $idnumber
     * @param $iclickerid
     * @throws invalid_iclicker_id
     */
    protected function register_iclicker($idnumber, $iclickerid) {
        global $iru;

        $iclickerobj = new stdClass;
        $iclickerobj->idnumber = $idnumber;
        $iclickerobj->iclickerid = $iclickerid;
        $iru->register_iclicker_id($iclickerobj);
    }

    public function test_is_user_already_registered() {
        global $iru, $testiclickerid;
        $user = $this->enrol_student_user($this->course1);

        // Currently, $user don't have an iclicker register, test that this is the case.
        $this->assertEquals(false, $iru->is_user_already_registered($user));

        // Register $user with an iclicker and the user should now be registered.
        $iclickerobj = new stdClass;
        $iclickerobj->idnumber = $user->idnumber;
        $iclickerobj->iclickerid = $testiclickerid;
        $iru->register_iclicker_id($iclickerobj);
        $this->assertEquals(true, $iru->is_user_already_registered($user));
    }

    public function test_is_user_already_registered_by_idnumber() {
        global $iru, $testiclickerid;
        $user = $this->enrol_student_user($this->course1);

        // Currently, $user don't have an iclicker register, test that this is the case.
        $this->assertEquals(false, $iru->is_user_already_registered_by_idnumber($user->idnumber));

        // Register $user with an iclicker and the user should now be registered.
        $iclickerobj = new stdClass;
        $iclickerobj->idnumber = $user->idnumber;
        $iclickerobj->iclickerid = $testiclickerid;
        $iru->register_iclicker_id($iclickerobj);
        $this->assertEquals(true, $iru->is_user_already_registered_by_idnumber($user->idnumber));
    }

    public function test_is_iclicker_id_already_registered() {
        global $iru, $testiclickerid;
        $user = $this->enrol_student_user($this->course1);

        // Currently, $user don't have an iclicker register, test that this is the case.
        $this->assertEquals(false,  $iru->is_iclicker_id_already_registered($testiclickerid));

        // Register $user with an iclicker and the user should now be registered.
        $iclickerobj = new stdClass;
        $iclickerobj->idnumber = $user->idnumber;
        $iclickerobj->iclickerid = $testiclickerid;
        $iru->register_iclicker_id($iclickerobj);
        $this->assertEquals(true, $iru->is_iclicker_id_already_registered($testiclickerid));
    }

    public function test_get_iclicker_by_iclicker_id() {
        global $iru, $testiclickerid;
        $user = $this->enrol_student_user($this->course1);

        // Currently, $user don't have an iclicker register, test that this is the case.
        $this->assertEquals(false, $iru->get_iclicker_by_iclicker_id($testiclickerid));

        // Register $user with an iclicker and the user should now be registered.
        $iclickerobj = new stdClass;
        $iclickerobj->idnumber = $user->idnumber;
        $iclickerobj->iclickerid = $testiclickerid;
        $iru->register_iclicker_id($iclickerobj);

        $iclickerobjnew = $iru->get_iclicker_by_iclicker_id($testiclickerid);

        // Since $iclickerobj is a subset of $iclickerobj_new's keys, let's get rid
        // of extra keys.
        $iclickerarraynew = array_intersect_key((array)$iclickerobj, (array)$iclickerobjnew);

        $this->assertEquals((array)$iclickerobj, $iclickerarraynew);
    }

    public function test_get_iclicker() {
        global $iru, $testiclickerid;
        $user = $this->enrol_student_user($this->course1);

        // Currently, $user don't have an iclicker register, test that this is the case.
        $this->assertEquals(false, $iru->get_iclicker($user));

        // Register $user with an iclicker and the user should now be registered.
        $iclickerobj = new stdClass;
        $iclickerobj->idnumber = $user->idnumber;
        $iclickerobj->iclickerid = $testiclickerid;
        $iru->register_iclicker_id($iclickerobj);

        $iclickerobjnew = $iru->get_iclicker($user);

        // Since $iclickerobj is a subset of $iclickerobj_new's keys, let's get rid
        // of extra keys.
        $iclickerarraynew = array_intersect_key((array)$iclickerobj, (array)$iclickerobjnew);

        $this->assertEquals((array)$iclickerobj, $iclickerarraynew);
    }

    public function test_get_iclicker_by_ccid() {
        global $iru, $testiclickerid;
        $user = $this->enrol_student_user($this->course1);

        // Currently, $user don't have an iclicker register, test that this is the case.
        $this->assertEquals(false, $iru->get_iclicker_by_idnumber($user->idnumber));

        // Register $user with an iclicker and the user should now be registered.
        $iclickerobj = new stdClass;
        $iclickerobj->idnumber = $user->idnumber;
        $iclickerobj->iclickerid = $testiclickerid;
        $iru->register_iclicker_id($iclickerobj);

        $iclickerobjnew = $iru->get_iclicker_by_idnumber($user->idnumber);

        // Since $iclickerobj is a subset of $iclickerobj_new's keys, let's get rid
        // of extra keys.
        $iclickerarraynew = array_intersect_key((array)$iclickerobj, (array)$iclickerobjnew);

        $this->assertEquals((array)$iclickerobj, $iclickerarraynew);
    }

    public function test_get_iclickers() {
        global $iru, $testiclickerid;
        $user = $this->enrol_student_user($this->course1);

        // Currently, $user don't have an iclicker register, test that this is the case.
        $this->assertEquals(array(), $iru->get_iclickers($user));

        // Register $user with an iclicker and the user should now be registered.
        $iclickerobj = new stdClass;
        $iclickerobj->idnumber = $user->idnumber;
        $iclickerobj->iclickerid = $testiclickerid;
        $iru->register_iclicker_id($iclickerobj);

        // Ensure that we get "same" iclickerobj (see comment below for "same").
        $iclickerobjnew = $iru->get_iclickers($user);
        $iclickerobjnew = array_shift($iclickerobjnew);

        // Since $iclickerobj is a subset of $iclickerobj_new's keys, let's get rid
        // of extra keys.
        $iclickerarraynew = array_intersect_key((array)$iclickerobj, (array)$iclickerobjnew);

        $this->assertEquals((array)$iclickerobj, $iclickerarraynew);
    }

    /**
     * Tests iclicker_registration_user::is_iclicker_id_duplicate_in_course for
     * basic cases.
     */
    public function test_is_iclicker_id_duplicate_in_course_basic_cases() {
        global $iru, $testiclickerid;

        $user1 = $this->enrol_student_user($this->course1, 'me1');
        $user2 = $this->enrol_student_user($this->course1, 'me2');

        // Non-duplicate case.
        $this->register_iclicker($user1->idnumber, $testiclickerid);
        $this->assertFalse($iru->is_iclicker_id_duplicate_in_course('14131E19', $user1->idnumber, $this->course1->id));
        // Duplicate case.
        $this->register_iclicker($user2->idnumber, $testiclickerid);  // Register same id.
        $this->assertTrue($iru->is_iclicker_id_duplicate_in_course($testiclickerid, $user1->idnumber, $this->course1->id));
    }

    /**
     * Tests iclicker_registration_user::is_iclicker_id_duplicate_in_course for
     * edge cases.
     */
    public function test_is_iclicker_id_duplicate_in_course_edge_cases() {
        global $iru, $testiclickerid;

        $user1 = $this->enrol_student_user($this->course1, 'me1');
        $user2 = $this->enrol_student_user($this->course2, 'me2');

        // Edge case 1: Same iclicker in different courses and different users shouldn't
        //              be counted as different.
        $this->register_iclicker($user1->idnumber, $testiclickerid);
        $this->register_iclicker($user2->idnumber, $testiclickerid);

        // No duplicate error should show in the users respective course.
        $this->assertFalse($iru->is_iclicker_id_duplicate_in_course($testiclickerid, $user1->idnumber, $this->course1->id));
        $this->assertFalse($iru->is_iclicker_id_duplicate_in_course($testiclickerid, $user2->idnumber, $this->course2->id));
    }

    /**
     * Tests iclicker_registration_user::get_iclicker_id_duplicate_count_in_course for
     * basic cases.
     */
    public function test_get_iclicker_id_duplicate_count_in_course_base_case() {
        global $iru, $testiclickerid;

        $user1 = $this->enrol_student_user($this->course1, 'me1');
        $user2 = $this->enrol_student_user($this->course1, 'me2');

        // Non-duplicate case.
        $this->register_iclicker($user1->idnumber, $testiclickerid);
        $this->assertEquals(0, $iru->get_iclicker_id_duplicate_count_in_course($this->course1->id));
        // Duplicate case.
        $this->register_iclicker($user2->idnumber, $testiclickerid);  // Register same id.
        $this->assertEquals(2, $iru->get_iclicker_id_duplicate_count_in_course($this->course1->id));
    }

    /**
     * Tests iclicker_registration_user::is_iclicker_id_duplicate_in_course for
     * edge cases.
     */
    public function test_get_iclicker_id_duplicate_count_in_course_edge_cases() {
        global $iru, $testiclickerid;

        $user1 = $this->enrol_student_user($this->course1, 'me1');
        $user2 = $this->enrol_student_user($this->course2, 'me2');

        // Edge case 1: Same iclicker in different courses and different users shouldn't
        //              be counted as different.
        $this->register_iclicker($user1->idnumber, $testiclickerid);
        $this->register_iclicker($user2->idnumber, $testiclickerid);

        // No duplicate error should show in the users respective course.
        $this->assertEquals(0, $iru->get_iclicker_id_duplicate_count_in_course($this->course1->id));
        $this->assertEquals(0, $iru->get_iclicker_id_duplicate_count_in_course($this->course2->id));
    }

    /**
     * Tests iclicker_registration_user::get_iclicker_id_duplicate_count_in_course for
     * basic cases.
     */
    public function test_get_iclicker_id_duplicate_count_base_case() {
        global $iru, $testiclickerid;

        $user1 = $this->enrol_student_user($this->course1, 'me1');
        $user2 = $this->enrol_student_user($this->course1, 'me2');

        // Non-duplicate case.
        $this->register_iclicker($user1->idnumber, $testiclickerid);
        $this->assertEquals(0, $iru->get_iclicker_id_duplicate_count());
        // Duplicate case.
        $this->register_iclicker($user2->idnumber, $testiclickerid);  // Register same id.
        $this->assertEquals(2, $iru->get_iclicker_id_duplicate_count());
    }

    /**
     * Tests iclicker_registration_user::is_iclicker_id_duplicate_in_course for
     * edge cases.
     */
    public function test_get_iclicker_id_duplicate_count_edge_cases() {
        global $iru, $testiclickerid;

        $user1 = $this->enrol_student_user($this->course1, 'me1');
        $user2 = $this->enrol_student_user($this->course2, 'me2');

        // Edge case 1: Same iclicker in different courses and different users shouldn't
        //              be counted as different.
        $this->register_iclicker($user1->idnumber, $testiclickerid);
        $this->register_iclicker($user2->idnumber, $testiclickerid);

        // No duplicate error should show in the users respective course.
        $this->assertEquals(0, $iru->get_iclicker_id_duplicate_count());
    }

    /**
     * Tests iclicker_registration_user::get_user_left_join_iclickers
     */
    public function test_get_user_left_join_iclickers() {
        global $iru, $testiclickerid;

        // Enroll multiple users.
        $user1 = $this->enrol_student_user($this->course1, 'me1');
        $user2 = $this->enrol_student_user($this->course2, 'me2');
        $user3 = $this->enrol_student_user($this->course2, 'me3');

        // Register just one iclicker.
        $this->register_iclicker($user1->idnumber, $testiclickerid);

        // Now do the left join of each users.
        $user1leftjoin = $iru->get_user_left_join_iclickers($user1->idnumber);
        $user2leftjoin = $iru->get_user_left_join_iclickers($user2->idnumber);
        $user3leftjoin = $iru->get_user_left_join_iclickers($user3->idnumber);

        $this->assertEquals($testiclickerid, $user1leftjoin->iclicker_id);
        $this->assertEquals(null, $user2leftjoin->iclicker_id);
        $this->assertEquals(null, $user3leftjoin->iclicker_id);
    }

    /**
     * Tests iclicker_registration_user::get_all_users_left_join_iclickers
     *
     * Note: This is the fattest function in the class for optimization reasons
     *       like, "all of the operations must be in sql".
     */
    public function test_get_all_users_left_join_iclickers_base_case() {
        global $iru, $testiclickerid;
        $iclickeridproperty = iclicker_registration_users::$iclickeridproperty;

        // Enroll multiple users.
        $user1 = $this->enrol_student_user($this->course1, 'me1');
        $user2 = $this->enrol_student_user($this->course1, 'me2');
        $user3 = $this->enrol_student_user($this->course1, 'me3');

        // Register just one iclicker. This is arbitrary, just to
        // get some results.
        $this->register_iclicker($user1->idnumber, $testiclickerid);

        // See if we can retrieve all of them.
        $result = $iru->get_all_users_left_join_iclickers(array('orderby' => 'idnumber'));
        $result = array_values($result);  // Replace associative array with normal array (0-based indexing).
        // Sanity check 1: Confirm no funny business.
        $this->assertEquals(3, count($result));
        // Sanity check 2: Confirm that each have the corresponding $user.
        $users = array($user1, $user2, $user3);
        for ($i = 0; $i < count($result); $i++) {
            $user = $users[$i];
            $r = $result[$i];

            $this->assertEquals($user->idnumber, $r->idnumber);
        }
        // Sanity check 3: Confirm that first result (corresponding to $user1) have an iclickerid.
        $this->assertEquals($testiclickerid, $result[0]->$iclickeridproperty);
    }

    public function test_get_all_users_left_join_iclickers_edge_case_duplicate_profiling() {
        global $iru, $iclickeridproperty, $testiclickerid;

        // Enroll multiple users.
        $user1 = $this->enrol_student_user($this->course1, 'me1');
        $user2 = $this->enrol_student_user($this->course1, 'me2');
        $user3 = $this->enrol_student_user($this->course2, 'me3');

        // Register just one iclicker. This is arbitrary, just to
        // get some results.
        $this->register_iclicker($user1->idnumber, $testiclickerid);
        $this->register_iclicker($user2->idnumber, $testiclickerid);

        // Since user1, user2 is in the same course, assert that their duplicate_count is 1
        // (since there is one other iclicker id that is not them).
        $result = $iru->get_all_users_left_join_iclickers(array('orderby' => 'idnumber'));
        $result = array_values($result);  // Replace associative array with normal array (0-based indexing).

        $this->assertEquals(1, $result[0]->duplicate_count);
        $this->assertEquals(1, $result[1]->duplicate_count);
    }

    public function test_get_all_users_left_join_iclickers_edge_case_filter_by_course() {
        global $iru, $testiclickerid;

        // Enroll multiple users.
        $user1 = $this->enrol_student_user($this->course1, 'me1');
        $user2 = $this->enrol_student_user($this->course1, 'me2');
        $user3 = $this->enrol_student_user($this->course2, 'me3');

        // Register just one iclicker. This is arbitrary, just to
        // get some results.
        $this->register_iclicker($user1->idnumber, $testiclickerid);
        $this->register_iclicker($user2->idnumber, $testiclickerid);

        // Filter for course 1 and assert to have 2 result, user1 and user2.
        $result1 = $iru->get_all_users_left_join_iclickers(array(
            'orderby' => 'idnumber',
            'courseid' => $this->course1->id));
        $result1 = array_values($result1);  // Replace associative array with normal array (0-based indexing).
        $this->assertEquals(2, count($result1));
        $this->assertEquals('me1', $result1[0]->idnumber);
        $this->assertEquals('me2', $result1[1]->idnumber);

        // Filter for course 2 and assert to have 1 result, user 3.
        $result2 = $iru->get_all_users_left_join_iclickers(array(
            'orderby' => 'idnumber',
            'courseid' => $this->course2->id));
        $result2 = array_values($result2);  // Replace associative array with normal array (0-based indexing).

        $this->assertEquals(1, count($result2));
        $this->assertEquals('me3', $result2[0]->idnumber);
    }

    public function test_get_all_users_left_join_iclickers_edge_case_hide_unregistered() {
        global $iru, $testiclickerid;

        // Enroll multiple users.
        $user1 = $this->enrol_student_user($this->course1, 'me1');
        $user2 = $this->enrol_student_user($this->course1, 'me2');
        $user3 = $this->enrol_student_user($this->course2, 'me3');

        // Register just one iclicker. This is arbitrary, just to
        // get some results.
        $this->register_iclicker($user1->idnumber, $testiclickerid);
        $this->register_iclicker($user2->idnumber, $testiclickerid);

        // Hide unregistered, and assert the result is the first two users, user1, user2.
        $result = $iru->get_all_users_left_join_iclickers(array(
            'orderby' => 'idnumber',
            'hideunregistered' => true));
        $result = array_values($result);  // Replace associative array with normal array (0-based indexing).

        $this->assertEquals($user1->idnumber, $result[0]->idnumber);
        $this->assertEquals($user2->idnumber, $result[1]->idnumber);
    }

    public function test_get_iclicker_user_duplicate_profile() {
        global $iru, $testiclickerid;

        // Enroll multiple users.
        $user1 = $this->enrol_student_user($this->course1, 'me1');
        $user2 = $this->enrol_student_user($this->course1, 'me2');
        $user3 = $this->enrol_student_user($this->course2, 'me3');

        // Register just one iclicker. This is arbitrary, just to
        // get some results.
        $this->register_iclicker($user1->idnumber, $testiclickerid);
        $this->register_iclicker($user2->idnumber, $testiclickerid);

        $result = $iru->get_all_users_left_join_iclickers(array(
            'orderby' => 'idnumber'));
        $result = array_values($result);
        $user1duplicateprofile = $iru->get_iclicker_user_duplicate_profile($result[0]);
        $user2duplicateprofile = $iru->get_iclicker_user_duplicate_profile($result[1]);
        $user3duplicateprofile = $iru->get_iclicker_user_duplicate_profile($result[2]);

        // Ensure that $user1/$user2 have a duplicate profile and that it is both in $course1.
        $this->assertEquals(1, count($user1duplicateprofile));
        $this->assertEquals(1, count($user2duplicateprofile));
        $this->assertEquals($this->course1->id, $user1duplicateprofile[0]->course->courseid);
        $this->assertEquals($this->course1->id, $user2duplicateprofile[0]->course->courseid);

        // Ensure that $user3 have no duplicate profile.
        $this->assertEquals(array(), $user3duplicateprofile);
    }

    public function test_get_iclicker_user_duplicate_profile_in_course() {
        global $iru, $testiclickerid;

        // Enroll multiple users.
        $user1 = $this->enrol_student_user($this->course1, 'me1');
        $user2 = $this->enrol_student_user($this->course1, 'me2');
        $user3 = $this->enrol_student_user($this->course2, 'me3');

        // Register just one iclicker. This is arbitrary, just to
        // get some results.
        $this->register_iclicker($user1->idnumber, $testiclickerid);
        $this->register_iclicker($user2->idnumber, $testiclickerid);

        $result = $iru->get_all_users_left_join_iclickers(array(
            'orderby' => 'idnumber'));
        $result = array_values($result);
        $user1duplicateprofile = $iru->get_iclicker_user_duplicate_profile_in_course($result[0], $this->course1->id);
        $user2duplicateprofile = $iru->get_iclicker_user_duplicate_profile_in_course($result[1], $this->course1->id);
        $user3duplicateprofile = $iru->get_iclicker_user_duplicate_profile_in_course($result[2], $this->course2->id);

        // Ensure that $user1/$user2 have a duplicate profile and that it is both in $course1.
        $this->assertEquals($this->course1->id, $user1duplicateprofile[0]->course->courseid);
        $this->assertEquals($this->course1->id, $user2duplicateprofile[0]->course->courseid);

        // Ensure that $user3 have no duplicate profile.
        $this->assertEquals(array(), $user3duplicateprofile);
    }

    public function test_validate_iclicker_id() {
        global $iru, $testiclickerid;

        // Invalid test.
        $invalidiclickerid = "ABCa1234";  // 'a' is not capitalized.
        try {
            $iru->validate_iclicker_id($invalidiclickerid);
            $this->assertTrue(false);  // Should not reach here.
        } catch (invalid_iclicker_id $iid) {
            $this->assertTrue(true);  // It should be invalid.
        }

        // Valid test.
        $validiclickerid = $testiclickerid;
        $this->assertTrue($iru->validate_iclicker_id($validiclickerid));
    }
}