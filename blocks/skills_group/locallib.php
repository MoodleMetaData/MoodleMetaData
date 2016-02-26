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
 * This is the locallib.php file for the project.  Any functions that are
 * used across several different modules are here.
 *
 * @package    block_skills_group
 * @category   block
 * @copyright  2014 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once(dirname(__FILE__).'/../../config.php');
global $CFG;
require_once($CFG->dirroot.'/user/lib.php');

/** name of the plugin */
define('BLOCK_SG_LANG_TABLE', 'block_skills_group');
define('NOFEEDBACK', 0);

/**
 * This function checks for a user's access to a particular form/page.  The main check
 * is requiring a user to be logged into a particular course.  Optionally, it will check
 * for a capability and check the user's sesskey.
 *
 * @param string $capability Verify the user has this capability.
 * @param bool $checksesskey T/F indicating whether sesskey should be checked.
 * @return bool T/F indicating if access is permitted.
 *
 */
function blocks_skills_group_verify_access($capability = null, $checksesskey = false) {
    $courseid = required_param('courseid', PARAM_INT);
    try {
        require_login($courseid, false);
    } catch (Exception $e) {
        echo get_string('nologin', BLOCK_SG_LANG_TABLE);
        return false;
    }
    if ($capability != null) {
        if (!has_capability($capability, context_course::instance($courseid))) {
            echo get_string('noaccess', BLOCK_SG_LANG_TABLE);
            return false;
        }
    }
    try {
        if ($checksesskey != false) {
            if (!confirm_sesskey()) {
                echo get_string('badsesskey', BLOCK_SG_LANG_TABLE);
                return false;
            }
        }
    } catch (Exception $e) {
        echo get_string('badsesskey', BLOCK_SG_LANG_TABLE);
        return false;
    }
    return true;
}

/**
 * This function sets up a standard entry page.
 *
 * @param object $url moodle_url of current page.
 * @param string $title Title of current page.
 *
 */

function block_skills_group_setup_page($courseid, $url, $title, $layout = 'standard') {
    global $PAGE;

    $PAGE->set_url($url);
    $PAGE->set_context(context_course::instance($courseid));
    $PAGE->set_title($title);
    $PAGE->set_pagelayout($layout);
    $PAGE->set_heading($title);
    $PAGE->navbar->add($title, $url);
}

/**
 * This function looks up the given set of user IDs and returns an array containing
 * their full names.
 *
 * @param array $ids List of user IDs to lookup.
 * @return array List of names corresponding to IDs.
 *
 */
function block_skills_group_retrieve_names($ids) {
    $names = array();
    $users = user_get_users_by_id($ids);
    // Explicit sort here is needed to preserve ID ordering.
    sort($users);
    foreach ($users as $user) {
        $names[] = $user->firstname.' '.$user->lastname;
    }
    return $names;
}

/**
 * This function strips returned user records down to their ID only.  In this
 * case, the key in the array contains the ID number.
 *
 * @param object $records Full user records including other fields.
 * @return array Array containing IDs only of form ['id'] => 'id'.
 *
 */
function block_skills_group_strip_to_ids($records) {
    $idsonly = array();
    // User records are indexed with the ID as the key.
    foreach ($records as $key => $record) {
        $idsonly[$key] = $key;
    }
    return $idsonly;
}


