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
 * Behat searching for emails related step definitions.
 *
 * @package    local
 * @category   eclass/landing
 * @author     Anthony Radziszewski radzisze@ualberta.ca
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

class behat_criteria_email extends behat_base {

    /**
     * @Given /^I am on criteria emails with term "([^"]*)"$/
     */
    public function i_am_on_criteria_emails_with_term($arg1) {

        $this->getSession()->visit($this->locate_path('/local/eclass/landing/criteria_emails_list.php?term='
            .$arg1 .'&category=&role=&course=&lastaccess='));
    }

    /**
     * @Given /^I am on criteria emails with category "([^"]*)"$/
     */
    public function i_am_on_criteria_emails_with_category($arg1) {

        $this->getSession()->visit($this->locate_path('/local/eclass/landing/criteria_emails_list.php?term=&category='
            .$arg1 .'&role=&course=&lastaccess='));
    }

    /**
     * @Given /^I am on criteria emails with role "([^"]*)"$/
     */
    public function i_am_on_criteria_emails_with_role($arg1) {

        $this->getSession()->visit($this->locate_path('/local/eclass/landing/criteria_emails_list.php?term=&category=&role='
            .$arg1 .'&course=&lastaccess='));
    }

    /**
     * @Given /^I am on criteria emails with course "([^"]*)"$/
     */
    public function i_am_on_criteria_emails_with_course($arg1) {

        $this->getSession()->visit($this->locate_path('/local/eclass/landing/criteria_emails_list.php?term=&category=&role=
        &course=' .$arg1 .'&lastaccess='));
    }

    /**
     * @Given /^I am on criteria emails with accessdate "([^"]*)"$/
     */
    public function i_am_on_criteria_emails_with_accessdate($arg1) {

        $this->getSession()->visit($this->locate_path('/local/eclass/landing/criteria_emails_list.php?term=&category=&role=
        &course=&lastaccess=' .$arg1));
    }
}