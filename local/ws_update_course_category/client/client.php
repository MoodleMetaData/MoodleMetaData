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
 * Based on XMLRPC client for Moodle 2 - local_wstemplate
 *
 * This script does not depend of any Moodle code,
 * and it can be called from a browser.
 *
 * original @authorr Jerome Mouneyrac
 */

$serverurl = 'https://eclass-dev.srv.ualberta.ca';
// Copy the token from Site administration / Plugins / Web services / Manage tokens.
$token = '35ccac64ccde335ace2c246557482d32';
$functionname = 'local_ws_update_course_category';
$parameters = array('courseid' => 3, 'categoryid' => 2);

require_once('./curl.php');
$curl = new curl;

$webserviceurl = $serverurl . '/webservice/rest/server.php?wstoken=' . $token . '&wsfunction=' . $functionname;

echo "$webserviceurl \n";

$result = $curl->post($webserviceurl, $parameters);

echo $result;

// Example code from https://github.com/php-curl-class/php-curl-class.
if ($curl->error) {
    echo 'Error: ' . $curl->error_code . ': ' . $curl->error_message;
}
// You can show full output using var_dump($curl->response).
