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

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use Behat\Mink\Exception\ElementNotFoundException as ElementNotFoundException,
    Behat\Mink\Exception\ExpectationException as ExpectationException;

use Behat\Behat\Context\Step\Given as Given,
    Behat\Behat\Context\Step\When as When,
    Behat\Gherkin\Node\TableNode as TableNode;

/**
 * Steps definitions to deal with the nurs_navigation system.
 *
 * @package    block_nurs_navigation
 * @category   test
 * @copyright  2014 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class behat_block_nurs_navigation extends behat_base {

    /**
     * Creates shorthand for renaming a section to a new name.
     *
     * @Given /^I rename section "(?P<count>\d+)" to "(?P<username>(?:[^"]|\\")*)"$/
     * @throws Exception
     * @param int $sectionnumber The number of the section to rename
     * @param string $sectionname The new name of the section
     * 
     */
    public function i_rename_section_to($sectionnumber, $sectionname) {

        $data = new TableNode("| name | " . $sectionname . " |");

        return array(
            new When("I click on \"//li[@id='section-" . $sectionnumber . "']//div[@class='summary']/a\" \"xpath_element\""),
            new When('I uncheck "Use default section name"'),
            new When("I fill the moodle form with:", $data),
            new When('I press "Save changes"')
            );
    }

}
