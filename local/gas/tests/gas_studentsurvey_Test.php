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

class surveytest extends advanced_testcase {

    /**
     * @runInSeparateProcess
     */
    public function test_studentsurvey_submit() {
        $this->resetAfterTest(true);
        $user = $this->getDataGenerator()->create_user();
        $row = array();
        $row['user_id'] = $user->id;
        $row['email'] = null;
        $row['timestamp'] = time();
        G\addvaliduser($row);
        $this->setUser($user);
        Global $CFG;
        include(dirname(dirname(__FILE__)) . '/config.php');
        ob_start();
        $_POST['action'] = 'submited';
        $_POST['survey'] = 'student';
        $_POST['surveyID'] = 777;
        include(dirname(dirname(__FILE__)) . '/index.php');
        $out = ob_get_contents();
        ob_end_clean();
        $this->assertContains('Thanks for your participation', $out);
    }
}
