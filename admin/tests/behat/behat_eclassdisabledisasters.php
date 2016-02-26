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
 * Completion steps definitions.
 *
 * @package   core_admin
 * @category  test
 */

require_once(__DIR__ . '/../../../lib/behat/behat_base.php');
require_once(__DIR__ . '/../../../lib/behat/behat_field_manager.php');

use Behat\Behat\Context\Step\Given as Given,
    Behat\Gherkin\Node\TableNode as TableNode;

/**
 * eclassdisabledisasters steps definitions.
 *
 * @package    core_admin
 * @category   test
 */
class behat_eclassdisabledisasters extends behat_base {

    /**
     * Sets the specified global variable. A table with | variable_name | value | is expected.
     *
     * @Given /^I set global variables with values:$/
     * @param TableNode $table
     */
    public function i_set_global_variables_with_values(TableNode $table) {
        global $CFG;

        if (!$data = $table->getRowsHash()) {
            return;
        }

        foreach ($data as $label => $value) {
            $$label = $value;
            print "\nFrom table I've set $$label = ${$label}";
        }
    }

    /**
     * @Given /^I set "([^"]*)" to "([^"]*)"$/
     */
    public function i_set_to($label, $value) {
        global $CFG;

        print "\n  Setting $label = $value\n";
        $$label = $value;
        print "  $$label = ${$label}\n";
    }

    /**
     * @Given /^"([^"]*)" is not set$/
     */
    public function is_not_set($arg1) {
        global $CFG;

        return !isset($$arg1);
    }

    /**
     * @Then /^"([^"]*)" is set$/
     */
    public function is_set($arg1) {
        global $CFG;

        return isset($$arg1);
    }

    /**
     * @Given /^I am on "([^"]*)"$/
     */
    public function i_am_on($arg1) {
        $this->getSession()->visit($this->locate_path($arg1));
    }

    /**
     * @Given /^I run "([^"]*)"$/
     */
    public function i_run($arg1) {
        shell_exec($arg1);
    }

    /**
     * @Given /^I append the line "([^"]*)" to "([^"]*)"$/
     */
    public function i_append_the_line_to($arg1, $arg2) {
        $command = "echo '$arg1' >> '" . __DIR__ . "/../../../$arg2'";
        print "\n:::$command:::";
        shell_exec($command);
    }
}
