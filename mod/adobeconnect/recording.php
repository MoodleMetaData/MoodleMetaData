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
 * The purpose of this file is to add a log entry when the user edits a
 * recording, before redirecting them to the recording edit screen
 *
 * @package mod
 * @subpackage adobeconnect
 * @author Josh Stagg (josh.stagg@ualberta.ca)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/locallib.php');
require_once(dirname(__FILE__).'/connect_class.php');
require_once(dirname(__FILE__).'/connect_class_dom.php');

$id          = required_param('id', PARAM_INT);
$groupid     = required_param('groupid', PARAM_INT);
$recscoid    = required_param('recording', PARAM_INT);
$mode        = required_param('mode', PARAM_TEXT);
$sessionkey  = required_param('sesskey', PARAM_TEXT);

global $CFG, $USER, $DB, $PAGE, $OUTPUT, $SESSION;

// Do the usual Moodle setup.
if (! $cm = get_coursemodule_from_id('adobeconnect', $id)) {
    error('Course Module ID was incorrect');
}

if (! $course = $DB->get_record('course', array('id' => $cm->course))) {
    error('Course is misconfigured');
}

if (! $adobeconnect = $DB->get_record('adobeconnect', array('id' => $cm->instance))) {
    error('Course module is incorrect');
}

if (strcasecmp($mode, 'normal') != 0 && strcasecmp($mode, 'edit') != 0 && strcasecmp($mode, 'offline') != 0
    && strcasecmp($mode, 'update_recording') != 0 && strcasecmp($mode, 'edit_recording') != 0
    && strcasecmp($mode, 'log_recording') != 0) {
    error('Recording mode is invalid');
}

$context  = context_module::instance($cm->id);

if (strcasecmp($mode, 'update_recording') == 0) {
    $connect   = aconnect_login();
    $success = new stdClass();
    $success->_success = false;
    if ( sesskey() == $sessionkey && has_capability('mod/adobeconnect:meetinghost', $context, $USER->id, false)) {
        $name  = required_param('name', PARAM_TEXT);
        $description  = optional_param('description', null, PARAM_TEXT);
        if(update_recording($connect, $recscoid, $name, $description)){
            $savedrecording = aconnect_get_recording($connect, $recscoid);
            if (!empty($savedrecording)) {
                $success->_success = true;
                $success->data = $savedrecording;
            }
        }
    }
    header('Content-type: application/json');
    echo json_encode($success);
    aconnect_logout($connect);
    return;
}

if (!confirm_sesskey()) {
    print_error('Invalid session');
}

require_login($course, true, $cm);
// Set page global.

$PAGE->set_title('Edit');
$PAGE->set_pagelayout('popup');
$PAGE->set_heading($course->fullname);
$PAGE->set_context($context);
$url = new moodle_url('/mod/adobeconnect/recording.php', array('id' => $cm->id));
$PAGE->set_url($url);

$usrobj = new stdClass();
$usrobj = clone($USER);
// Create a Connect Pro login session for this user.
$name = empty($usrobj->idnumber) ? $usrobj->username : $usrobj->idnumber;
$usrobj->username = set_username($name, $usrobj->email);

$params = array('instanceid' => $cm->instance, 'groupid' => $groupid);
$sql = "SELECT meetingscoid FROM {adobeconnect_meeting_groups} amg WHERE ".
       "amg.instanceid = :instanceid AND amg.groupid = :groupid";

$meetscoid = $DB->get_record_sql($sql, $params);

// Get the Meeting recording details.
$recording  = array();
$aconnect   = aconnect_login();
$fldid      = aconnect_get_folder($aconnect, 'content');
$usrcanjoin = false;

$usrprincipal = 0;
if (!($usrprincipal = aconnect_user_exists($aconnect, $usrobj))) {
    if (!($usrprincipal = aconnect_create_user($aconnect, $usrobj))) {
        debugging(get_string('erroruser', 'adobeconnect'), DEBUG_DEVELOPER);
    }
}

// Check the user's capabilities and assign them the Adobe Role.
if (has_capability('mod/adobeconnect:meetinghost', $context, $usrobj->id, false)) {
    // Add the host user to the host group if they aren't already.
    $group_principal_id = aconnect_get_host_group($aconnect);
    aconnect_add_user_group($aconnect, $group_principal_id, $usrprincipal);
    aconnect_check_user_perm($aconnect, $usrprincipal, $meetscoid->meetingscoid, ADOBE_HOST, true);
} else if (has_capability('mod/adobeconnect:meetingpresenter', $context, $usrobj->id, false)) {
    aconnect_check_user_perm($aconnect, $usrprincipal, $meetscoid->meetingscoid, ADOBE_PRESENTER, true);
} else if (has_capability('mod/adobeconnect:meetingparticipant', $context, $usrobj->id, false)) {
    aconnect_check_user_perm($aconnect, $usrprincipal, $meetscoid->meetingscoid, ADOBE_PARTICIPANT, true);
} else {
    // Check if meeting is public and allow them to join.
    if ($adobeconnect->meetingpublic) {
        // If for a public meeting the user does not not have either of presenter or participant capabilities
        // then give the user the participant role for the meeting.
        aconnect_check_user_perm($aconnect, $usrprincipal, $meetscoid->meetingscoid, ADOBE_PARTICIPANT, true);
    }
}

$recording = aconnect_get_recording($aconnect, $recscoid);

if (empty($recording) and confirm_sesskey()) {
    echo $OUTPUT->notification(get_string('errormeeting', 'adobeconnect'));
    die();
}
aconnect_logout($aconnect);

// If separate groups is enabled, check if the user is a part of the selected group.
if (NOGROUPS != $cm->groupmode) {
    $usrgroups = groups_get_user_groups($cm->course, $USER->id);
    $usrgroups = $usrgroups[0]; // Just want groups and not groupings.

    $group_exists = false !== array_search($groupid, $usrgroups);
    $aag          = has_capability('moodle/site:accessallgroups', $context);

    if ($group_exists || $aag) {
        $usrcanjoin = true;
    }
} else {
    $usrcanjoin = true;
}

if ( !$usrcanjoin ) {
    notice(get_string('usergrouprequired', 'adobeconnect'), $url);
}

if ( $usrcanjoin ) {
    // Include the port number only if it is a port other than 80 or 443.
    $port = '';

    if (!empty($CFG->adobeconnect_port) and (80 != $CFG->adobeconnect_port)  and (443 != $CFG->adobeconnect_port)) {
        $port = ':' . $CFG->adobeconnect_port;
    }
    if (strcasecmp($mode, 'log_recording') == 0) {
        $PAGE->set_title('Watched - '.$recording->name);
        $renderer = $PAGE->get_renderer('mod_adobeconnect');
        echo $OUTPUT->header();
        echo $renderer->display_usersviewed_recording($recscoid, $cm->id, $context, $recording);
        echo $OUTPUT->footer();
        add_to_log($course->id, 'adobeconnect', 'view',
            "view.php?id=$cm->id", "Log recording, id: {$recscoid}; name: {$recording->name}; ", $cm->id);
    } else if (strcasecmp($mode, 'edit_recording') == 0) {
        $PAGE->set_title('Edit recording - '.$recording->name);
        $renderer = $PAGE->get_renderer('mod_adobeconnect');
        echo $OUTPUT->header();
        echo $renderer->display_edit_recording($recscoid, $recording, $id, $groupid, $adobeconnect->name);
        echo $OUTPUT->footer();
        add_to_log($course->id, 'adobeconnect', 'update recording',
            "view.php?id=$cm->id", "Edit recording, id: {$recscoid}; name: {$recording->name}; ", $cm->id);
    } else {
        add_to_log($course->id, 'adobeconnect', 'view recording',
            "view.php?id=$cm->id", "{$mode}, id: {$recscoid}; name: {$recording->name}", $cm->id);
        // Log the fact that the user has watched the recording
        if (strcasecmp($mode, 'normal') == 0) {
            $userid = empty($USER->id) ? '0' : $USER->id;
            $exists = $DB->record_exists('adobeconnect_watched', array('scoid' => $recscoid, 'instanceid' => $cm->id, 'userid' => $userid));
            if (!$exists) {
                $record = new stdClass();
                $record->scoid = $recscoid;
                $record->instanceid = $cm->id;
                $record->userid = $userid;
                $DB->insert_record('adobeconnect_watched', $record);
            }
        }
        // Get HTTPS setting.
        $https      = false;
        $protocol   = 'http://';
        if (isset($CFG->adobeconnect_https) and (!empty($CFG->adobeconnect_https))) {
            $https      = true;
            $protocol   = 'https://';
        }
        $aconnect = new connect_class_dom($CFG->adobeconnect_host, $CFG->adobeconnect_port,
            '', '', '', $https);
        $aconnect->request_http_header_login(1, $usrobj->username);
        $adobesession = $aconnect->get_cookie();
        redirect($protocol . $CFG->adobeconnect_meethost . $port . $recording->url . '?session=' . $aconnect->get_cookie() . '&pbMode=' . $mode);
    }
}


