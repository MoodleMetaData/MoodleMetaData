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
require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/locallib.php');
require_once(dirname(__FILE__).'/connect_class.php');
require_once(dirname(__FILE__).'/connect_class_dom.php');

$coursemoduleid = optional_param('id', 0, PARAM_INT);
$aconnectinstid  = optional_param('a', 0, PARAM_INT);
$groupid = optional_param('group', 0, PARAM_INT);

global $CFG, $USER, $DB, $PAGE, $OUTPUT, $SESSION;

if ($coursemoduleid) {

    if (! $cm = get_coursemodule_from_id('adobeconnect', $coursemoduleid)) {
        print_error('Course Module ID was incorrect');
    }
    if (! $course = $DB->get_record('course',  array('id' => $cm->course))) {
        print_error('Course is misconfigured');
    }
    if (! $adobeconnect = $DB->get_record('adobeconnect', array('id' => $cm->instance))) {
        print_error('Course module is incorrect');
    }
} else if ( $aconnectinstid ) {
    if (! $adobeconnect = $DB->get_record('adobeconnect', array('id' => $aconnectinstid))) {
        print_error('Course module is incorrect');
    }
    if (! $course = $DB->get_record('course', array('id' => $adobeconnect->course))) {
        print_error('Course is misconfigured');
    }
    if (! $cm = get_coursemodule_from_instance('adobeconnect', $adobeconnect->id, $course->id)) {
        print_error('Course Module ID was incorrect');
    }
} else {
    print_error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);

// Check for submitted data.
if (($formdata = data_submitted($CFG->wwwroot . '/mod/adobeconnect/view.php')) && confirm_sesskey()) {
    // Edit participants.
    if (isset($formdata->participants)) {
        $cond = array('shortname' => 'adobeconnectpresenter');
        $roleid = $DB->get_field('role', 'id', $cond);
        if (!empty($roleid)) {
            redirect("participants.php?id=$coursemoduleid&contextid={$context->id}&roleid=$roleid&groupid={$formdata->group}",
                '', 0);
        } else {
            $message = get_string('nopresenterrole', 'adobeconnect');
            $OUTPUT->notification($message);
        }
    }

    if (isset($formdata->btnpublic)) {
        $aconnect = aconnect_login();
        foreach ($formdata->scoid as $key => $recordingid) {
            aconnect_make_public($aconnect, $recordingid);
        }
        aconnect_logout($aconnect);
        redirect(new moodle_url('/mod/adobeconnect/view.php', array('id' => $cm->id, 'session' => sesskey())));
    }

    if (isset($formdata->btnprivate)) {
        $aconnect = aconnect_login();
        foreach ($formdata->scoid as $key => $recordingid) {
            aconnect_make_private($aconnect, $recordingid);
        }
        aconnect_logout($aconnect);
        redirect(new moodle_url('/mod/adobeconnect/view.php', array('id' => $cm->id, 'session' => sesskey())));
    }


    if (isset($formdata->btnhide)) {
        foreach ($formdata->scoid as $key => $recordingid) {
            if ($id = $DB->get_record('adobeconnect_recordings', array('recordingid' => $recordingid))) {
                $recording = new stdClass();
                $recording->id = $id->id;
                $recording->hidden = true;
                $DB->update_record('adobeconnect_recordings', $recording);
            } else {
                $recording = new stdClass();
                $recording->recordingid = $recordingid;
                $recording->hidden = true;
                $DB->insert_record('adobeconnect_recordings', $recording, false);
            }
        }
        redirect(new moodle_url('/mod/adobeconnect/view.php', array('id' => $cm->id, 'session' => sesskey())));
    }

    if (isset($formdata->btnshow)) {
        foreach ($formdata->scoid as $key => $recordingid) {
            if ($id = $DB->get_record('adobeconnect_recordings', array('recordingid' => $recordingid))) {
                $recording = new stdClass();
                $recording->id = $id->id;
                $recording->hidden = false;
                $DB->update_record('adobeconnect_recordings', $recording);
            } else {
                $recording = new stdClass();
                $recording->recordingid = $recordingid;
                $recording->hidden = false;
                $DB->insert_record('adobeconnect_recordings', $recording, false);
            }
        }
        redirect(new moodle_url('/mod/adobeconnect/view.php', array('id' => $cm->id, 'session' => sesskey())));
    }

}

// Check if the user's email is the Connect Pro user's login.
$usrobj = new stdClass();
$usrobj = clone($USER);
$name = empty($usrobj->idnumber) ? $usrobj->username : $usrobj->idnumber;
$usrobj->username = set_username($name, $usrobj->email);

// Print the page header.
$url = new moodle_url('/mod/adobeconnect/view.php', array('id' => $cm->id));

$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title(format_string($adobeconnect->name));
$PAGE->set_heading($course->fullname);


echo $OUTPUT->header();

$stradobeconnects = get_string('modulenameplural', 'adobeconnect');
$stradobeconnect  = get_string('modulename', 'adobeconnect');

$params = array('instanceid' => $cm->instance);
$sql = "SELECT meetingscoid ".
       "FROM {adobeconnect_meeting_groups} amg ".
       "WHERE amg.instanceid = :instanceid ";

$meetscoids = $DB->get_records_sql($sql, $params);
$recordings = array();

if (!empty($meetscoids)) {
    $recscoids = array();

    $aconnect = aconnect_login();

    // Get the forced recordings folder sco-id.
    // Get recordings that are based off of the meeting.
    $fldid = aconnect_get_folder($aconnect, 'forced-archives');
    foreach ($meetscoids as $scoid) {
        $data = aconnect_get_recordings($aconnect, $fldid, $scoid->meetingscoid);
        if (!empty($data)) {
            // Store recordings in an array to be moved to the Adobe shared folder later on.
            $recscoids = array_merge($recscoids, array_keys($data));
        }

    }

    // Move the meetings to the shared content folder.
    if (!empty($recscoids)) {
        $recscoids = array_flip($recscoids);
        aconnect_move_to_shared($aconnect, $recscoids);

    }

    // Get the shared content folder sco-id
    // Create a list of recordings moved to the shared content folder.
    foreach ($meetscoids as $scoid) {
        $data = aconnect_get_recordings($aconnect, $scoid->meetingscoid, $scoid->meetingscoid);
        if ( !empty($data) ) {
            $recordings[] = $data;
        }
    }

    // Clean up any duplicated meeting recordings.  Duplicated meeting recordings happen when the
    // recording settings on ACP server change between "publishing recording links in meeting folders" and
    // not "publishing recording links in meeting folders".
    $names = array();
    foreach ($recordings as $key => $recordingarray) {
        foreach ($recordingarray as $key2 => $record) {
            if (!empty($names)) {
                if (!array_search($record->name, $names)) {
                    $names[] = $record->name;
                } else {
                    unset($recordings[$key][$key2]);
                }
            } else {
                $names[] = $record->name;
            }
        }
    }
    unset($names);

    // Check if the user exists and if not create the new user.
    if (!($usrprincipal = aconnect_user_exists($aconnect, $usrobj))) {
        if (!($usrprincipal = aconnect_create_user($aconnect, $usrobj))) {
            debugging("error creating user", DEBUG_DEVELOPER);
            $validuser = false;
        }
    }

    if ( $usrprincipal && has_capability('mod/adobeconnect:meetinghost', $context, $usrobj->id, false) ) {
        // Add the user to the host group if they aren't already.
        $group_principal_id = aconnect_get_host_group($aconnect);
        aconnect_add_user_group($aconnect, $group_principal_id, $usrprincipal);
    }

    // Check the user's capability and assign them view permissions to the recordings folder
    // if it's a public meeting give them permissions regardless.
    if ($cm->groupmode) {
        if (has_capability('mod/adobeconnect:meetingpresenter', $context, $usrobj->id) or
            has_capability('mod/adobeconnect:meetingparticipant', $context, $usrobj->id)) {
            if (!aconnect_assign_user_perm($aconnect, $usrprincipal, $fldid, ADOBE_VIEW_ROLE)) {
                debugging("error assign user recording folder permissions", DEBUG_DEVELOPER);
            }
        }
    } else {
        aconnect_assign_user_perm($aconnect, $usrprincipal, $fldid, ADOBE_VIEW_ROLE);
    }
    aconnect_logout($aconnect);
}

// Log in the current user.
$login = $usrobj->username;
$password  = $usrobj->username;
$https = false;

if (isset($CFG->adobeconnect_https) and (!empty($CFG->adobeconnect_https))) {
    $https = true;
}

$aconnect = new connect_class_dom($CFG->adobeconnect_host, $CFG->adobeconnect_port, '', '', '', $https);
$aconnect->request_http_header_login(1, $login);
$adobesession = $aconnect->get_cookie();

// The batch of code below handles the display of Moodle groups.
if ($cm->groupmode) {

    $querystring = array('id' => $cm->id);
    $url = new moodle_url('/mod/adobeconnect/view.php', $querystring);

    // Retrieve a list of groups that the current user can see/manage.
    $user_groups = groups_get_activity_allowed_groups($cm, $USER->id);

    if ($user_groups) {

        // Print groups selector drop down.
        groups_print_activity_menu($cm, $url, false, true);

        // Retrieve the currently active group for the user's session.
        $groupid = groups_get_activity_group($cm);

        /* Depending on the series of events groups_get_activity_group will
         * return a groupid value of  0 even if the user belongs to a group.
         * If the groupid is set to 0 then use the first group that the user
         * belongs to.
         */
        if (0 == $groupid ) {
            $groups = groups_get_user_groups($cm->course, $USER->id);
            $groups = current($groups);
            if (has_capability('moodle/site:accessallgroups', $context)) {
                /* If the user does not explicitely belong to any group
                 * check their capabilities to see if they have access
                 * to manage all groups; and if so display the first course
                 * group by default.
                 */
                $groupid = key($user_groups);
            } else if (!empty($groups)) {
                $array = $SESSION->activegroup[$cm->course];
                $groupid = key($SESSION->activegroup[$cm->course]);
            }
        }
    }
}


$aconnect = aconnect_login();

// Get the Meeting details.
$cond = array('instanceid' => $adobeconnect->id, 'groupid' => $groupid);
$scoid = $DB->get_field('adobeconnect_meeting_groups', 'meetingscoid', $cond);

$meetfldscoid = aconnect_get_folder($aconnect, 'meetings');


$filter = array('filter-sco-id' => $scoid);

if (($meeting = aconnect_meeting_exists($aconnect, $meetfldscoid, $filter))) {
    $meeting = current($meeting);
} else {

    /* First check if the module instance has a user associated with it
       if so, then check the user's adobe connect folder for existince of the meeting */
    if (!empty($adobeconnect->userid)) {
        $username     = get_connect_username($adobeconnect->userid);
        $meetfldscoid = aconnect_get_user_folder_sco_id($aconnect, $username);
        $meeting      = aconnect_meeting_exists($aconnect, $meetfldscoid, $filter);

        if (!empty($meeting)) {
            $meeting = current($meeting);
        }
    }

    // If meeting does not exist then display an error message.
    if (empty($meeting)) {

        $message = get_string('nomeeting', 'adobeconnect');
        echo $OUTPUT->notification($message);
        aconnect_logout($aconnect);
        die();
    }
}
aconnect_logout($aconnect);

$sesskey = !empty($usrobj->sesskey) ? $usrobj->sesskey : '';

$renderer = $PAGE->get_renderer('mod_adobeconnect');

$meetingdetail = new stdClass();
$meetingdetail->name = html_entity_decode($meeting->name);


// Determine if the user has the permissions to assign perticipants.
$meetingdetail->ishost =  has_capability('mod/adobeconnect:meetinghost', $context, $usrobj->id);

$meetingdetail->privileged = ( has_capability('mod/adobeconnect:meetingpresenter', $context, $usrobj->id) ||
                               $meetingdetail->ishost );

$meetingdetail->canjoin = ( has_capability('mod/adobeconnect:meetingparticipant', $context, $usrobj->id) ||
                            $meetingdetail->privileged );


// Determine if the Meeting URL is to appear.
if ( $meetingdetail->privileged and $adobeconnect->meetingpublic ) {

    // Include the port number only if it is a port other than 80 or 443.
    $port = '';

    if (!empty($CFG->adobeconnect_port) and (80 != $CFG->adobeconnect_port) and (443 != $CFG->adobeconnect_port)) {
        $port = ':' . $CFG->adobeconnect_port;
    }

    $protocol = 'http://';

    if ($https) {
        $protocol = 'https://';
    }

    $url = $protocol . $CFG->adobeconnect_meethost . $port . $meeting->url;

    $meetingdetail->url = $url;

} else {
    $meetingdetail->url = '';
}

//  CONTRIB-2929 - remove date format and let Moodle decide the format
// Get the meeting start time.
$time = userdate($adobeconnect->starttime);
$meetingdetail->starttime = $time;

// Get the meeting end time.
$time = userdate($adobeconnect->endtime);
$meetingdetail->endtime = $time;

// Get the meeting intro text.
$meetingdetail->intro = $adobeconnect->intro;
$meetingdetail->introformat = $adobeconnect->introformat;


echo $OUTPUT->box_start('generalbox', 'meetingsummary');

// If groups mode is enabled for the activity and the user belongs to a group.
if (NOGROUPS != $cm->groupmode && 0 != $groupid) {
    echo $renderer->display_meeting_detail($meetingdetail, $coursemoduleid, $groupid);
} else if (NOGROUPS == $cm->groupmode) {
    // If groups mode is disabled.
    echo $renderer->display_meeting_detail($meetingdetail, $coursemoduleid, $groupid);
} else {
    // If groups mode is enabled but the user is not in a group.
    echo $renderer->display_no_groups_message();
}

echo $OUTPUT->box_end();

echo '<br />';

$showrecordings = $adobeconnect->meetingpublic;
// Check if meeting is private, if so check the user's capability.  If public show recorded meetings.
if (!$showrecordings && $meetingdetail->canjoin) {
    $showrecordings = true;
}

// Lastly check group mode and group membership.
if (NOGROUPS != $cm->groupmode && 0 != $groupid) {
    $showrecordings = $showrecordings && true;
} else if (NOGROUPS == $cm->groupmode) {
    $showrecording = $showrecordings && true;
} else {
    $showrecording = $showrecordings && false;
}


// Echo the rendered HTML to the page.
echo $renderer->display_meeting_help($meetingdetail);

if ($showrecordings and !empty($recordings)) {
    echo $OUTPUT->box_start('generalbox', 'recordings');

    // Echo the rendered HTML to the page.
    echo $renderer->display_meeting_recording($meetingdetail, $recordings, $cm->id, $groupid, $scoid);

    echo $OUTPUT->box_end();
}

add_to_log($course->id, 'adobeconnect', 'view',
           "view.php?id=$cm->id", "View {$adobeconnect->name} details", $cm->id);
// Finish the page.
echo $OUTPUT->footer();
