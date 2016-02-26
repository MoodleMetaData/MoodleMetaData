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

class studenttest extends advanced_testcase {

    /**
     * @runInSeparateProcess
     */
    public function test_student_courses() {
        $this->resetAfterTest(true);
        $user = $this->getDataGenerator()->create_user();
        $row = array();
        $row['user_id'] = $user->id;
        $row['email'] = null;
        $row['timestamp'] = time();
        G\addvaliduser($row);
        $this->setUser($user);
        $course = $this->getDataGenerator()->create_course();
        $cohort = $this->getDataGenerator()->create_cohort(array('idnumber' => 1234.56789));

        cohort_add_member($cohort->id, $user->id);

        G\addtermid('1234');

        $enrol = enrol_get_plugin('cohort');
        $instance = array();
        $instance['name'] = 'name';
        $instance['status'] = ENROL_INSTANCE_ENABLED; // Enable it.
        $instance['customint1'] = $cohort->id; // Used to store the cohort id.
        $instance['roleid'] = $enrol->get_config('roleid'); // Default role for cohort enrol which is usually student.
        $instance['customint2'] = 0; // Optional group id.
        $enrol->add_instance($course, $instance);
        $trace = new null_progress_trace();
        enrol_cohort_sync($trace, $course->id);
        $trace->finished();

        $courses = G\coursesas($user->id, 'student');
        $courseids = array();
        foreach ($courses as $newcourse) {
            array_push($courseids, $newcourse->id);
        }
        $this->assertContains($course->id, $courseids);
    }

}
