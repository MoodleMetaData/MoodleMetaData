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
 * Created by IntelliJ IDEA.
 * User: ggibeau
 * Date: 2013-08-12
 * Time: 4:02 PM
 * To change this template use File | Settings | File Templates.
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/adobeconnect/lib.php');
require_once($CFG->dirroot . '/mod/adobeconnect/locallib.php');
require_once($CFG->dirroot . '/mod/adobeconnect/connect_class.php');
require_once($CFG->dirroot . '/mod/adobeconnect/connect_class_dom.php');

/**
 * Create User, if user exists do nothing (return success) otherwise create user.
 * @param $user
 * @return bool|int false or userid if successful
 */
function connectsync_create_user($user) {
    if ($aconnect = aconnect_login()) {
        $pid = aconnect_user_exists($aconnect, $user);
        if ($pid) {
            $rvalue = true;
        } else {
            // May need to assign roles?  This is something we need to consider (business users especially)!
            $rvalue = aconnect_create_user($aconnect, $user);
            echo "Created user: ". $user->username ." in Adobe Connect\n";
        }
    } else {
        $rvalue = false;
    }

    aconnect_logout($aconnect);
    return $rvalue;
}

/**
 * Update user in adobe connect
 * @param $user
 * @return bool
 */
function connectsync_update_user($user) {
    if ($aconnect = aconnect_login()) {
        $pid = aconnect_user_exists($aconnect, $user);
        if ($pid) {
            // If pid provided then its an update.
            $rvalue = aconnect_create_user($aconnect, $user, $pid);
            echo "Updated user: ". $user->username ." in Adobe Connect\n";
        } else {
            // Do nothing, no user to update.
            $rvalue = true;
        }
    } else {
        // TODO Logging? Log trouble connecting?
        $rvalue = false;
    }

    aconnect_logout($aconnect);
    return $rvalue; // Do nothing.
}

/**
 * Delete user in adobe connect
 * @param $user  object with the following fields: username, idnumber, email, firstname, lastname, password
 * @return bool
 */
function connectsync_delete_user($user) {
    if ($aconnect = aconnect_login()) {
        $pid = aconnect_user_exists($aconnect, $user);
        if ($pid) {
            $rvalue = aconnect_delete_user($aconnect, $pid); // Pid = principle id.
            echo "Deleted user: ". $user->username ." in Adobe Connect\n";
        } else {
            $rvalue = true; // TODO User doesn't exist.....delete successful log?
        }
    } else {
        // TODO: Logging? Log trouble connecting?
        $rvalue = false;
    }

    aconnect_logout($aconnect);
    return $rvalue; // Do nothing.
}

function connectsync_add_user_to_host_group($userid) {
    global $DB;
    static $groupid;
    if ($aconnect = aconnect_login()) {
        if (!isset($groupid)) {
            $groupid = aconnect_get_host_group($aconnect);
        }
        aconnect_add_user_group($aconnect, $groupid, $userid);
        echo "Added user: ". $DB->get_field('user', 'username', 'id', $userid) ." to Host Group in Adobe Connect\n";
    }
}
