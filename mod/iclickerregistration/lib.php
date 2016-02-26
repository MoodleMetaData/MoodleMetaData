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
 * Library of interface functions and constants for module iclickerregistration
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle should be placed here.
 *
 * All the iclickerregistration specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package    mod_iclickerregistration
 * @copyright  2015 Joey Andres <jandres@ualberta.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/classes/iclicker_registration_user.php');

/* Moodle core API */

/**
 * Returns the information on whether the module supports a feature
 *
 * See {@link plugin_supports()} for more info.
 *
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function iclickerregistration_supports($feature) {

    switch($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_GRADE_HAS_GRADE:
            return false;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the iclickerregistration into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param stdClass $iclickerregistration Submitted data from the form in mod_form.php
 * @param mod_iclickerregistration_mod_form $mform The form instance itself (if needed)
 * @return int The id of the newly inserted iclickerregistration record
 */
function iclickerregistration_add_instance(stdClass $iclickerregistration, mod_iclickerregistration_mod_form $mform = null) {
    global $DB;

    $iclickerregistration->timecreated = time();

    $iclickerregistration->id = $DB->insert_record('iclickerregistration', $iclickerregistration);

    return $iclickerregistration->id;
}

/**
 * Updates an instance of the iclickerregistration in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param stdClass $iclickerregistration An object from the form in mod_form.php
 * @param mod_iclickerregistration_mod_form $mform The form instance itself (if needed)
 * @return boolean Success/Fail
 */
function iclickerregistration_update_instance(stdClass $iclickerregistration, mod_iclickerregistration_mod_form $mform = null) {
    global $DB;

    $iclickerregistration->timemodified = time();
    $iclickerregistration->id = $iclickerregistration->instance;

    $result = $DB->update_record('iclickerregistration', $iclickerregistration);

    return $result;
}

/**
 * Removes an instance of the iclickerregistration from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function iclickerregistration_delete_instance($id) {
    global $DB;

    if (! $iclickerregistration = $DB->get_record('iclickerregistration', array('id' => $id))) {
        return false;
    }

    // No dependent records. Nothing more to delete.

    $DB->delete_records('iclickerregistration', array('id' => $iclickerregistration->id));

    return true;
}

/**
 * Returns a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 *
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @param stdClass $course The course record
 * @param stdClass $user The user record
 * @param cm_info|stdClass $mod The course module info object or record
 * @param stdClass $iclickerregistration The iclickerregistration instance record
 * @return stdClass|null
 */
function iclickerregistration_user_outline($course, $user, $mod, $iclickerregistration) {
    global $iru;

    $userismanuallyenrolled = isset($user->idnumber) === false || $user->idnumber === "";
    $return = new stdClass();
    $return->time = 0;

    if ($userismanuallyenrolled === false &&
        $iru->is_user_already_registered_by_idnumber($user->idnumber)) {
        // Retrieve the id of the registered iclicker.
        $iclickeruser = $iru->get_iclicker_by_idnumber($user->idnumber);
        $return->time = $iclickeruser->timemodified;
        $return->info = "User with ccid: $user->idnumber has registered iClicker ID: $iclickeruser->iclickerid.";
    } else {
        $return->info = "User is manually enrolled (no ccid) or have no registered iClicker.";
    }

    return $return;
}

/**
 * Prints a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * It is supposed to echo directly without returning a value.
 *
 * @param stdClass $course the current course record
 * @param stdClass $user the record of the user we are generating report for
 * @param cm_info $mod course module info
 * @param stdClass $iclickerregistration the module instance record
 */
function iclickerregistration_user_complete($course, $user, $mod, $iclickerregistration) {
    return iclickerregistration_user_outline($course, $user, $mod, $iclickerregistration);
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in iclickerregistration activities and print it out.
 *
 * @param stdClass $course The course record
 * @param bool $viewfullnames Should we display full names
 * @param int $timestart Print activity since this timestamp
 * @return boolean True if anything was printed, otherwise false
 */
function iclickerregistration_print_recent_activity($course, $viewfullnames, $timestart) {
    return false;
}

/**
 * Returns all other caps used in the module
 *
 * For example, this could be array('moodle/site:accessallgroups') if the
 * module uses that capability.
 *
 * @return array
 */
function iclickerregistration_get_extra_capabilities() {
    return array(
        'mod/iclickerregistration:addinstance',
        'mod/iclickerregistration:viewown',
        'mod/iclickerregistration:viewenrolled',
        'mod/iclickerregistration:viewallusers',
        'mod/iclickerregistration:editiclickerid'
    );
}