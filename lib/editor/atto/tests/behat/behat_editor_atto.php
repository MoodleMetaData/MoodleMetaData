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
 * Atto custom steps definitions.
 *
 * @package    editor_atto
 * @category   test
 * @copyright  2014 Damyon Wiese
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../behat/behat_base.php');
require_once(__DIR__ . '/../../../../behat/behat_field_manager.php');

use Behat\Behat\Context\Step\Given as Given;

/**
 * Steps definitions to deal with the atto text editor
 *
 * @package    editor_atto
 * @category   test
 * @copyright  2014 Damyon Wiese
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_editor_atto extends behat_base {

    /**
     * Select the text in an Atto field.
     *
     * @Given /^I select the text in the "([^"]*)" Atto editor$/
     * @throws ElementNotFoundException Thrown by behat_base::find
     * @param string $field
     * @return void
     */
    public function select_the_text_in_the_atto_editor($fieldlocator) {
        if (!$this->running_javascript()) {
            throw new coding_exception('Selecting text requires javascript.');
        }
        // We delegate to behat_form_field class, it will
        // guess the type properly.
        $field = behat_field_manager::get_form_field_from_label($fieldlocator, $this);

        if (!method_exists($field, 'select_text')) {
            throw new coding_exception('Field does not support the select_text function.');
        }
        $field->select_text();
    }

    /**
     * Append a line to atto configuration, example of this is:
     *
     * I Add "statusbar=statusbar, countplusplus" to atto configuration
     *
     * @Given /^I Add "([^"]*)" to atto configuration$/
     */
    public function configure_atto_toolbar($arg) {
        return array(
            new Given('I expand "Site administration" node'),
            new Given('I expand "Plugins" node'),
            new Given('I expand "Text editors" node'),
            new Given('I expand "Atto HTML editor" node'),
            new Given('I follow "Atto toolbar settings"'),
            new Given('I append to "Toolbar config" "' . $arg . '"'),
            new Given('I press "Save changes"')
        );
    }

    /**
     * Given a field locator, appends a value to it.
     *
     * @Given /^I append to "([^"]*)" "([^"]*)"$/
     */
    public function i_append_to($fieldlocator, $value) {
        $field = behat_field_manager::get_form_field_from_label($fieldlocator, $this);
        $configuration = $field->get_value();
        $configuration .= "\n" . $value;
        $field->set_value($configuration);
    }
}

