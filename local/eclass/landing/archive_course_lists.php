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

define("MOODLE_INTERNAL", true);
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_login();
require_capability('moodle/site:config', context_system::instance());
global $DB, $CFG;

try {
    if (empty($_GET['term'])) {
        echo "Missing Term code - e.g term=1450";
        exit();
    } else {
        $termcode = $_GET['term'];
        if (!preg_match("/^[0-9]{4}$/", $termcode)) {
            echo "Term Code must be a 4 digit term code..";
            exit();
        }
    }

    echo "<div>";
    // Query to identify credit courses in terms equal to or less than the term provided.
    $sql = 'SELECT courseid, shortname, fullname, STRING_AGG(roles.firstname, \',\') AS firstnames, ' .
    'STRING_AGG(roles.lastname, \',\') AS lastnames, ' .
    'STRING_AGG(roles.email, \',\') AS emails, ' .
    'STRING_AGG(roles.idnumber, \',\') AS ccids FROM ( ' .
        'SELECT DISTINCT e.courseid ' .
            'FROM {enrol} e JOIN {cohort} co ON (e.customint1 = co.id) ' .
            'WHERE co.idnumber ~ \'^[0-9]{4}.[0-9]{5}\' ' .
            'GROUP BY e.courseid HAVING MAX(to_number(co.idnumber, \'9999D99999\')) < ? ' .
        ') AS clean LEFT JOIN {course} c ON (c.id = clean.courseid) ' .
        'LEFT JOIN (select distinct u.firstname, u.lastname, u.idnumber, u.email, cxt.instanceid ' .
            'from {role_assignments} ra ' .
            'JOIN {context} cxt ON(ra.contextid=cxt.id) ' .
            'JOIN {user} u ON(ra.userid = u.id) ' .
        'where roleid=3 and cxt.contextlevel=50) as roles on (c.id = roles.instanceid) ' .
        'WHERE c.idnumber LIKE \'%UOFAB%\' ' .
        'GROUP BY courseid, c.shortname, c.fullname ' .
        'ORDER BY courseid';

    $archive = $DB->get_recordset_sql($sql, array(($termcode + 1).'.00000'));
    if ($archive->valid()) {
        echo '"Course ID", "Course shortname", "Course fullname", "First name(s)", "Last name(s)", "Email(s)", "CCIDs"'.
            ', "Opt-Out flag" <br>';
        foreach ($archive as $record) {
                // Print out the course info for courses that will be archived.
                echo $record->courseid.', "'.$record->shortname.'", "'.$record->fullname.'", "'.$record->firstnames.
                    '", "'.$record->lastnames.'", "'.$record->emails.'", "'.$record->ccids.'", <br>';
        }
    } else {
        echo "No records found.";
    }
    $archive->close();
    echo "</div>";
} catch (Exception $e) {
    echo "FAIL! ".$e;
    return;
}