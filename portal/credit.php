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

/*
 * THIS FILE NEEDS TO BE PROTECTED WITH PUB COOKIE
 */

session_start();

$error = "Unknown error";
$valid = false;

// Is there $_SERVER['REMOTE_USER'] set? Pub Cookie
// Server sets this to an empty string for some reason,  check if empty not just isset.
if (!empty($_SERVER["REDIRECT_REMOTE_USER"])) {
    $_SERVER["REMOTE_USER"] = $_SERVER["REDIRECT_REMOTE_USER"];
}
if (!empty($_SERVER['REMOTE_USER'])) {
    $user = $_SERVER['REMOTE_USER'];
    $valid = true;
} else {
    header('Location: index.php?err=1');
}

// Determine the server URL.
$port = $_SERVER['SERVER_PORT'];

if ($port == '80') {
    $protocol = "http://";
} else if ($port == '443') {
    $protocol = "https://";
} else {
    $error = "Unknown port number being used: " . $port;
    $valid = false;
}

$server = $protocol . $_SERVER['HTTP_HOST'];

// If there have been no errors...
if ($valid == true) {
    header('Location: ' . $server);
}


echo "<html><head><title>Eclass Login</title></head><body><div>";
echo '<p>Internal System Error ('.$error.')- Please contact <a href="mailto:eclass@ualberta.ca?subject=Portal Error&body=Error: '.
        $error.'">eclass@ualberta.ca</a> for further assistance.</p>';
echo '<p>Return to <a href="index.php">login page</a></p>';
echo '<br><br>';
echo "</div></body></html>";

