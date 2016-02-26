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
    Behat\Behat\Context\Step\Then as Then;

/**
 * Steps definitions to deal with the skills_group block.
 *
 * @package    block_skills_group
 * @category   test
 * @copyright  2014 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class behat_block_skills_group extends behat_base {

    /**
     * Adds the specified student to the group member selector.
     *
     * The autocomplete selector uses the "aria-hidden" attribute to determine which
     * one is currently visible on the page.
     *
     * @Given /^I add "(?P<student>(?:[^"]|\\")*)" to my group$/
     * @throws ElementNotFoundException
     * @param string $student The students's full name (fname lname)
     *
     */
    public function i_add_to_my_group($student) {

        return array(new When('I click on "#groupmembers" "css_element"'),
            new When("I click on \"//div[@aria-hidden='false']//li[text()='" . $student . "']\" \"xpath_element\"")
        );

    }

    /**
     * Removes the specified student from the group member selector.
     *
     * The <li> that holds the student has class "yui3-multivalueinput-listitem"
     *
     * @Given /^I remove "(?P<student>(?:[^"]|\\")*)" from my group$/
     * @throws ElementNotFoundException
     * @param string $student The students's full name (fname lname)
     *
     */
    public function i_remove_from_my_group($student) {

        $class = "contains(concat(' ', normalize-space(@class), ' '), 'yui3-multivalueinput-listitem ')";
        $text = "text()[contains(.,'{$student}')]";

        return array(new When("I click on \"//li[$class and $text]//a\" \"xpath_element\""));
    }

    /**
     * Checks for a label that was not rendered as collapsed.
     *
     * The individual step for this is long and the Moodle codecheker will complain, so
     * I've set it up here instead.
     *
     * @Given /^remove "(?P<labelname>(?:[^"]|\\")*)"$/
     * @throws ElementNotFoundException
     * @param string $labelname The label name with the skiplabel class attached
     *
     */
    public function remove($labelname) {

        $lixpath = "//li[contains(concat(' ', normalize-space(@class), ' '), ' modtype_label ')]";
        $pxpath = "//p[contains(concat(' ', normalize-space(@class), ' '), ' skiplabel ') and text()='" . $labelname . "']";

        return array(new Then("\"$lixpath$pxpath\" \"xpath_element\" should be visible"));
    }

    /**
     * Checks the database to see if a particular user is in a group
     *
     * @Then /^"(?P<username>(?:[^"]|\\")*)" should be in group "(?P<groupname>(?:[^"]|\\")*)"$/
     * @throws Exception
     * @param string $username Username of user in question
     * @param string $groupname The name of the group to check
     *
     */
    public function should_be_in_group($username, $groupname) {

        $record = $this->get_group_record($username, $groupname);

        if ($record === false) {
            throw new Exception('User ' . $username . ' not found in group' . $groupname);
        }
    }

    /**
     * Checks the database to see if a particular user is not in a group
     *
     * @Then /^"(?P<username>(?:[^"]|\\")*)" should not be in group "(?P<groupname>(?:[^"]|\\")*)"$/
     * @throws Exception
     * @param string $username Username of user in question
     * @param string $groupname The name of the group to check
     *
     */
    public function should_not_be_in_group($username, $groupname) {

        $record = $this->get_group_record($username, $groupname);

        if ($record !== false) {
            throw new Exception('User ' . $username . ' was found in group' . $groupname);
        }
    }

    /**
     * Retrieve a group record from the database -> see if $username is in
     * $groupname.
     *
     * @param string $username Username of user in question
     * @param string $groupname The name of the group to check
     * @return object The group record from the database
     */
    private function get_group_record($username, $groupname) {
        global $DB;

        $groupid = $DB->get_field('groups', 'id', array('name' => $groupname));
        $userid = $this->get_user_id($username);
        return $record = $DB->get_record('groups_members', array('groupid' => $groupid, 'userid' => $userid));
    }

    /**
     * Transform a moodle username into the corresponding ID.  I expected that this
     * would already exist as part of the API, but it does not.
     *
     * @throws Exception
     * @param string $username The username to find
     * @return int The ID corresponding to the username
     */
    private function get_user_id($username) {
        global $DB;

        if (!$id = $DB->get_field('user', 'id', array('username' => $username))) {
            throw new Exception('The specified user with username "' . $username . '" does not exist');
        }
        return $id;
    }
}
