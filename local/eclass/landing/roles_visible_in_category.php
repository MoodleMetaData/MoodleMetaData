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

// Moodle settings and security based on capability.
define("MOODLE_INTERNAL", true);
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_login();
require_capability('moodle/site:config', context_system::instance());

global $DB, $CFG;

// Cat id.
$coursecatid = required_param('categoryid', PARAM_INT);

$categorycontext = context_coursecat::instance($coursecatid);

$roleswith = get_role_names_with_caps_in_context($categorycontext, array('moodle/course:viewhiddencourses'));
$body = '';
foreach ($roleswith as $role) {
    $body .= "$role<br/>";
}
echo $body;
