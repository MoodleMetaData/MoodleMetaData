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

class consenttest extends advanced_testcase
{
    /**
     * @runInSeparateProcess
     */
    public function test_consent_submit() {
        $this->resetAfterTest(true);
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        Global $CFG;
        include(dirname(dirname(__FILE__)) . '/config.php');
        ob_start();
        $_POST['agreed'] = 'yes';
        include_once(dirname(dirname(__FILE__)).'/consent.php');
        $out = ob_get_contents();
        ob_end_clean();
        $this->assertContains('Thanks for your participation', $out);
    }
}