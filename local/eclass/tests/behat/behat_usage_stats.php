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
 * Behat module_stats related step definitions.
 *
 * @package    local
 * @category   eclass/landing
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

class behat_usage_stats extends behat_base {

    /**
     * @Given /^I am on usage_stats with no parameters$/
     */
    public function i_am_on_usage_stats_with_no_parameters() {
        $this->getSession()->visit($this->locate_path('/local/eclass/landing/usage_stats.php'));
    }

    /**
     * @Given /^I am on usage_stats with term "([^"]*)"$/
     */
    public function i_am_on_modle_stats_with_term($term) {
        $this->getSession()->visit($this->locate_path('/local/eclass/landing/usage_stats.php'
                . '?term=' . $term));
    }

    /**
     * @Given /^I am on usage_stats with verbose and term "([^"]*)"$/
     */
    public function i_am_on_modle_stats_with_verbose_and_term($term) {
        $this->getSession()->visit($this->locate_path('/local/eclass/landing/usage_stats.php?verbose=1'
                . '&term=' . $term));
    }
}