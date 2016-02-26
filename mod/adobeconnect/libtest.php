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
 * @author Josh Stagg (josh.stagg@ualberta.ca)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('locallib.php');

function adobe_connection_test($host = '', $port = 80, $username = '',
                               $password = '', $httpheader = '',
                               $emaillogin, $https = false) {

    if (empty($host) or
        empty($port) or (0 == $port) or
        empty($username) or
        empty($password) or
        empty($httpheader)) {

        echo "</p>One of the required parameters is blank or incorrect: <br />".
            "Host: $host<br /> Port: $port<br /> Username: $username<br /> Password: $password".
            "<br /> HTTP Header: $httpheader</p>";

        die();
    }

    $aconnect = new connect_class_dom($host,
        $port,
        $username,
        $password,
        '',
        $https);

    $params = array(
        'action' => 'common-info'
    );

    // Send common-info call to obtain the session key.
    echo '<p>Sending common-info call:</p>';
    $aconnect->create_request($params);
    $response = $aconnect->get_xmlresponse();
    if (!empty($response)) {
        // Get the session key from the XML response.
        $aconnect->read_cookie_xml($response);

        $cookie = $aconnect->get_cookie();
        if (empty($cookie)) {

            echo '<p>unable to obtain session key from common-info call</p>';
            echo '<p>xmlrequest:</p>';
            $doc = new DOMDocument();

            if ($doc->loadXML($aconnect->get_xmlrequest())) {
                echo '<p>' . htmlspecialchars($doc->saveXML()) . '</p>';
            } else {
                echo '<p>unable to display the XML request</p>';
            }

            echo '<p>xmlresponse:</p>';
            $doc = new DOMDocument();

            if ($doc->loadXML($aconnect->get_xmlresponse())) {
                echo '<p>' . htmlspecialchars($doc->saveHTML()) . '</p>';
            } else {
                echo '<p>unable to display the XML response</p>';
            }

        } else {

            // Print success.
            echo '<p style="color:#006633">successfully obtained the session key: ' . $aconnect->get_cookie() . '</p>';

            // Test logging in as the administrator.
            $params = array(
                'action' => 'login',
                'login' => $aconnect->get_username(),
                'password' => $aconnect->get_password(),
            );

            if ($aconnect->request($params)) {
                echo '<p style="color:#006633">successfully logged in as admin user</p>';

                // Test retrieval of folders.
                echo '<p>Testing retrevial of shared content, recording and meeting folders:</p>';
                $folderscoid = aconnect_get_folder($aconnect, 'content');

                if ($folderscoid) {
                    echo '<p style="color:#006633">successfully obtained shared content folder scoid: '. $folderscoid .
                        '</p>';
                } else {
                    echo '<p>error obtaining shared content folder</p>';
                    echo '<p style="color:#680000">XML request:<br />'. htmlspecialchars($aconnect->get_xmlrequest()).
                        '</p>';
                    echo '<p style="color:#680000">XML response:<br />'. htmlspecialchars($aconnect->get_xmlresponse()).
                        '</p>';
                }

                $folderscoid = aconnect_get_folder($aconnect, 'forced-archives');

                if ($folderscoid) {
                    echo '<p style="color:#006633">successfully obtained forced-archives (meeting recordings) folder scoid: '.
                        $folderscoid . '</p>';
                } else {

                    echo '<p>error obtaining forced-archives (meeting recordings) folder</p>';
                    echo '<p style="color:#680000">XML request:<br />'. htmlspecialchars($aconnect->get_xmlrequest()). '</p>';
                    echo '<p style="color:#680000">XML response:<br />'. htmlspecialchars($aconnect->get_xmlresponse()). '</p>';
                }

                $folderscoid = aconnect_get_folder($aconnect, 'meetings');

                if ($folderscoid) {
                    echo '<p style="color:#006633">successfully obtained meetings folder scoid: '. $folderscoid . '</p>';
                } else {
                    echo '<p>error obtaining meetings folder</p>';
                    echo '<p style="color:#680000">XML request:<br />'. htmlspecialchars($aconnect->get_xmlrequest()). '</p>';
                    echo '<p style="color:#680000">XML response:<br />'. htmlspecialchars($aconnect->get_xmlresponse()). '</p>';

                }

                // Test creating a meeting.
                $folderscoid = aconnect_get_folder($aconnect, 'meetings');

                $meeting = new stdClass();
                $meeting->name = 'testmeetingtest';
                $time = time();
                $meeting->starttime = $time;
                $time = $time + (60 * 60);
                $meeting->endtime = $time;

                if (($meetingscoid = aconnect_create_meeting($aconnect, $meeting, $folderscoid))) {
                    echo '<p style="color:#006633">successfully created meeting <b>testmeetingtest</b> scoid: '.
                        $meetingscoid . '</p>';
                } else {

                    echo '<p>error creating meeting <b>testmeetingtest</b> folder</p>';
                    echo '<p style="color:#680000">XML request:<br />'. htmlspecialchars($aconnect->get_xmlrequest()). '</p>';
                    echo '<p style="color:#680000">XML response:<br />'. htmlspecialchars($aconnect->get_xmlresponse()). '</p>';
                }

                // Test creating a user.
                $user = new stdClass();
                $user->username = 'testusertest';
                $user->firstname = 'testusertest';
                $user->lastname = 'testusertest';
                $user->email = 'testusertest@test.com';

                if (!empty($emaillogin)) {
                    $user->username = $user->email;
                }

                $skipdeletetest = false;

                if (!($usrprincipal = aconnect_user_exists($aconnect, $user))) {
                    $usrprincipal = aconnect_create_user($aconnect, $user);
                    if ($usrprincipal) {
                        echo '<p style="color:#006633">successfully created user <b>testusertest</b> principal-id: '.
                            $usrprincipal . '</p>';
                    } else {
                        echo '<p>error creating user  <b>testusertest</b></p>';
                        echo '<p style="color:#680000">XML request:<br />'. htmlspecialchars($aconnect->get_xmlrequest()). '</p>';
                        echo '<p style="color:#680000">XML response:<br />'. htmlspecialchars($aconnect->get_xmlresponse()). '</p>';

                        aconnect_logout($aconnect);
                        die();
                    }
                } else {

                    echo '<p>user <b>testusertest</b> already exists skipping delete user test</p>';
                    $skipdeletetest = true;
                }

                // Test assigning a user a role to the meeting.
                if (aconnect_check_user_perm($aconnect, $usrprincipal, $meetingscoid, ADOBE_PRESENTER, true)) {
                    echo '<p style="color:#006633">successfully assigned user <b>testusertest</b>'.
                        ' presenter role in meeting <b>testmeetingtest</b>: '. $usrprincipal . '</p>';
                } else {
                    echo '<p>error assigning user <b>testusertest</b> presenter role in meeting <b>testmeetingtest</b></p>';
                    echo '<p style="color:#680000">XML request:<br />'. htmlspecialchars($aconnect->get_xmlrequest()). '</p>';
                    echo '<p style="color:#680000">XML response:<br />'. htmlspecialchars($aconnect->get_xmlresponse()). '</p>';
                }

                // Test removing role from meeting.
                if (aconnect_check_user_perm($aconnect, $usrprincipal, $meetingscoid, ADOBE_REMOVE_ROLE, true)) {
                    echo '<p style="color:#006633">successfully removed presenter role for user <b>testusertest</b>'.
                        ' in meeting <b>testmeetingtest</b>: '. $usrprincipal . '</p>';
                } else {
                    echo '<p>error remove presenter role for user <b>testusertest</b> in meeting <b>testmeetingtest</b></p>';
                    echo '<p style="color:#680000">XML request:<br />'. htmlspecialchars($aconnect->get_xmlrequest()). '</p>';
                    echo '<p style="color:#680000">XML response:<br />'. htmlspecialchars($aconnect->get_xmlresponse()). '</p>';
                }

                // Test removing user from server.
                if (!$skipdeletetest) {
                    if (aconnect_delete_user($aconnect, $usrprincipal)) {
                        echo '<p style="color:#006633">successfully removed user <b>testusertest</b> principal-id: '.
                            $usrprincipal . '</p>';
                    } else {
                        echo '<p>error removing user <b>testusertest</b></p>';
                        echo '<p style="color:#680000">XML request:<br />'. htmlspecialchars($aconnect->get_xmlrequest()). '</p>';
                        echo '<p style="color:#680000">XML response:<br />'. htmlspecialchars($aconnect->get_xmlresponse()). '</p>';
                    }
                }

                // Test removing meeting from server.
                if ($meetingscoid) {
                    if (aconnect_remove_meeting($aconnect, $meetingscoid)) {
                        echo '<p style="color:#006633">successfully removed meeting <b>testmeetingtest</b> scoid: '.
                            $meetingscoid . '</p>';
                    } else {
                        echo '<p>error removing meeting <b>testmeetingtest</b> folder</p>';
                        echo '<p style="color:#680000">XML request:<br />'. htmlspecialchars($aconnect->get_xmlrequest()). '</p>';
                        echo '<p style="color:#680000">XML response:<br />'. htmlspecialchars($aconnect->get_xmlresponse()). '</p>';
                    }
                }

            } else {
                echo '<p style="color:#680000">logging in as '. $username . ' was not successful, check to see if the'.
                    ' username and password are correct </p>';
            }
        }

    } else {
        echo '<p style="color:#680000">common-info API call returned an empty document.'.
            ' Please check your settings and try again </p>';
    }

    aconnect_logout($aconnect);
}