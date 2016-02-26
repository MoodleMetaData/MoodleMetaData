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

require_once('locallib.php');

/*
 * Library of functions and constants for module adobeconnect
 * This file should have two well differenced parts:
 *   - All the core Moodle functions, neeeded to allow
 *     the module to work integrated in Moodle.
 *   - All the adobeconnect specific functions, needed
 *     to implement all the module logic. Please, note
 *     that, if the module become complex and this lib
 *     grows a lot, it's HIGHLY recommended to move all
 *     these module specific functions to a new php file,
 *     called "locallib.php" (see forum, quiz...). This will
 *     help to save some memory when Moodle is performing
 *     actions across all modules.
 */

/* Include eventslib.php */
require_once($CFG->libdir.'/eventslib.php');
/* Include calendar/lib.php */
require_once($CFG->dirroot.'/calendar/lib.php');

/**
 * @uses FEATURE_GROUPS
 * @uses FEATURE_GROUPINGS
 * @uses FEATURE_GROUPMEMBERSONLY
 * @uses FEATURE_MOD_INTRO
 * @uses FEATURE_COMPLETION_TRACKS_VIEWS
 * @uses FEATURE_GRADE_HAS_GRADE
 * @uses FEATURE_GRADE_OUTCOMES
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, false if not, null if doesn't know
 */
function adobeconnect_supports($feature) {
    switch($feature) {
        case FEATURE_GROUPS:
            return true;
        case FEATURE_GROUPINGS:
            return true;
        case FEATURE_GROUPMEMBERSONLY:
            return true;
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return false;
        case FEATURE_GRADE_HAS_GRADE:
            return false;
        case FEATURE_GRADE_OUTCOMES:
            return false;
        case FEATURE_BACKUP_MOODLE2:
            return false;
        default:
            return null;
    }
}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $adobeconnect An object from the form in mod_form.php
 * @return int The id of the newly inserted adobeconnect record
 */
function adobeconnect_add_instance($adobeconnect) {
    global $COURSE, $USER, $DB;

    $adobeconnect->timecreated  = time();
    $adobeconnect->meeturl      = adobeconnect_clean_meet_url($adobeconnect->meeturl);
    $adobeconnect->userid       = $USER->id;

    $name = empty($USER->idnumber) ? $USER->username : $USER->idnumber;
    $username     = set_username($name, $USER->email);

    // Assign the current user with the Adobe Presenter role.
    $context = context_course::instance($adobeconnect->course);

    if (!has_capability('mod/adobeconnect:meetinghost', $context, $USER->id, false)) {

        $param = array('shortname' => 'adobeconnecthost');
        $roleid = $DB->get_field('role', 'id', $param);

        if (!role_assign($roleid, $USER->id, $context->id, 'mod_adobeconnect')) {
            debugging('role assignment failed', DEBUG_DEVELOPER);
            return false;
        }
    }

    $aconnect = aconnect_login();
    if ($aconnect->get_connection() != 1) {
        debugging('Unable to connect to the Adobe Connect server.', DEBUG_DEVELOPER);
        return false;
    }

    $recid = $DB->insert_record('adobeconnect', $adobeconnect);
    if (empty($recid)) {
        debugging('creating adobeconnect module instance failed', DEBUG_DEVELOPER);
        return false;
    }

    // Get the user's meeting folder location, if non exists then get the shared meeting folder location.
    $meetfldscoid = aconnect_get_user_folder_sco_id($aconnect, $username);
    if (empty($meetfldscoid)) {
        $meetfldscoid = aconnect_get_folder($aconnect, 'meetings');
    }

    $meeting = clone $adobeconnect;

    if (0 != $adobeconnect->groupmode) { // Allow for multiple groups.

        // Get all groups for the course.
        $crsgroups = groups_get_all_groups($COURSE->id);

        if (empty($crsgroups)) {
            return 0;
        }

        require_once(dirname(dirname(dirname(__FILE__))).'/group/lib.php');

        // Create the meeting for each group.
        foreach ($crsgroups as $crsgroup) {

            // The teacher role if they don't already have one and assign them to each group.
            if (!groups_is_member($crsgroup->id, $USER->id)) {
                groups_add_member($crsgroup->id, $USER->id);
            }

            $meeting->name = $adobeconnect->name . '_' . $crsgroup->name;

            if (!empty($adobeconnect->meeturl)) {
                $meeting->meeturl = adobeconnect_clean_meet_url($adobeconnect->meeturl   . '_' . $crsgroup->name);
            }

            // If creating the meeting failed, then return false and revert the group role assignments.
            if (!$meetingscoid = aconnect_create_meeting($aconnect, $meeting, $meetfldscoid)) {
                groups_remove_member($crsgroup->id, $USER->id);
                debugging('Error creating meeting', DEBUG_DEVELOPER);
                return false;
            }

            // Update permissions for meeting.
            if (empty($adobeconnect->meetingpublic)) {
                aconnect_update_meeting_perm($aconnect, $meetingscoid, ADOBE_MEETPERM_PRIVATE);
            } else {
                aconnect_update_meeting_perm($aconnect, $meetingscoid, ADOBE_MEETPERM_PUBLIC);
            }

            // Insert record to activity instance in meeting_groups table.
            $record = new stdClass;
            $record->instanceid = $recid;
            $record->meetingscoid = $meetingscoid;
            $record->groupid = $crsgroup->id;

            $record->id = $DB->insert_record('adobeconnect_meeting_groups', $record);

            // Add event to calendar.
            $event = new stdClass();

            $event->name = $meeting->name;
            $event->description = format_module_intro('adobeconnect', $adobeconnect, $adobeconnect->coursemodule);
            $event->courseid = $adobeconnect->course;
            $event->groupid = $crsgroup->id;
            $event->userid = 0;
            $event->instance = $recid;
            $event->eventtype = 'group';
            $event->timestart = $adobeconnect->starttime;
            $event->timeduration = $adobeconnect->endtime - $adobeconnect->starttime;
            $event->visible = 1;
            $event->modulename = 'adobeconnect';

            calendar_event::create($event);
        }

    } else { // No groups support.
        $meetingscoid = aconnect_create_meeting($aconnect, $meeting, $meetfldscoid);
        // If creating the meeting failed, then return false and revert the group role assignments.
        if (!$meetingscoid) {
            debugging('error creating meeting', DEBUG_DEVELOPER);
            return false;
        }

        // Update permissions for meeting.
        if (empty($adobeconnect->meetingpublic)) {
            aconnect_update_meeting_perm($aconnect, $meetingscoid, ADOBE_MEETPERM_PRIVATE);
        } else {
            aconnect_update_meeting_perm($aconnect, $meetingscoid, ADOBE_MEETPERM_PUBLIC);
        }

        // Insert record to activity instance in meeting_groups table.
        $record = new stdClass;
        $record->instanceid = $recid;
        $record->meetingscoid = $meetingscoid;
        $record->groupid = 0;

        $record->id = $DB->insert_record('adobeconnect_meeting_groups', $record);

        // Add event to calendar.
        $event = new stdClass();

        $event->name = $meeting->name;
        $event->description = format_module_intro('adobeconnect', $adobeconnect, $adobeconnect->coursemodule);
        $event->courseid = $adobeconnect->course;
        $event->groupid = 0;
        $event->userid = 0;
        $event->instance = $recid;
        $event->eventtype = 'course';
        $event->timestart = $adobeconnect->starttime;
        $event->timeduration = $adobeconnect->endtime - $adobeconnect->starttime;
        $event->visible = 1;
        $event->modulename = 'adobeconnect';

        calendar_event::create($event);
    }

    // If no meeting URL was submitted,
    // update meeting URL for activity with server assigned URL.
    if (empty($adobeconnect->meeturl) and (0 == $adobeconnect->groupmode)) {
        $filter = array('filter-sco-id' => $meetingscoid);
        $meeting = aconnect_meeting_exists($aconnect, $meetfldscoid, $filter);

        if (!empty($meeting)) {
            $meeting = current($meeting);

            $record = new stdClass();
            $record->id = $recid;
            $record->meeturl = trim($meeting->url, '/');
            $DB->update_record('adobeconnect', $record);
        }
    }

    aconnect_logout($aconnect);

    return $recid;
}


/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $adobeconnect An object from the form in mod_form.php
 * @return boolean Success/Fail
 */
function adobeconnect_update_instance($adobeconnect) {
    global $DB;

    $adobeconnect->timemodified = time();
    $adobeconnect->id           = $adobeconnect->instance;

    $aconnect = aconnect_login();

    $meetfldscoid = aconnect_get_folder($aconnect, 'meetings');

    // Look for meetings whose names are similar.
    $filter = array('filter-like-name' => $adobeconnect->name);

    $namematches = aconnect_meeting_exists($aconnect, $meetfldscoid, $filter);

    if (empty($namematches)) {
        $namematches = array();
    }

    // Find meeting URLs that are similar.
    $url = $adobeconnect->meeturl;
    $filter = array('filter-like-url-path' => $url);

    $urlmatches = aconnect_meeting_exists($aconnect, $meetfldscoid, $filter);

    if (empty($urlmatches)) {
            $urlmatches = array();
    } else {
        // Format url for comparison.
        if ((false === strpos($url, '/')) or (0 != strpos($url, '/'))) {
            $url = '/' . $url;
        }
    }

    $url = adobeconnect_clean_meet_url($url);

    // Get all instances of the activity meetings.
    $param = array('instanceid' => $adobeconnect->instance);
    $grpmeetings = $DB->get_records('adobeconnect_meeting_groups', $param);

    if (empty($grpmeetings)) {
        $grpmeetings = array();
    }

    // If no errors then check to see if the updated name and URL are actually different.
    // If true, then update the meeting names and URLs now.
    $namechange = true;
    $urlchange = true;
    $timechange = true;

    // Look for meeting name change.
    foreach ($namematches as $match) {
        if (array_key_exists($match->scoid, $grpmeetings)) {
            if (0 == substr_compare($match->name, $adobeconnect->name . '_', 0, strlen($adobeconnect->name . '_'), false)) {
                // Break out of loop and change all referenced meetings.
                $namechange = false;
                break;
            } else if (date('c', $adobeconnect->starttime) == $match->starttime) {
                $timechange = false;
                break;
            } else if (date('c', $adobeconnect->endtime) == $match->endtime) {
                $timechange = false;
                break;
            }
        }
    }

    // Look for URL change.
    foreach ($urlmatches as $match) {
        if (array_key_exists($match->scoid, $grpmeetings)) {
            if (0 == substr_compare($match->url, $url . '_', 0, strlen($url . '_'), false)) {
                // Break out of loop and change all referenced meetings.
                $urlchange = false;
                break;
            } else if (date('c', $adobeconnect->starttime) == $match->starttime) {
                $timechange = false;
                break;
            } else if (date('c', $adobeconnect->endtime) == $match->endtime) {
                $timechange = false;
                break;
            }
        }
    }

    if ($timechange or $urlchange or $namechange) {

        $meetingobj = new stdClass;
        foreach ($grpmeetings as $grpmeeting) {
            $group = '';

            if ($adobeconnect->groupmode) {
                $group = groups_get_group($grpmeeting->groupid);
                $group = '_' . $group->name;
            }

            $meetingobj->scoid = $grpmeeting->meetingscoid;
            $meetingobj->name = $adobeconnect->name . $group;
            $meetingobj->starttime = date('c', $adobeconnect->starttime);
            $meetingobj->endtime = date('c', $adobeconnect->endtime);
            /* If the userid is not empty then set the meeting folder sco id to
               the user's connect folder.  If this line of code is not executed
               then user's meetings that were previously in the user's connect folder
               would be moved into the shared folder */
            if (!empty($adobeconnect->userid)) {
                $username = get_connect_username($adobeconnect->userid);
                $userfolder = aconnect_get_user_folder_sco_id($aconnect, $username);

                if (!empty($userfolder)) {
                    $meetfldscoid = $userfolder;
                }

            }

            // Update each meeting instance.
            if (!aconnect_update_meeting($aconnect, $meetingobj, $meetfldscoid)) {
                debugging('error updating meeting', DEBUG_DEVELOPER);
            }

            if (empty($adobeconnect->meetingpublic)) {
                aconnect_update_meeting_perm($aconnect, $grpmeeting->meetingscoid, ADOBE_MEETPERM_PRIVATE);
            } else {
                aconnect_update_meeting_perm($aconnect, $grpmeeting->meetingscoid, ADOBE_MEETPERM_PUBLIC);
            }

            // Update calendar event.
            $param = array('courseid' => $adobeconnect->course, 'instance' =>
                           $adobeconnect->id, 'groupid' => $grpmeeting->groupid,
                           'modulename' => 'adobeconnect');

            $eventid = $DB->get_field('event', 'id', $param);

            if (!empty($eventid)) {

                $event = new stdClass();
                $event->id = $eventid;
                $event->name = $meetingobj->name;
                $event->description = format_module_intro('adobeconnect', $adobeconnect, $adobeconnect->coursemodule);
                $event->courseid = $adobeconnect->course;
                $event->groupid = $grpmeeting->groupid;
                $event->userid = 0;
                $event->instance = $adobeconnect->id;
                $event->eventtype = 0 == $grpmeeting->groupid ? 'course' : 'group';
                $event->timestart = $adobeconnect->starttime;
                $event->timeduration = $adobeconnect->endtime - $adobeconnect->starttime;
                $event->visible = 1;
                $event->modulename = 'adobeconnect';

                $calendarevent = calendar_event::load($eventid);
                $calendarevent->update($event);
            }
        }
    }

    aconnect_logout($aconnect);

    return $DB->update_record('adobeconnect', $adobeconnect);
}


/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function adobeconnect_delete_instance($id) {
    global $DB;

    $param = array('id' => $id);
    if (! $adobeconnect = $DB->get_record('adobeconnect', $param)) {
        return false;
    }

    $result = true;

    // Remove meeting from Adobe connect server.
    $param = array('instanceid' => $adobeconnect->id);
    $adbmeetings = $DB->get_records('adobeconnect_meeting_groups', $param);

    if (!empty($adbmeetings)) {
        $aconnect = aconnect_login();
        foreach ($adbmeetings as $meeting) {
            // Update calendar event.
            $param = array('courseid' => $adobeconnect->course, 'instance' => $adobeconnect->id,
                           'groupid' => $meeting->groupid, 'modulename' => 'adobeconnect');
            $eventid = $DB->get_field('event', 'id', $param);

            if (!empty($eventid)) {
                $event = calendar_event::load($eventid);
                $event->delete();
            }
        }

        aconnect_logout($aconnect);
    }

    $param = array('id' => $adobeconnect->id);
    $result &= $DB->delete_records('adobeconnect', $param);

    $param = array('instanceid' => $adobeconnect->id);
    $result &= $DB->delete_records('adobeconnect_meeting_groups', $param);

    return $result;
}

/**
 * Meeting URLs need to start with an alpha then be alphanumeric
 * or hyphen('-')
 *
 * @param string $meeturl Incoming URL
 * @return string cleaned URL
 */
function adobeconnect_clean_meet_url($meeturl) {
    $meeturl = preg_replace ('/[^a-z0-9]/i', '-', $meeturl);
    return $meeturl;
}

function adobeconnect_cron() {
    global $CFG;
    $port = '';
    $https = false;
    $protocol = 'http://';
    $error = '';
    if (isset($CFG->adobeconnect_https)) {
        $https = true;
        $protocol = 'https://';
    }
    // Include the port number only if it is a port other than 80 or 443.
    if (isset($CFG->adobeconnect_port) && (80 != $CFG->adobeconnect_port) && (443 != $CFG->adobeconnect_port)) {
        $port = ':' . $CFG->adobeconnect_port;
    }
    mtrace('Server: '.$protocol . $CFG->adobeconnect_meethost . $port);

    $aconnect = new connect_class_dom($CFG->adobeconnect_host,
        $CFG->adobeconnect_port,
        $CFG->adobeconnect_admin_login,
        $CFG->adobeconnect_admin_password, '', $https);

    $xmlresponse = create_request($aconnect, array('action' => 'common-info'));
    if (empty($xmlresponse)) {
        $error = 'No response from the Adobe Connect Server';
    } else {
        $aconnect->set_session_cookie($xmlresponse);
        $params = array(
            'action' => 'login',
            'login' => $aconnect->get_username(),
            'password' => $aconnect->get_password(),
        );
        $status = request($aconnect, $params);

        if (0 == strcmp('ok', $status)) {
            mtrace('Adobe Connect server connection is ok');
        } else {
            $error = "Adobe Connect server connection returned $status\n";
        }
    }
    aconnect_logout($aconnect);

    if (!empty($error)) {
        $site = get_site();
        $subject = 'Adobe Connect connection error for:'.get_config('mod_adobeconnect', 'adobeconnect_meethost');

        $supportuser = new stdClass();
        $supportuser->email = get_config('mod_adobeconnect', 'support_email');
        $supportuser->firstname = get_string('noreplyname');
        $supportuser->lastname = '';
        $supportuser->maildisplay = true;
        email_to_user($supportuser, $site->shortname, $subject, $error);
        mtrace("Sending error notification: $error");
    }
}


/**
 * @param array $params
 * @param int $reattempts
 * @return mixed
 */
function request($aconnect, $params = array(), $reattempts = 5) {
    return request_internal($aconnect, $params, $reattempts, 0);
}

/**
 * @param array $params
 * @param $reattempts
 * @param $attempt
 * @return mixed
 */
function request_internal($aconnect, $params = array(), $reattempts, $attempt) {
    $xmlresponse = create_request($aconnect, $params, true);
    $status = false;
    if (!empty($xmlresponse)) {
        $xml = new SimpleXMLElement($xmlresponse);
        $status = $xml->status[0]['code'];
    }
    if (0 != strcmp('ok', $status)) {
        // Handle various error codes
        // Internal-error is a server error, should reattempt the request.
        if ( 0 == strcmp('internal-error', $status) ) {
            if ($attempt < $reattempts) {
                usleep(300000 * $attempt);
                $status = request_internal($aconnect, $params, $reattempts, $attempt + 1);
            }
        }
    }
    return $status;
}

function create_request($aconnect, $params = array()) {
    if (empty($params)) {
        return false;
    }
    $dom = new DOMDocument('1.0', 'UTF-8');
    $root = $dom->createElement('params');
    $dom->appendChild($root);

    foreach ($params as $key => $data) {
        $datahtmlent = htmlentities($data);
        $child = $dom->createElement('param', $datahtmlent);
        $root->appendChild($child);

        $attribute = $dom->createAttribute('name');
        $child->appendChild($attribute);

        $text = $dom->createTextNode($key);
        $attribute->appendChild($text);
    }
    $xml = $dom->saveXML();
    $aconnect->set_xmlrequest($xml);
    return $aconnect->send_request();
}