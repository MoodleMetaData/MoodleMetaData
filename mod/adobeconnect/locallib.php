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

require_once('connect_class.php');
require_once('connect_class_dom.php');

define('ADOBE_VIEW_ROLE', 'view');
define('ADOBE_HOST_ROLE', 'host');
define('ADOBE_MINIADMIN_ROLE', 'mini-host');
define('ADOBE_REMOVE_ROLE', 'remove');

define('ADOBE_PARTICIPANT', 1);
define('ADOBE_PRESENTER', 2);
define('ADOBE_REMOVE', 3);
define('ADOBE_HOST', 4);

define('ADOBE_TEMPLATE_POSTFIX', '- Template');
define('ADOBE_MEETING_POSTFIX', '- Meeting');

// The meeting is public, and anyone who has the URL for the meeting can enter the room.
define('ADOBE_MEETPERM_PUBLIC', 0);
// The meeting is protected, and only registered users and accepted guests can enter the room.
define('ADOBE_MEETPERM_PROTECTED', 1);
// The meeting is private, and only registered users and participants can enter the room.
define('ADOBE_MEETPERM_PRIVATE', 2);

define('ADOBE_TMZ_LENGTH', 6);

/**
 * Returns the folder sco-id
 * @param $aconnect
 * @internal param connect_class $aconnect object
 * @param string $folder name of the folder to get
 * (ex. forced-archives = recording folder | meetings = meetings folder
 * | content = shared content folder)
 * @return mixed adobe connect folder sco-id || false if there was an error
 *
 */
function aconnect_get_folder($aconnect, $folder = '') {
    $folderscoid = false;
    $params = array('action' => 'sco-shortcuts');

    if ($aconnect->request($params)) {
        $folderscoid = aconnect_get_folder_sco_id($aconnect->get_xmlresponse(), $folder);
    }

    return $folderscoid;
}

/**
 * TODO: comment function and return something meaningful
 */
function aconnect_get_folder_sco_id($xml, $folder) {
    $scoid = false;

    $dom = new DomDocument();
    $dom->loadXML($xml);

    $domnodelist = $dom->getElementsByTagName('sco');

    if (!empty($domnodelist->length)) {

        for ($i = 0; $i < $domnodelist->length; $i++) {

            $domnode = $domnodelist->item($i)->attributes->getNamedItem('type');

            if (!is_null($domnode)) {

                if (0 == strcmp($folder, $domnode->nodeValue)) {
                    $domnode = $domnodelist->item($i)->attributes->getNamedItem('sco-id');

                    if (!is_null($domnode)) {
                        $scoid = (int) $domnode->nodeValue;

                    }
                }
            }
        }
    }

    return $scoid;

}

/**
 * Log in as the admin user.  This should only be used to conduct API calls.
 */
function aconnect_login() {
    global $CFG, $USER, $COURSE;

    if (!isset($CFG->adobeconnect_host) or !isset($CFG->adobeconnect_admin_login) or !isset($CFG->adobeconnect_admin_password)) {

        if (is_siteadmin($USER->id)) {
            notice(get_string('adminnotsetupproperty', 'adobeconnect'),
                   $CFG->wwwroot . '/admin/settings.php?section=modsettingadobeconnect');
        } else {
            notice(get_string('notsetupproperty', 'adobeconnect'),
                   '', $COURSE);
        }
    }

    $https = false;

    if (isset($CFG->adobeconnect_https) and (!empty($CFG->adobeconnect_https))) {
        $https = true;
    }

    $aconnect = new connect_class_dom($CFG->adobeconnect_host, $CFG->adobeconnect_port, $CFG->adobeconnect_admin_login,
                                      $CFG->adobeconnect_admin_password, '', $https);

    $params = array(
        'action' => 'common-info'
    );

    $aconnect->create_request($params);

    $aconnect->read_cookie_xml($aconnect->get_xmlresponse());

    $params = array(
          'action' => 'login',
          'login' => $aconnect->get_username(),
          'password' => $aconnect->get_password(),
    );

    if ($aconnect->request($params)) {
        $aconnect->set_connection(1);
    } else {
        $aconnect->set_connection(0);
    }

    return $aconnect;
}


/**
 * Logout
 * @param $aconnect
 * @internal param connect_class $aconnect object
 * @return boolean true on success else false
 */
function aconnect_logout(&$aconnect) {
    if (!$aconnect->get_connection()) {
        return true;
    }

    $params = array('action' => 'logout');

    if ($aconnect->request($params)) {
        $aconnect->set_connection(0);
        return true;
    } else {
        $aconnect->set_connection(1);
        return false;
    }
}

/**
 * Calls all operations needed to retrieve and return all
 * templates defined in the shared templates folder and meetings
 * @param $aconnect
 * @internal param connect_class $aconnect object
 * @return array $templates an array of templates
 */
function aconnect_get_templates_meetings($aconnect) {
    $templates = array();
    $meetings = array();
    $tempfldscoid = false;

    $params = array(
        'action' => 'sco-shortcuts',
    );

    if ($aconnect->request($params)) {
        // Get shared templates folder sco-id.
        $tempfldscoid = aconnect_get_shared_templates($aconnect->get_xmlresponse());
    }

    if (false !== $tempfldscoid) {
        $params = array(
            'action' => 'sco-expanded-contents',
            'sco-id' => $tempfldscoid,
        );

        if ($aconnect->request($params)) {
            $templates = aconnect_return_all_templates($aconnect->get_xmlresponse());
        }
    }

    return $templates + $meetings;
}

/**
 * Parse XML looking for shared-meeting-templates attribute
 * and returning the sco-id of the folder
 * @param string $xml returned XML from a sco-shortcuts call
 * @return mixed sco-id if found or false if not found or error
 */
function aconnect_get_shared_templates($xml) {
    $scoid = false;

    $dom = new DomDocument();
    $dom->loadXML($xml);

    $domnodelist = $dom->getElementsByTagName('shortcuts');

    if (!empty($domnodelist->length)) {

        $innerlist = $domnodelist->item(0)->getElementsByTagName('sco');

        if (!empty($innerlist->length)) {

            for ($x = 0; $x < $innerlist->length; $x++) {

                if ($innerlist->item($x)->hasAttributes()) {

                    $domnode = $innerlist->item($x)->attributes->getNamedItem('type');

                    if (!is_null($domnode)) {

                        if (0 == strcmp('shared-meeting-templates', $domnode->nodeValue)) {
                            $domnode = $innerlist->item($x)->attributes->getNamedItem('sco-id');

                            if (!is_null($domnode)) {
                                $scoid = (int) $domnode->nodeValue;
                            }
                        }
                    }
                }
            }
        }
    }
    return $scoid;
}

function aconnect_return_all_meetings($xml) {
    $meetings = array();
    $xml = new SimpleXMLElement($xml);

    if (empty($xml)) {
        return $meetings;
    }

    foreach ($xml->{'expanded-scos'}[0]->sco as $sco) {
        if (0 == strcmp('meeting', $sco['type'])) {
            $mkey = (int) $sco['sco-id'];
            $meetings[$mkey] = (string) current($sco->name) .' '. ADOBE_MEETING_POSTFIX;
        }
    }
    return $meetings;
}

/**
 * Parses XML for meeting templates and returns an array
 * with sco-id as the key and template name as the value
 * @param string $xml XML returned from a sco-expanded-contents call
 * @return array of templates sco-id -> key, name -> value
 */
function aconnect_return_all_templates($xml) {
    $templates = array();

    $dom = new DomDocument();
    $dom->loadXML($xml);

    $domnodelist = $dom->getElementsByTagName('expanded-scos');

    if (!empty($domnodelist->length)) {

        $innerlist = $domnodelist->item(0)->getElementsByTagName('sco');

        if (!empty($innerlist->length)) {

            for ($i = 0; $i < $innerlist->length; $i++) {

                if ($innerlist->item($i)->hasAttributes()) {
                    $domnode = $innerlist->item($i)->attributes->getNamedItem('type');

                    if (!is_null($domnode) and 0 == strcmp('meeting', $domnode->nodeValue)) {
                        $domnode = $innerlist->item($i)->attributes->getNamedItem('sco-id');

                        if (!is_null($domnode)) {
                            $tkey = (int) $domnode->nodeValue;
                            $namelistnode = $innerlist->item($i)->getElementsByTagName('name');

                            if (!is_null($namelistnode)) {
                                $name = $namelistnode->item(0)->nodeValue;
                                $templates[$tkey] = (string) $name .' ' . ADOBE_TEMPLATE_POSTFIX;
                            }
                        }
                    }
                }
            }
        }
    }

    return $templates;
}

function aconnect_get_recording($aconnect, $scoid) {
    $params = array('action' => 'sco-info',
        'sco-id'=> $scoid);

    if ($aconnect->request($params)) {
        $dom = new DomDocument();
        $dom->loadXML($aconnect->get_xmlresponse());

        if (!empty($dom)) {

            $innernodelist = $dom->getElementsByTagName('sco');

            if ($innernodelist->item(0)->hasAttributes()) {
                $meetingdetail = $innernodelist->item(0);
                // Check if the SCO item is a recording or uploaded document. We only want to display recordings.
                $recording = new stdClass();

                $recording->id = $scoid;

                $recording->name = (string)
                (!is_null($meetingdetail->getElementsByTagName('name')->item(0))) ?
                    $meetingdetail->getElementsByTagName('name')->item(0)->nodeValue : '';

                $recording->url = (string)
                (!is_null($meetingdetail->getElementsByTagName('url-path')->item(0))) ?
                    $meetingdetail->getElementsByTagName('url-path')->item(0)->nodeValue : '';

                $recording->startdate = (string)
                (!is_null($meetingdetail->getElementsByTagName('date-begin')->item(0))) ?
                    $meetingdetail->getElementsByTagName('date-begin')->item(0)->nodeValue : '';

                $recording->enddate = (string)
                (!is_null($meetingdetail->getElementsByTagName('date-end')->item(0)) ?
                    $meetingdetail->getElementsByTagName('date-end')->item(0)->nodeValue : '');

                $recording->createdate = (string)
                (!is_null($meetingdetail->getElementsByTagName('date-created')->item(0))) ?
                    $meetingdetail->getElementsByTagName('date-created')->item(0)->nodeValue : '';

                $recording->modified = (string)
                (!is_null($meetingdetail->getElementsByTagName('date-modified')->item(0))) ?
                    $meetingdetail->getElementsByTagName('date-modified')->item(0)->nodeValue : '';

                $recording->description = (string)
                (!is_null($meetingdetail->getElementsByTagName('description')->item(0))) ?
                    $meetingdetail->getElementsByTagName('description')->item(0)->nodeValue : '';

                $recording->public = aconnect_check_public_access($aconnect, $scoid);
                return $recording;
            }
        }
    }
    return false;
}


/**
 * Returns information about all recordings that belong to a specific
 * meeting sco-id
 *
 * @param $aconnect
 * @internal param connect_class $aconnect object
 * @param int $folderscoid the recordings folder sco-id
 * @param int $sourcescoid the meeting sco-id
 *
 * @return mixed array an array of object with the recording sco-id
 * as the key and the recording properties as properties
 */
function aconnect_get_recordings($aconnect, $folderscoid, $sourcescoid) {
    $params = array('action' => 'sco-contents',
                    'sco-id' => $folderscoid,
                    'filter-icon' => 'archive',
                    'sort-date-begin' => 'asc',
                    );

    // Check if meeting scoid and folder scoid are the same
    // If they are the same then that means that forced recordings is not
    // enabled filter-source-sco-id should not be included.  If the
    // meeting scoid and folder scoid are not equal then forced recordings
    // are enabled and we can use filter by filter-source-sco-id
    // Thanks to A. gtdino.
    if ($sourcescoid != $folderscoid) {
        $params['filter-source-sco-id'] = $sourcescoid;
    }

    $recordings = array();

    if ($aconnect->request($params)) {
        $dom = new DomDocument();
        $dom->loadXML($aconnect->get_xmlresponse());

        $domnodelist = $dom->getElementsByTagName('scos');

        if (!empty($domnodelist->length)) {

            $innernodelist = $domnodelist->item(0)->getElementsByTagName('sco');

            if (!empty($innernodelist->length)) {

                for ($x = 0; $x < $innernodelist->length; $x++) {

                    if ($innernodelist->item($x)->hasAttributes()) {

                        $domnode = $innernodelist->item($x)->attributes->getNamedItem('sco-id');

                        if (!is_null($domnode)) {
                            $meetingdetail = $innernodelist->item($x);

                            // In AC9, the recording length info is stored as an attributed of 'sco'.
                            $recordingvac9 = $innernodelist->item($x)->attributes->getNamedItem('duration');
                            // In AC-8 and before, the recording length info is stored as its own element.
                            $recordingvac8 = $meetingdetail->getElementsByTagName('duration')->item(0);

                            // Check if the SCO item is a recording or uploaded document. We only want to display recordings.

                            if (!is_null($recordingvac9) || !is_null($recordingvac8)) {
                                $j = (int) $domnode->nodeValue;

                                $recordings[$j] = new stdClass();

                                $recordings[$j]->name = (string)
                                        (!is_null($meetingdetail->getElementsByTagName('name')->item(0))) ?
                                        $meetingdetail->getElementsByTagName('name')->item(0)->nodeValue : '';

                                $recordings[$j]->url = (string)
                                        (!is_null($meetingdetail->getElementsByTagName('url-path')->item(0))) ?
                                        $meetingdetail->getElementsByTagName('url-path')->item(0)->nodeValue : '';

                                $recordings[$j]->startdate = (string)
                                        (!is_null($meetingdetail->getElementsByTagName('date-begin')->item(0))) ?
                                        $meetingdetail->getElementsByTagName('date-begin')->item(0)->nodeValue : '';

                                $recordings[$j]->enddate = (string)
                                        (!is_null($meetingdetail->getElementsByTagName('date-end')->item(0)) ?
                                        $meetingdetail->getElementsByTagName('date-end')->item(0)->nodeValue : '');

                                $recordings[$j]->createdate = (string)
                                        (!is_null($meetingdetail->getElementsByTagName('date-created')->item(0))) ?
                                        $meetingdetail->getElementsByTagName('date-created')->item(0)->nodeValue : '';

                                $recordings[$j]->modified = (string)
                                        (!is_null($meetingdetail->getElementsByTagName('date-modified')->item(0))) ?
                                        $meetingdetail->getElementsByTagName('date-modified')->item(0)->nodeValue : '';

                                $recordings[$j]->description = (string)
                                (!is_null($meetingdetail->getElementsByTagName('description')->item(0))) ?
                                    $meetingdetail->getElementsByTagName('description')->item(0)->nodeValue : '';

                                $recordings[$j]->duration = (string)
                                        (!is_null($recordingvac9) ? $recordingvac9->nodeValue : $recordingvac8->nodeValue);

                                $recordings[$j]->sourcesco = (int) $sourcescoid;

                                $recordings[$j]->public = aconnect_check_public_access($aconnect, $j);
                            }
                        }
                    }
                }
            }
            return $recordings;
        }
    }
    return false;
}

/**
 * Parses XML and returns the meeting sco-id
 * @param string $xml obtained from a sco-update call
 * @return mixed meeting scoid if successful, false otherwise
 */
function aconnect_get_meeting_scoid($xml) {
    $scoid = false;

    $dom = new DomDocument();
    $dom->loadXML($xml);

    $domnodelist = $dom->getElementsByTagName('sco');

    if (!empty($domnodelist->length)) {
        if ($domnodelist->item(0)->hasAttributes()) {
            $domnode = $domnodelist->item(0)->attributes->getNamedItem('sco-id');

            if (!is_null($domnode)) {
                $scoid = (int) $domnode->nodeValue;
            }
        }
    }

    return $scoid;
}

/**
 * Update meeting
 * @param $aconnect
 * @internal param connect_class $object $aconnect connect_class object
 * @param object $meetingobj an adobeconnect module object
 * @param int $meetingfdl adobe connect meeting folder sco-id
 * @return bool true if call was successful else false
 */
function aconnect_update_meeting($aconnect, $meetingobj, $meetingfdl) {
    $params = array('action' => 'sco-update',
                    'sco-id' => $meetingobj->scoid,
                    'name' => htmlentities($meetingobj->name),
                    'folder-id' => $meetingfdl,
                    'date-begin' => $meetingobj->starttime,
                    'date-end' => $meetingobj->endtime,
                    );

    return $aconnect->request($params);

}

/**
 * Update a meeting's access permissions
 * @param $aconnect
 * @internal param connect_class $aconnect object
 * @param int $meetingscoid meeting sco-id
 * @param int $perm meeting permission id
 * @return bool true if call was successful else false
 */
function aconnect_update_meeting_perm($aconnect, $meetingscoid, $perm) {
    $params = array('action' => 'permissions-update',
                 'acl-id' => $meetingscoid,
                 'principal-id' => 'public-access',
                );

    switch ($perm) {
        case ADOBE_MEETPERM_PUBLIC:
            $params['permission-id'] = 'view-hidden';
            break;
        case ADOBE_MEETPERM_PROTECTED:
            $params['permission-id'] = 'remove';
            break;
        case ADOBE_MEETPERM_PRIVATE:
        default:
            $params['permission-id'] = 'denied';
            break;
    }

    return $aconnect->request($params);
}

/** CONTRIB-1976, CONTRIB-1992
 * This function adds a fraction of a second to the ISO 8601 date
 * @param int $time unix timestamp
 * @return mixed a string (ISO 8601) containing the decimal fraction of a second
 * or false if it was not able to determine where to put it
 */
function aconnect_format_date_seconds($time) {

    $date = date("c", $time);

    $pos = strrpos($date, '-');
    $length = strlen($date);

    $diff = $length - $pos;

    if ((0 < $diff) and (ADOBE_TMZ_LENGTH == $diff)) {
        $firstpart = substr($date, 0, $pos);
        $lastpart = substr($date, $pos);
        return $firstpart . '.000' . $lastpart;
    }

    $pos = strrpos($date, '+');
    $length = strlen($date);

    $diff = $length - $pos;

    if ((0 < $diff) and (ADOBE_TMZ_LENGTH == $diff)) {
        $firstpart = substr($date, 0, $pos);
        $lastpart = substr($date, $pos);
        return  $firstpart . '.000' . $lastpart;
    }

    return false;
}

/**
 * Creates a meeting
 * @param $aconnect
 * @internal param connect_class $aconnect object
 * @param object $meetingobj an adobeconnect module object
 * @param int $meetingfdl adobe connect meeting folder sco-id
 * @return mixed meeting sco-id on success || false on error
 */
function aconnect_create_meeting($aconnect, $meetingobj, $meetingfdl) {
    $starttime = aconnect_format_date_seconds($meetingobj->starttime);
    $endtime = aconnect_format_date_seconds($meetingobj->endtime);

    if (empty($starttime) or empty($endtime)) {
        $message = 'Failure (aconnect_find_timezone) in finding the +/- sign in the date timezone'.
                    "\n".date("c", $meetingobj->starttime)."\n".date("c", $meetingobj->endtime);
        debugging($message, DEBUG_DEVELOPER);
        return false;
    }

    $params = array('action' => 'sco-update',
                    'type' => 'meeting',
                    'name' => htmlentities($meetingobj->name),
                    'folder-id' => $meetingfdl,
                    'date-begin' => $starttime,
                    'date-end' => $endtime,
                    );

    if (!empty($meetingobj->meeturl)) {
        $params['url-path'] = $meetingobj->meeturl;
    }

    if (!empty($meetingobj->templatescoid)) {
        $params['source-sco-id'] = $meetingobj->templatescoid;
    }

    if ($aconnect->request($params)) {
        return aconnect_get_meeting_scoid($aconnect->get_xmlresponse());
    } else {
        return false;
    }
}

/**
 * Finds a matching meeting sco-id
 * @param object $aconnect a connect_class object
 * @param int $meetfldscoid Meeting folder sco-id
 * @param array $filter array key is the filter and array value is the value
 * (ex. array('filter-name' => 'meeting101'))
 * @return mixed array of objects with sco-id as key and meeting name and url as object
 * properties as value || false if not found or error occured
 */
function aconnect_meeting_exists($aconnect, $meetfldscoid, $filter = array()) {
    $matches = array();

    $params = array(
        'action' => 'sco-contents',
        'sco-id' => $meetfldscoid,
        'filter-type' => 'meeting',
    );

    if (empty($filter)) {
        return false;
    }

    $params = array_merge($params, $filter);

    if ($aconnect->request($params)) {
        $dom = new DomDocument();
        $dom->loadXML($aconnect->get_xmlresponse());

        $domnodelist = $dom->getElementsByTagName('scos');

        if (!empty($domnodelist->length)) {

            $innernodelist = $domnodelist->item(0)->getElementsByTagName('sco');

            if (!empty($innernodelist->length)) {

                for ($i = 0; $i < $innernodelist->length; $i++) {

                    if ($innernodelist->item($i)->hasAttributes()) {

                        $domnode = $innernodelist->item($i)->attributes->getNamedItem('sco-id');

                        if (!is_null($domnode)) {

                            $key = (int) $domnode->nodeValue;

                            $meetingdetail = $innernodelist->item($i);

                            $value = (!is_null($meetingdetail->getElementsByTagName('name'))) ?
                                     $meetingdetail->getElementsByTagName('name')->item(0)->nodeValue : '';

                            if (!isset($matches[$key])) {
                                $matches[$key] = new stdClass();
                            }

                            $matches[$key]->name = (string) $value;

                            $value = (!is_null($meetingdetail->getElementsByTagName('url-path'))) ?
                                     $meetingdetail->getElementsByTagName('url-path')->item(0)->nodeValue : '';

                            $matches[$key]->url = (string) $value;

                            $matches[$key]->scoid = (int) $key;

                            $value = (!is_null($meetingdetail->getElementsByTagName('date-begin'))) ?
                                     $meetingdetail->getElementsByTagName('date-begin')->item(0)->nodeValue : '';

                            $matches[$key]->starttime = (string) $value;

                            $value = (!is_null($meetingdetail->getElementsByTagName('date-end'))) ?
                                     $meetingdetail->getElementsByTagName('date-end')->item(0)->nodeValue : '';

                            $matches[$key]->endtime = (string) $value;

                        }
                    }
                }
            }
        } else {
            return false;
        }
    } else {
        return false;
    }

    return $matches;
}

/**
 * Parse XML and returns the user's principal-id
 * @param string $xml XML returned from call to principal-list
 * @return mixed user's principal-id if successful false otherwise
 */
function aconnect_get_user_principal_id($xml) {
    $usrprincipalid = false;

    $dom = new DomDocument();
    $dom->loadXML($xml);

    $domnodelist = $dom->getElementsByTagName('principal-list');

    if (!empty($domnodelist->length)) {
        $domnodelist = $domnodelist->item(0)->getElementsByTagName('principal');

        if (!empty($domnodelist->length)) {
            if ($domnodelist->item(0)->hasAttributes()) {
                $domnode = $domnodelist->item(0)->attributes->getNamedItem('principal-id');

                if (!is_null($domnode)) {
                    $usrprincipalid = (int) $domnode->nodeValue;
                }
            }
        }
    }

    return $usrprincipalid;
}

/**
 * Check to see if a user exists on the Adobe connect server
 * searching by username
 * @param object $aconnect a connection_class object
 * @param $usrdata
 * @internal param object $userdata an object with username as a property
 * @return mixed user's principal-id of match is found || false if not
 * found or error occured
 */
function aconnect_user_exists($aconnect, $usrdata) {
    $params = array(
        'action' => 'principal-list',
        'filter-login' => $usrdata->username,
        'filter-type' => 'user',
    );

    if ($aconnect->request($params)) {
        return aconnect_get_user_principal_id($aconnect->get_xmlresponse());
    }

    return false;
}

/**
 * Delete a user from the Adobe connect server using the users
 * principalid
 * @param object $aconnect a connection_class object
 * @param int $principalid the principal-id of the user
 * @return boolean call status
 */

function aconnect_delete_user($aconnect, $principalid = 0) {

    if (empty($principalid)) {
        return false;
    }
    $params = array(
        'action' => 'principals-delete',
        'principal-id' => $principalid,
    );

    return $aconnect->request($params);
}

/**
 * Creates a new user on the Adobe Connect server.
 * Parses XML from a principal-update call and returns
 * the principal-id of the new user.
 *
 * @param $aconnect
 * @param object $usrdata an object with firstname,lastname,
 * username and email properties.
 * @internal param object $aconnet a connect_class object
 * @return mixed principal-id of the new user or false
 */
function aconnect_create_user($aconnect, $usrdata) {
    $principal_id = false;

    $params = array(
        'action' => 'principal-update',
        'first-name' => $usrdata->firstname,
        'last-name' => $usrdata->lastname,
        'login' => $usrdata->username,
        'password' => strtoupper(md5($usrdata->username . time())),
        'extlogin' => $usrdata->username,
        'type' => 'user',
        'send-email' => 'false',
        'has-children' => 0,
        'email' => $usrdata->email,
    );

    if ($aconnect->request($params)) {
        $dom = new DomDocument();
        $dom->loadXML($aconnect->get_xmlresponse());

        $domnodelist = $dom->getElementsByTagName('principal');

        if (!empty($domnodelist->length)) {
            if ($domnodelist->item(0)->hasAttributes()) {
                $domnode = $domnodelist->item(0)->attributes->getNamedItem('principal-id');

                if (!is_null($domnode)) {
                    $principal_id = (int) $domnode->nodeValue;
                }
            }
        }
    }
    return $principal_id;
}

function aconnect_add_user_group($aconnect, $group_principal_id, $usrprincipal) {
    // Check if user is in this group.

    $useringroup = false;

    $params = array(
        'action' => 'principal-list',
        'group-id' => $group_principal_id,
        'filter-principal-id' => $usrprincipal,
        'filter-is-member' => 'false'
    );

    if ($aconnect->request($params)) {
        $dom = new DomDocument();
        $dom->loadXML($aconnect->get_xmlresponse());
        $domnodelist = $dom->getElementsByTagName('principal');
        // User is not part of this group if a principal is returned.
        if (empty($domnodelist->length)) {
            $useringroup = true;
        } else {
            // Build request to add user to the group.
            $params = array(
                'action' => 'group-membership-update',
                'group-id' =>  $group_principal_id,
                'principal-id' => $usrprincipal,
                'is-member' => 'true'
            );
            $useringroup = $aconnect->request($params);
        }
    }
    return $useringroup;
}

function aconnect_get_host_group($aconnect) {
    $groupparams = array(
        'action' => 'principal-list',
        'filter-type' => 'live-admins'
    );
    if ($aconnect->request($groupparams)) {
        $dom = new DomDocument();
        $dom->loadXML($aconnect->get_xmlresponse());

        $domnodelist = $dom->getElementsByTagName('principal');
        if (!empty($domnodelist->length)) {
            if ($domnodelist->item(0)->hasAttributes()) {
                $domnode = $domnodelist->item(0)->attributes->getNamedItem('principal-id');
                if (!is_null($domnode)) {
                    $group_principal_id = (int) $domnode->nodeValue;
                    return $group_principal_id;
                }
            }
        }
    }
    return false;
}

function aconnect_assign_user_perm($aconnect, $usrprincipal, $meetingscoid, $type) {
    $params = array(
        'action' => 'permissions-update',
        'acl-id' => $meetingscoid, // Sco-id of meeting.
        'permission-id' => $type, //  Host, mini-host, view.
        'principal-id' => $usrprincipal, // Principal id of user you are looking at.
    );
    return $aconnect->request($params);
}

function aconnect_remove_user_perm($aconnect, $usrprincipal, $meetingscoid) {
    $params = array(
        'action' => 'permissions-update',
        'acl-id' => $meetingscoid, // Sco-id of meeting.
        'permission-id' => ADOBE_REMOVE_ROLE, //  Host, mini-host, view.
        'principal-id' => $usrprincipal, // Principal id of user you are looking at.
    );
    return $aconnect->request($params);
}

function aconnect_check_public_access($aconnect, $sco) {
    $params = array(
        'action' => 'permissions-info',
        'acl-id' => $sco, // Sco-id of meeting.
        'principal-id' => 'public-access', // Principal id of user you are looking at.
    );
    $public = false;
    if ($aconnect->request($params)) {
        $dom = new DomDocument();
        $dom->loadXML($aconnect->get_xmlresponse());
        $domnodelist = $dom->getElementsByTagName('permission');

        if (!empty($domnodelist->length)) {
            if ($domnodelist->item(0)->hasAttributes()) {
                $domnode = $domnodelist->item(0)->attributes->getNamedItem('permission-id');
                if (!is_null($domnode)) {
                    $public = ($domnode->value == 'view');
                }
            }
        }
    }
    return $public;
}

/**
 * @param $aconnect
 * @internal param connect_class $aconnect object
 * @param int $sco Sco-id of the folder/object
 * @return mixed Bool to determine if the call was successful
 */
function aconnect_make_public( $aconnect, $sco ) {
    $params = array('action' => 'permissions-update',
        'acl-id' => $sco,
        'principal-id' => 'public-access',
        'permission-id' => 'view'
    );
    return $aconnect->request($params);
}

/**
 * @param $aconnect
 * @internal param connect_class $aconnect object
 * @param int $sco sco-id of the folder/object
 * @return mixed bool to determine if the call was successful
 */
function aconnect_make_private($aconnect, $sco ) {
    $params = array('action' => 'permissions-update',
        'acl-id' => $sco,
        'principal-id' => 'public-access',
        'permission-id' => 'denied'
    );

    return $aconnect->request($params);
}

/**
 * Check if a user has a permission
 * @param object $aconnect a connect_class object
 * @param int $usrprincipal user principal-id
 * @param int $meetingscoid meeting sco-id
 * @param int $roletype can be ADOBE_PRESENTER, ADOBE_PARTICIPANT or ADOBE_REMOVE
 * @param bool $assign set to true if you want to assign the user the role type
 * set to false to just check the user's permission.  $assign parameter is ignored
 * if $roletype is ADOBE_REMOVE
 * @return boolean true if user has permission
 *
 */
function aconnect_check_user_perm($aconnect, $usrprincipal, $meetingscoid, $roletype, $assign = false) {
    $perm_type = '';
    $hasperm = false;

    switch ($roletype) {
        case ADOBE_PRESENTER:
            $perm_type = ADOBE_MINIADMIN_ROLE;
            break;
        case ADOBE_PARTICIPANT:
            $perm_type = ADOBE_VIEW_ROLE;
            break;
        case ADOBE_HOST:
            $perm_type = ADOBE_HOST_ROLE;
            break;
        case ADOBE_REMOVE:
            $perm_type = ADOBE_REMOVE_ROLE;
            break;
        default:
            break;
    }

    $params = array(
        'action' => 'permissions-info',
        'acl-id' => $meetingscoid,
        'filter-principal-id' => $usrprincipal,
    );

    if (ADOBE_REMOVE_ROLE != $perm_type) {
        $params['filter-permission-id'] = $perm_type;
    }

    if ($aconnect->request($params)) {
        $dom = new DomDocument();
        $dom->loadXML($aconnect->get_xmlresponse());

        $domnodelist = $dom->getElementsByTagName('permissions');

        if (!empty($domnodelist->length)) {
            $domnodelist = $domnodelist->item(0)->getElementsByTagName('principal');

            if (!empty($domnodelist->length)) {
                $hasperm = true;
            }
        }

        if (ADOBE_REMOVE_ROLE != $perm_type and $assign and !$hasperm) {
            // TODO: check return values of the two functions below
            // Assign permission to user.
            return aconnect_assign_user_perm($aconnect, $usrprincipal, $meetingscoid, $perm_type);
        } else if (ADOBE_REMOVE_ROLE == $perm_type) {
            // Remove user's permission.
            return aconnect_remove_user_perm($aconnect, $usrprincipal, $meetingscoid);
        }
    }
    return $hasperm;
}

/**
 * Remove a meeting
 * @param $aconnect
 * @internal param connect_class $aconnect object
 * @param int $scoid sco-id of the meeting
 * @return bool true of success false on failure
 */
function aconnect_remove_meeting($aconnect, $scoid) {
    $params = array(
        'action' => 'sco-delete',
        'sco-id' => $scoid,
    );

    return $aconnect->request($params);
}

/**
 * Move SCOs to the shared content folder
 * @param $aconnect
 * @internal param connect_class $aconnect object
 * @param array $scolist sco-ids as array keys
 * @return bool false if error or nothing to move true if a move occured
 */
function aconnect_move_to_shared($aconnect, $scolist) {
    // Get shared folder sco-id.
    $shscoid = aconnect_get_folder($aconnect, 'content');

    // Iterate through list of sco and move them all to the shared folder.
    if (!empty($shscoid)) {

        foreach ($scolist as $scoid => $data) {
            $params = array(
                'action' => 'sco-move',
                'folder-id' => $shscoid,
                'sco-id' => $scoid,
            );
            $aconnect->request($params);
        }
        return true;
    } else {
        return false;
    }
}

/**
 * Gets a list of roles that this user can assign in this context
 *
 * @param object $context the context.
 * @param int $rolenamedisplay the type of role name to display. One of the
 *      ROLENAME_X constants. Default ROLENAME_ALIAS.
 * @param bool $withusercounts if true, count the number of users with each role.
 * @param integer|object $user A user id or object. By default (null) checks the permissions of the current user.
 * @return array if $withusercounts is false, then an array $roleid => $rolename.
 *      if $withusercounts is true, returns a list of three arrays,
 *      $rolenames, $rolecounts, and $nameswithcounts.
 */
function adobeconnect_get_assignable_roles($context, $rolenamedisplay = ROLENAME_ALIAS, $withusercounts = false, $user = null) {
    global $USER, $DB;

    // Make sure there is a real user specified.
    if ($user === null) {
        $userid = !empty($USER->id) ? $USER->id : 0;
    } else {
        $userid = !empty($user->id) ? $user->id : $user;
    }

    if (!has_capability('moodle/role:assign', $context, $userid)) {
        if ($withusercounts) {
            return array(array(), array(), array());
        } else {
            return array();
        }
    }

    $parents = $context->get_parent_context_ids();
    $contexts = implode(',' , $parents);

    $params = array();
    $extrafields = '';
    if ($rolenamedisplay == ROLENAME_ORIGINALANDSHORT or $rolenamedisplay == ROLENAME_SHORT) {
        $extrafields .= ', r.shortname';
    }

    if ($withusercounts) {
        $extrafields = ', (SELECT count(u.id)
                             FROM {role_assignments} cra JOIN {user} u ON cra.userid = u.id
                            WHERE cra.roleid = r.id AND cra.contextid = :conid AND u.deleted = 0
                          ) AS usercount';
        $params['conid'] = $context->id;
    }

    if (is_siteadmin($userid)) {
        // Show all roles allowed in this context to admins.
        $assignrestriction = "";
    } else {
        $assignrestriction = "JOIN (SELECT DISTINCT raa.allowassign AS id
                                      FROM {role_allow_assign} raa
                                      JOIN {role_assignments} ra ON ra.roleid = raa.roleid
                                     WHERE ra.userid = :userid AND ra.contextid IN ($contexts)
                                   ) ar ON ar.id = r.id";
        $params['userid'] = $userid;
    }
    $params['contextlevel'] = $context->contextlevel;
    $sql = "SELECT r.id, r.name $extrafields
              FROM {role} r
              $assignrestriction
              JOIN {role_context_levels} rcl ON r.id = rcl.roleid
             WHERE rcl.contextlevel = :contextlevel
          ORDER BY r.sortorder ASC";
    $roles = $DB->get_records_sql($sql, $params);

    // Only include Adobe Connect roles.
    $param = array('shortname' => 'adobeconnectpresenter');
    $presenterid    = $DB->get_field('role', 'id', $param);

    $param = array('shortname' => 'adobeconnectparticipant');
    $participantid  = $DB->get_field('role', 'id', $param);

    $param = array('shortname' => 'adobeconnecthost');
    $hostid         = $DB->get_field('role', 'id', $param);

    foreach ($roles as $key => $data) {
        if ($key != $participantid and $key != $presenterid and $key != $hostid) {
            unset($roles[$key]);
        }
    }

    $rolenames = array();
    foreach ($roles as $role) {
        if ($rolenamedisplay == ROLENAME_SHORT) {
            $rolenames[$role->id] = $role->shortname;
            continue;
        }
        $rolenames[$role->id] = $role->name;
        if ($rolenamedisplay == ROLENAME_ORIGINALANDSHORT) {
            $rolenames[$role->id] .= ' (' . $role->shortname . ')';
        }
    }
    if ($rolenamedisplay != ROLENAME_ORIGINALANDSHORT and $rolenamedisplay != ROLENAME_SHORT) {
        $rolenames = role_fix_names($rolenames, $context, $rolenamedisplay);
    }

    if (!$withusercounts) {
        return $rolenames;
    }

    $rolecounts = array();
    $nameswithcounts = array();
    foreach ($roles as $role) {
        $nameswithcounts[$role->id] = $rolenames[$role->id] . ' (' . $roles[$role->id]->usercount . ')';
        $rolecounts[$role->id] = $roles[$role->id]->usercount;
    }
    return array($rolenames, $rolecounts, $nameswithcounts);
}

/**
 * This function accepts a username and an email and returns the user's
 * adobe connect user name, depending on the module's configuration settings
 *
 * @param string - moodle username
 * @param string - moodle email
 *
 * @return string - user's adobe connect user name
 */
function set_username($username, $email) {
    global $CFG;
    if (isset($CFG->adobeconnect_email_login) and !empty($CFG->adobeconnect_email_login)) {
        return $email;
    } else {
        return $username;
    }
}

/**
 * This function search through the user-meetings folder for a folder named
 * after the user's login name and returns the sco-id of the user's folder
 *
 * @param obj - adobe connection connection object
 * @param string - the name of the user's folder
 * @return mixed - sco-id of the user folder (int) or false if no folder exists
 *
 */
function aconnect_get_user_folder_sco_id($aconnect, $folder_name) {

    $scoid   = false;
    $usr_meet_scoid = aconnect_get_folder($aconnect, 'user-meetings');

    if (empty($usr_meet_scoid)) {
        return $scoid;
    }

    $params = array('action' => 'sco-expanded-contents',
                    'sco-id' => $usr_meet_scoid,
                    'filter-name' => $folder_name);

    if ($aconnect->request($params)) {

        $dom = new DomDocument();
        $dom->loadXML($aconnect->get_xmlresponse());
        $domnodelist = $dom->getElementsByTagName('sco');

        if (!empty($domnodelist->length)) {
            if ($domnodelist->item(0)->hasAttributes()) {
                $domnode = $domnodelist->item(0)->attributes->getNamedItem('sco-id');
                if (!is_null($domnode)) {
                    $scoid = (int) $domnode->nodeValue;
                }
            }
        }
    }

    return $scoid;
}

/**
 * This function returns the user's adobe connect login username based off of
 * the adobe connect module's login configuration settings (Moodle username or
 * Moodle email)
 *
 * @param int $userid the user id
 * @return mixed - user's login username or false if something bad happened
 */
function get_connect_username($userid) {
    global $DB;

    $username = '';
    $param    = array('id' => $userid);
    $record   = $DB->get_record('user', $param, 'id,username,email,idnumber');

    if (!empty($userid) && !empty($record)) {
        $userid = empty($record->idnumber) ? $record->username : $record->idnumber;
        $username = set_username($userid, $record->email);
    }
    return $username;
}


/**
 * @param $aconnect connect_class adobe connection connection object
 * @param $scoid mixed The recording sco-id (string or number)
 * @param $name String Recording name
 * @param $description String optional description
 * @return boolean based on success of the call
 */
function update_recording($aconnect, $scoid, $name, $description=null) {
    $params = array('action' => 'sco-update',
        'sco-id' => $scoid,
        'name' => $name);

    if ( $description != null) {
        $params += array( 'description' => $description);
    }
    return $aconnect->request($params);
}