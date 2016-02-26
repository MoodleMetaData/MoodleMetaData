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
 * List course instructor email addresses from provided term codes.
 *
 * @package    local
 * @category   eclass/landing
 * @author     Anthony Radziszewski radzisze@ualberta.ca
 */

define("MOODLE_INTERNAL", true);
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_login();
require_capability('moodle/site:config', context_system::instance());
global $DB, $CFG;

try {
    $input = required_param('term', PARAM_SEQUENCE);
    if (empty($input)) {
        echo "Missing Term code - e.g eclass.srv.ualberta.ca/local/eclass/landing/instructor_emails_list.php?term=1450,1460";
        exit();
    } else {
        $termcodes = str_getcsv($input);

        foreach ($termcodes as $termcode) {
            if (!preg_match("/^[0-9]{4}$/", $termcode)) {
                echo "Term Codes must be in a comma separated list of 4 digit term codes.";
                exit();
            }
        }
    }

    $queryterms = $DB->sql_like("co.idnumber", "'$termcodes[0]._____'");

    for ($i = 1; $i < count($termcodes); $i++) {
        $queryterms .= "or " . $DB->sql_like("co.idnumber", "'$termcodes[$i]._____'");
    }

    echo "<div>";
    // Query to identify emails in terms provided.
    $sql = 'select distinct u.email from {role_assignments} ra '.
        'JOIN {context} cxt ON(ra.contextid=cxt.id) '.
        'JOIN {user} u ON(ra.userid = u.id) '.
        'JOIN {course} c ON(cxt.instanceid=c.id) where ra.roleid=3 and cxt.contextlevel=50 '.
        'AND c.id IN (select e.courseid from {enrol} e, {cohort} co where e.customint1 = co.id '.
        'AND ('. $queryterms .'))';

    $emails = $DB->get_recordset_sql($sql);
    if ($emails->valid()) {
        foreach ($emails as $record) {
             // Print out the email addresses.
             echo $record->email.'<br>';
        }
    } else {
        echo "No records found.";
    }
    $emails->close();
    echo "</div>";
} catch (Exception $e) {
    $fail = <<<fail
    <pre>
                                    ','. '. ; : ,','
                                      '..'FAIL,..'
                                         ';.' ,'
                                          ;;
                                          ;'
                            :._   _.------------.___
                    __      :__:-'                  '--.
             __   ,' .'    .'             ______________'.
           /__ '.-  _\___.'          0  .' .'  .'  _.-_.'
              '._                     .-': .' _.' _.'_.'
                 '----'._____________.'_'._:_:_.-'--'
</pre>
fail;
    echo $fail.'<br>'.$e;
    return;
}