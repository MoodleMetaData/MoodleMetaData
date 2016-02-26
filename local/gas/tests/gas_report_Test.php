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
//
// Author: Behdad Bakhshinategh!

use GAAT\functions as G;

require_once(dirname(dirname(__FILE__)) . '/lib/functions.php');

class reporttest extends advanced_testcase {

    /**
     * Adding a user
     * @param type $userid
     */
    private function adduser($userid) {
        $row = array();
        $row['user_id'] = $userid;
        $row['email'] = null;
        $row['timestamp'] = time();
        G\addvaliduser($row);
    }

    /**
     * 
     * @param type $user
     * @param type $course
     * @param type $cohort
     * @param type $mode
     */
    private function enroluserincourse($course, $cohort, $mode) {
        $enrol = enrol_get_plugin('cohort');
        $instance = array();
        $instance['name'] = 'name';
        $instance['status'] = ENROL_INSTANCE_ENABLED; // Enable it.
        $instance['customint1'] = $cohort->id; // Used to store the cohort id.
        $teacherroleid = $enrol->get_config('roleid');
        if ($mode == 'teacher') {
            $teacherroleid = G\getteacherroleid();
        }
        $instance['roleid'] = $teacherroleid;
        $instance['customint2'] = 0; // Optional group id.
        $enrol->add_instance($course, $instance);
        $trace = new null_progress_trace();
        enrol_cohort_sync($trace, $course->id);
        $trace->finished();
    }

    /**
     * @runInSeparateProcess
     */
    public function test_report() {
        $this->resetAfterTest(true);

        $user = $this->getDataGenerator()->create_user();
        $this->adduser($user->id);
        $this->setUser($user);

        $course = $this->getDataGenerator()->create_course();

        $cohort = $this->getDataGenerator()->create_cohort(array('idnumber' => 1234.56789));
        cohort_add_member($cohort->id, $user->id);
        G\addtermid('1234');

        $this->enroluserincourse($course, $cohort, 'teacher');

        $user2 = $this->getDataGenerator()->create_user();
        $this->adduser($user2->id);

        $user3 = $this->getDataGenerator()->create_user();
        $this->adduser($user3->id);

        $user4 = $this->getDataGenerator()->create_user();
        $this->adduser($user4->id);

        $cohort2 = $this->getDataGenerator()->create_cohort(array('idnumber' => 9876.54321));
        cohort_add_member($cohort2->id, $user2->id);
        cohort_add_member($cohort2->id, $user3->id);
        cohort_add_member($cohort2->id, $user4->id);
        G\addtermid('9876');

        $this->enroluserincourse($course, $cohort2, 'student');

        $year = date("Y");
        $sem = G\semOfDate(date("d"), date("m"));

        G\newassessment($user3->id, time(), 121, $sem, "Student");

        $students = G\studentsofcourse($course->id);
        $numofstudents = count($students);
        $numofstudentsdoneassessment = 0;
        if (count($students) > 0) {
            foreach ($students as $studentid) {
                if (G\doneassessment($studentid, $sem, $year)) {
                    $numofstudentsdoneassessment++;
                }
            }
        }
        $this->assertEquals(3, $numofstudents);
        $this->assertEquals(1, $numofstudentsdoneassessment);
    }

}
