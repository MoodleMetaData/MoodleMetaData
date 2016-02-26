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
 * Internal library of functions for module iclickerregistration
 *
 * All the iclickerregistration specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @package    mod_iclickerregistration
 * @copyright  2015 Joey Andres <jandres@ualberta.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__).'/../../config.php');
require_once(dirname(__FILE__).'/classes/iclicker_registration_user.php');

$iru = iclicker_registration_users::instance();

function get_user_by_idnumber($idnumber) {
    global $DB;
    return $DB->get_record('user', array("idnumber" => $idnumber));
}

function is_current_user($idnumber) {
    global $USER;
    return "$idnumber" === "current_user" || $idnumber === $USER->idnumber;
}

/**
 * @param string $booleanstring "true" or "false" string.
 * @return If parameter is "true", returns true. false otherwise.
 */
function boolean_string_to_boolean($booleanstring) {
    $booleanstring = !!$booleanstring ? $booleanstring : false;
    return filter_var($booleanstring, FILTER_VALIDATE_BOOLEAN);
}