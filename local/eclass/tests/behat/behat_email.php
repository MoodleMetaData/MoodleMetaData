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
 * Behat grade related steps definitions.
 *
 * @package    local
 * @category   eclass/landing
 * @author     Anthony Radziszewski radzisze@ualberta.ca
 */

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

class behat_email extends behat_base {

    /**
     * @Given /^I am on instructor emails with term "([^"]*)"$/
     */
    public function i_am_on_instructor_emails_with_term($arg1) {

        $this->getSession()->visit($this->locate_path('/local/eclass/landing/instructor_emails_list.php?term=' .$arg1));
    }
}