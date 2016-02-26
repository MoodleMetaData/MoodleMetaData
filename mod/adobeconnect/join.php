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
 * @package mod
 * @subpackage adobeconnect
 * @author Akinsaya Delamarre (adelamarre@remote-learner.net)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/locallib.php');
require_once(dirname(__FILE__).'/connect_class.php');
require_once(dirname(__FILE__).'/connect_class_dom.php');

$id       = required_param('id', PARAM_INT);
$groupid  = required_param('groupid', PARAM_INT);
$sesskey  = required_param('sesskey', PARAM_ALPHANUM);


global $CFG, $USER, $DB, $PAGE, $OUTPUT;

if (! $cm = get_coursemodule_from_id('adobeconnect', $id)) {
    error('Course Module ID was incorrect');
}

$cond = array('id' => $cm->course);
if (! $course = $DB->get_record('course', $cond)) {
    error('Course is misconfigured');
}

$cond = array('id' => $cm->instance);
if (! $adobeconnect = $DB->get_record('adobeconnect', $cond)) {
    error('Course module is incorrect');
}

require_login($course, true, $cm);

// Check if the user's email is the Connect Pro user's login.
$usrobj = new stdClass();
$usrobj = clone($USER);
$name = empty($usrobj->idnumber) ? $usrobj->username : $usrobj->idnumber;
$usrobj->username = set_username($name, $usrobj->email);

$usrcanjoin = false;

$context   = context_module::instance( $cm->id );

// If separate groups is enabled, check if the user is a part of the selected group.
if (NOGROUPS != $cm->groupmode) {

    $usrgroups = groups_get_user_groups($cm->course, $usrobj->id);
    $usrgroups = $usrgroups[0]; // Just want groups and not groupings.

    $group_exists = false !== array_search($groupid, $usrgroups);
    $aag          = has_capability('moodle/site:accessallgroups', $context);

    if ($group_exists || $aag) {
        $usrcanjoin = true;
    }
} else {
    $usrcanjoin = true;
}

// Set page global.
$url = new moodle_url('/mod/adobeconnect/view.php', array('id' => $cm->id));

$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title(format_string($adobeconnect->name));
$PAGE->set_heading($course->fullname);

// User has to be in a group.
if ($usrcanjoin and confirm_sesskey($sesskey)) {

    $usrprincipal = 0;
    $validuser    = true;

    // Get the meeting sco-id.
    $param        = array('instanceid' => $cm->instance, 'groupid' => $groupid);
    $meetingscoid = $DB->get_field('adobeconnect_meeting_groups', 'meetingscoid', $param);

    $aconnect = aconnect_login();

    // Check if the meeting still exists in the shared folder of the Adobe server.
    $meetfldscoid = aconnect_get_folder($aconnect, 'meetings');
    $filter       = array('filter-sco-id' => $meetingscoid);
    $meeting      = aconnect_meeting_exists($aconnect, $meetfldscoid, $filter);

    if (!empty($meeting)) {
        $meeting = current($meeting);
    } else {

        /* Check if the module instance has a user associated with it
           if so, then check the user's adobe connect folder for existence of the meeting */
        if (!empty($adobeconnect->userid)) {
            $username     = get_connect_username($adobeconnect->userid);
            $meetfldscoid = aconnect_get_user_folder_sco_id($aconnect, $username);
            $meeting      = aconnect_meeting_exists($aconnect, $meetfldscoid, $filter);
            if (!empty($meeting)) {
                $meeting = current($meeting);
            }
        }
    }

    if (!($usrprincipal = aconnect_user_exists($aconnect, $usrobj))) {
        if (!($usrprincipal = aconnect_create_user($aconnect, $usrobj))) {
            echo $OUTPUT->notification(get_string('erroruser', 'adobeconnect'));
            $validuser = false;
        }
    }

    if ( $validuser && has_capability('mod/adobeconnect:meetinghost', $context, $usrobj->id, false) ) {
        // Add the user to the host group if they aren't already.
        $group_principal_id = aconnect_get_host_group($aconnect);
        aconnect_add_user_group($aconnect, $group_principal_id, $usrprincipal);
    }

    // Check the user's capabilities and assign them the Adobe Role.
    if (!empty($meetingscoid) and !empty($usrprincipal) and !empty($meeting)) {
        if (has_capability('mod/adobeconnect:meetinghost', $context, $usrobj->id, false)) {
            if (!aconnect_check_user_perm($aconnect, $usrprincipal, $meetingscoid, ADOBE_HOST, true)) {
                $validuser = false;
            }
        } else if (has_capability('mod/adobeconnect:meetingpresenter', $context, $usrobj->id, false)) {
            if (!aconnect_check_user_perm($aconnect, $usrprincipal, $meetingscoid, ADOBE_PRESENTER, true)) {
                $validuser = false;
            }
        } else if (has_capability('mod/adobeconnect:meetingparticipant', $context, $usrobj->id, false)) {
            if (!aconnect_check_user_perm($aconnect, $usrprincipal, $meetingscoid, ADOBE_PARTICIPANT, true)) {
                $validuser = false;
            }
        } else if ($adobeconnect->meetingpublic) {
            // If for a public meeting the user does not not have either of presenter or participant capabilities
            // then give the user the participant role for the meeting.
            if (!aconnect_check_user_perm($aconnect, $usrprincipal, $meetingscoid, ADOBE_PARTICIPANT, true)) {
                $validuser = false;
            }
        }
    } else {
        $validuser = false;
        notice(get_string('unableretrdetails', 'adobeconnect'), $url);
    }

    aconnect_logout($aconnect);

    // User is either valid or invalid, if valid redirect user to the meeting url.
    if (empty($validuser)) {
        notice(get_string('notparticipant', 'adobeconnect'), $url);
    } else {
        $protocol = 'http://';
        $https = false;
        $login = $usrobj->username;

        if (isset($CFG->adobeconnect_https) and (!empty($CFG->adobeconnect_https))) {

            $protocol = 'https://';
            $https = true;
        }

        $aconnect = new connect_class_dom($CFG->adobeconnect_host, $CFG->adobeconnect_port, '', '', '', $https);

        $aconnect->request_http_header_login(1, $login);

        // Include the port number only if it is a port other than 80 or 443.
        $port = '';

        if (!empty($CFG->adobeconnect_port) and (80 != $CFG->adobeconnect_port) and (443 != $CFG->adobeconnect_port)) {
            $port = ':' . $CFG->adobeconnect_port;
        }

        add_to_log($course->id, 'adobeconnect', 'join meeting',
                   "join.php?id=$cm->id&groupid=$groupid&sesskey=$sesskey",
                   "Joined $adobeconnect->name meeting", $cm->id);

        redirect($protocol . $CFG->adobeconnect_meethost . $port
                 . $meeting->url
                 . '?session=' . $aconnect->get_cookie());
    }
} else {
    notice(get_string('usergrouprequired', 'adobeconnect'), $url);
}
