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

require_once(__DIR__ . '/../../../../../../behat/behat_base.php');
require_once(__DIR__ . '/../../../../../../behat/behat_field_manager.php');

use Behat\Behat\Context\Step\Given as Given;

/**
 * Behat atto_countplusplus extension.
 *
 * @package    atto_countplusplus
 * @category   eclass/landing
 * @author     Joey Andres jandres@ualberta.ca
 */
class behat_atto_countplusplus extends behat_base {
    /**
     * @Given /^I should read "([^"]*)"$/
     * @param $arg text to be matched.
     * @throws Exception if $arg is not found in the page.
     */
    public function i_should_read($arg) {
        $body = preg_replace("/\\s+/", " ", strip_tags($this->find("css", "body")->getHtml()));
        if (preg_match("/$arg/", $body) != 1) {
            throw new Exception("\"$arg\" was not found in the page.");
        }
    }
}