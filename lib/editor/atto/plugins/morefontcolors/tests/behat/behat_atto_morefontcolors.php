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
use Behat\Mink\Exception\ExpectationException as ExpectationException;

/**
 * @param $hex string
 * @return array [r, g, b]
 */
function hex2rgb($hex) {
    $hex = str_replace("#", "", $hex);

    if (strlen($hex) === 3) {
        $r = hexdec(substr($hex, 0, 1).substr($hex, 0, 1));
        $g = hexdec(substr($hex, 1, 1).substr($hex, 1, 1));
        $b = hexdec(substr($hex, 2, 1).substr($hex, 2, 1));
    } else {
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
    }
    $rgb = array($r, $g, $b);

    return $rgb; // Returns an array with the rgb values.
}

/**
 * Behat atto_countplusplus extension.
 *
 * @package    atto_countplusplus
 * @category   eclass/landing
 * @author     Joey Andres jandres@ualberta.ca
 */
class behat_atto_morefontcolors extends behat_base {
    /**
     * @Then /^There should be a visible "([^"]*)" with color "(?P<hex_color_string>#(?:[^"]|[a-fA-F0-9]){6,6})" containing text:$/
     */
    public function there_should_be_element_with_attribute_background_color_of($element, $hexcolorstr, $text) {
        if (!$this->running_javascript()) {
            throw new DriverException('Visible checks are disabled in scenarios without Javascript support');
        }

        $rgb = hex2rgb($hexcolorstr);
        $rgbformat = "rgb($rgb[0], $rgb[1], $rgb[2])";

        $node = null;
        try {
            $node = $this->get_selected_node("css_element", $element . "[style*=\"color: " . $hexcolorstr . "\"]");
        } catch (Exception $e) {
            $node = $this->get_selected_node("css_element", $element . "[style*=\"color: " . $rgbformat . "\"]");
        }

        $elementcontainstext = strcmp($node->getText(), $text);

        if ($elementcontainstext && $node !== null && !$node->isVisible()) {
            throw new ExpectationException('"' . $element . '" with color "' . $hexcolorstr .
                '" "css_element" is not visible', $this->getSession());
        }
    }

    /**
     * @Then /^There should be a visible "([^"]*)" with background-color "(?P<hex_color_string>#(?:[^"]|[a-fA-F0-9]){6,6})"$/
     */
    public function there_should_be_element_with_attribute_background_background_color_of($element, $hexcolorstr) {
        try {
            return new Given('"'.$element.'[style*=\"background-color: ' . $hexcolorstr .
                '\"]" "css_element" should be visible');
        } catch (Exception $e) {
            $rgb = hex2rgb($hexcolorstr);
            $rgbformat = "rgb($rgb[0], $rgb[1], $rgb[2])";
            return new Given('"'.$element.'[style*=\"background-color: ' . $rgbformat .
                '\"]" "css_element" should be visible');
        }
    }

    /**
     * @Then /^There should NOT be a visible "([^"]*)" with background-color "(?P<hex_color_string>#(?:[^"]|[a-fA-F0-9]){6,6})"$/
     */
    public function there_should_not_be_element_with_attribute_background_color_of($element, $hexcolorstr) {
        try {
            return new Given('"'.$element.'[style*=\"background-color: ' . $hexcolorstr .
                '\"]" "css_element" should not be visible');
        } catch (Exception $e) {
            $rgb = hex2rgb($hexcolorstr);
            $rgbformat = "rgb($rgb[0], $rgb[1], $rgb[2])";
            return new Given('"'.$element.'[style*=\"background-color: ' . $rgbformat .
                '\"]" "css_element" should not be visible');
        }
    }

    /**
     * @Given /^I set the configuration of atto_morefontcolors to:$/
     */
    public function configure_atto_morefontcolors($markdown) {
        return array(
            new Given('I expand "Site administration" node'),
            new Given('I expand "Plugins" node'),
            new Given('I expand "Text editors" node'),
            new Given('I expand "Atto HTML editor" node'),
            new Given('I follow "More font colours"'),
            new Given('I set the field "s_atto_morefontcolors_availablecolors" to "' . $markdown . '"'),
            new Given('I press "Save changes"')
        );
    }
}