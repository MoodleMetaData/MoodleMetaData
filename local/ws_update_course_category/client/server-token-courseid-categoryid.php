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

if (count($argv) != 5) {
    echo "Usage: php $argv[0] <server> <webservice_token> <courseid> <categoryid>\n";
    echo "e.g.: php $argv[0] https://eclass-dev.srv.ualberta.ca 35ccac64ccde335ace2c246557482d32 2 1\n";
    echo "Token can be found in Moodle under:\n";
    echo " Site administration / Plugins / Web services / Manage tokens.\n";
    echo "Using defaults.\n\n";
}

$serverurl = isset($argv[1]) ? $argv[1] : 'https://eclass-dev.srv.ualberta.ca';
$token = isset($argv[2]) ? $argv[2] : '35ccac64ccde335ace2c246557482d32';
$courseid = isset($argv[3]) ? intval($argv[3]) : 3;
$categoryid = isset($argv[4]) ? intval($argv[4]) : 2;

$parameters = array('courseid' => 3, 'categoryid' => 2);
$functionname = 'local_ws_update_course_category';
$webserviceurl = $serverurl . '/webservice/rest/server.php?wstoken=' . $token . '&wsfunction=' . $functionname;

echo "$webserviceurl \n";
echo "Moving course $courseid to category $categoryid.\n\n";

require_once('./curl.php');
$curl = new curl;

$result = $curl->post($webserviceurl, $parameters);

echo $result;

if ($curl->error) {
    echo 'Error: ' . $curl->error_code . ': ' . $curl->error_message;
}
// You can view the full output using var_dump($curl->response).
