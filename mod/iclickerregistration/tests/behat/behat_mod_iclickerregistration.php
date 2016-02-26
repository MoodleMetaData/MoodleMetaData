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

require_once(__DIR__ . "/../../../../lib/behat/behat_base.php");
require_once(__DIR__ . "/../../../../lib/behat/behat_field_manager.php");

use Behat\Behat\Context\Step\Given as Given;

/**
 * Created by IntelliJ IDEA.
 * User: jandres
 * Date: 07/10/15
 * Time: 6:28 PM
 */


class behat_mod_iclickerregistration extends behat_base {
    /**
     * @Given /^I as non-student register iclicker id "(?P<iclickerid>(?:[^"]|\\")*)" at course "(?P<course>(?:[^"]|\\")*)" and activity "(?P<activity>(?:[^"]|\\")*)"$/
     */
    public function non_student_register_iclicker_to_current_user($iclicker, $course, $activity) {
        return array(
            new Given("I follow \"$course\""),
            new Given("I follow \"$activity\""),
            new Given('I press "iclickerregistration/registrationbuttontext" lang string'),
            new Given("I set the field \"clicker-id\" to \"$iclicker\""),
            new Given('I press "iclickerregistration/registrationbuttontext" lang string'),
            new Given('I should see "iclickerregistration/registrationsuccess" lang string'),
            new Given('I wait to be redirected'),
        );
    }

    /**
     * @Given /^I as student register iclicker id "(?P<iclickerid>(?:[^"]|\\")*)" at course "(?P<course>(?:[^"]|\\")*)" and activity "(?P<activity>(?:[^"]|\\")*)"$/
     */
    public function student_register_iclicker_to_current_user($iclicker, $course, $activity) {
        return array(
            new Given("I follow \"$course\""),
            new Given("I follow \"$activity\""),
            new Given("I set the field \"clicker-id\" to \"$iclicker\""),
            new Given('I press "iclickerregistration/registrationbuttontext" lang string'),
            new Given('I should see "iclickerregistration/registrationsuccess" lang string'),
            new Given('I wait to be redirected')
        );
    }
}