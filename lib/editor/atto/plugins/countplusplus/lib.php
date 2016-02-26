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
 * Atto text editor integration version file.
 *
 * @package    atto_countplusplus
 * @copyright  2015 Joey Andres <jandres@ualberta.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Initialise the strings required for js
 */
function atto_countplusplus_strings_for_js() {
    global $PAGE;

    $strings = array(
        'wordsinalltext',
        'lettersinalltext',
        'countwordsandletters'
    );

    $PAGE->requires->strings_for_js($strings, 'atto_countplusplus');
}

/**
 * Make some strings (from settings.php) available in javascript.
 */
function atto_countplusplus_params_for_js($elementid, $options, $foptions) {
    // Pass the number of visible groups as a param.
    $params = array('statlayout' => get_config('atto_countplusplus', 'statlayout'));
    return $params;
}