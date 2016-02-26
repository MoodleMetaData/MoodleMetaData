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
 * Landing page that lists filtered email addresses from criteria.
 * Uses the email_criteria_search lib to query based on parameters in url.
 *
 * @package    local
 * @category   eclass/landing
 * @author     Anthony Radziszewski radzisze@ualberta.ca
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define("MOODLE_INTERNAL", true);
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once(dirname(dirname(__FILE__)) . '/lib/email_criteria_search.php');
require_login();
require_capability('moodle/site:config', context_system::instance());
global $DB, $CFG;

try {
    $inputterm = required_param('term', PARAM_SEQUENCE);
    $inputcategory = required_param('category', PARAM_SEQUENCE);
    $inputrole = required_param('role', PARAM_SEQUENCE);
    $inputcourse = required_param('course', PARAM_SEQUENCE);
    $inputlastaccess = required_param('lastaccess', PARAM_INT);

    $termcodes = str_getcsv($inputterm);
    $categoryids = str_getcsv($inputcategory);
    $roleids = str_getcsv($inputrole);
    $courseids = str_getcsv($inputcourse);
    $lastaccess = str_getcsv($inputlastaccess);

    if (empty($termcodes[0]) && empty($categoryids[0]) && empty($roleids[0]) && empty($courseids[0]) && $lastaccess[0] == 0) {
        echo 'Missing a criteria code, please enter at least 1 criteria to filter on. For example, the link below will show
        all emails of students enrolled in term code 1450:' . '<br>';
        $linkaddress = "eclass.srv.ualberta.ca/local/eclass/landing/criteria_emails_list.php?term=1450".
        "&category=&role=5&course=&lastaccess=";
        echo "<a href='https://".$linkaddress."'>eclass.srv.ualberta.ca/local/eclass/landing/criteria_emails_list.php?term=1450".
        "&category=&role=5&course=&lastaccess=</a>";
        exit();
    }

    $searcher = new email_search();

    if (!empty($termcodes[0])) {
        foreach ($termcodes as $termcode) {
            $searcher->addterm($termcode);
        }
    }

    if (!empty($categoryids[0])) {
        foreach ($categoryids as $categoryid) {
            $searcher->addcategory($categoryid);
        }
    }

    if (!empty($roleids[0])) {
        foreach ($roleids as $roleid) {
            $searcher->addrole($roleid);
        }
    }

    if (!empty($courseids[0])) {
        foreach ($courseids as $courseid) {
            $searcher->addcourse($courseid);
        }
    }

    if (!empty($lastaccess[0])) {
        $searcher->setlastaccess($lastaccess[0]);
    }

    echo "<div>";

    $emails = $searcher->getemails();
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