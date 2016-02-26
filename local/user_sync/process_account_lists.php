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
 * User Account Sync
 *
 * This script executes
 *
 * @package    eClass
 * @subpackage cli
 * @copyright  2013 Trevor Jones
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Created by IntelliJ IDEA.
 * User: tdjones
 * Date: 2013-08-22
 * Time: 9:38 AM
 *
 */

define('CLI_SCRIPT', true);

require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->libdir . '/adminlib.php');
require_once('logger.php');

$_SERVER['HTTP_USER_AGENT'] = '';

list($options, $unrecognized) =
    cli_get_params(array('help' => false, 'peoplesoft_list' => dirname(__FILE__) . '/fixtures/enrolled_users.csv',
            'moodle_list' => '', 'adobeconnect' => null, 'business_list' => '', 'business_hosts' => ''),
        array('h' => 'help', 'p' => 'peoplesoft_list', 'm' => 'moodle_list', 'a' => 'adobeconnect',
            'b' => 'business_list', 'o' => 'business_hosts'));

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    $help =
        "Syncronizes users in Moodle and optionally Adobe connect with peoplesoft data.

Options:
-h, --help            Print out this help
-m, --moodle_list     (Optional) Path to the Moodle csv file, expected format: ccid, emplid, firstname, lastname
-p, --peoplesoft_list (Required) Path to the Peoplesoft csv file, expected format: ccid, emplid, firstname, lastname
-a, --adobeconnect    (Optional) If present, enables adobe connect integration for syncing.
-b, --business_list   (Optional) Path to the Business csv file, if provided enables business adobe connect account
 creation
-o, --business_hosts  (Optional) Path to the Business csv file, if provided enables business adobe connect host account
 creation
Example:
php process_account_lists.php -p /path/people_soft.csv -a -b /path/business.csv
        ";

    echo $help;
    die;
}

if (moodle_needs_upgrading()) {
    echo "Moodle upgrade pending, user sync execution suspended.\n";
    exit(1);
}

$logger = new mail_logger('listCompare.php');
$logger->setstarttime();
$logger->setstatus('SUCCESS');

define('LASTNAME', 3);
define('FIRSTNAME', 2);
define('EMPID', 1);
define('CCID', 0);

// Setup defaults.
if (!isset($CFG->user_sync_persist_to_db)) {
    $CFG->user_sync_persist_to_db = false; // Disable database persistence by default.
}
if (!isset($CFG->delete_threshold)) {
    $CFG->delete_threshold = 1000;
}
if (!isset($CFG->update_threshold)) {
    $CFG->update_threshold = 1000;
}

$moodleinputfile = $options['moodle_list'];
$peoplesoftinputfile = $options['peoplesoft_list'];
$businessinputfile = $options['business_list'];
$businesshostsinputfile = $options['business_hosts'];

$musers = array();
$pusers = array();
$busers = array();
$bhusers = array();
date_default_timezone_set('America/Edmonton');

$message = "--- " . date('c') . " ---";
cli_heading($message);
$logger->postmessage($message);

if (!empty($moodleinputfile)) {
    // Expected format: ccid,empid,firstname,lastname.
    $fhmof = fopen($moodleinputfile, "r");
    // Build a hash of current moodle people.
    while ($line = fgetcsv($fhmof)) {
        // Store by empid.
        $musers[$line[EMPID]] = $line;
    }
    fclose($fhmof);
} else {
    $usersrs = $DB->get_recordset('user', null, '', 'idnumber,username,firstname,lastname');
    foreach ($usersrs as $usr) {
        if (preg_match('/^\d+$/', $usr->username)) {
            $musers[$usr->username] = array($usr->idnumber, $usr->username, $usr->firstname, $usr->lastname);
        }
    }
}

$fhpof = fopen($peoplesoftinputfile, "r");
// Build a hash of current peoplesoft people.
while ($line = fgetcsv($fhpof)) {
    // Store by empid.
    $pusers[$line[EMPID]] = $line;
}
fclose($fhpof);

if (file_exists($CFG->dirroot . '/mod/adobeconnect') and !empty($options['adobeconnect'])) {
    require_once('lib/connect_sync.php');
    define("ACONNECT_ENABLED", true);
    $logger->postmessage('Adobe Connect sync ENABLED.');
} else {
    define("ACONNECT_ENABLED", false);
    $logger->postmessage('Adobe Connect sync disabled.');
}

// Build hash of business people.
if (ACONNECT_ENABLED) {
    if (!empty($options['business_list'])) {
        $fhbof = fopen($businessinputfile, "r");
        while ($line = fgetcsv($fhbof)) {
            // Store by empid.
            $busers[$line[EMPID]] = 1;
        }
        fclose($fhbof);
    }
    if (!empty($options['business_hosts'])) {
        $fhbhof = fopen($businesshostsinputfile, "r");
        while ($line = fgetcsv($fhbhof)) {
            // Store by empid.
            $bhusers[$line[EMPID]] = 1;
        }
        fclose($fhbhof);
    }
}

$stattotaloriginalmoodleusers = count($musers);
$stattotaloriginalpeoplesoftusers = count($pusers);
$stattotaloriginalbusinessusers = count($pusers);
$stattotalcreatedmoodleusers = 0;
$stattotaldeletedmoodleusers = 0;
$stattotalupdatedmoodleusers = 0;
$stattotalpreviouslydeletedmoodleusers = 0;

$queryqueue = array();
$deletequeue = array();
$createqueue = array();
$updatequeue = array();

foreach ($pusers as $empid => $info) {
    // If user is missing from moodle.
    if (!isset($musers[$empid])) {
        // Update the hash to contain the user. This prevents duplicates from being processed.
        $message = "Found Missing user {$empid} with CCID: {$info[CCID]}\n";
        echo $message;
        $musers[$empid] = $info;
        array_push($createqueue, $info);
        $stattotalcreatedmoodleusers += 1;
    } else if ($musers[$empid][CCID] != $info[CCID]) {
        // User exists but CCID has changed; undelete if deleted!
        $message = "Found changed CCID for user {$empid}, CCID changed {$musers[$empid][CCID]}->{$info[CCID]}\n";
        echo $message;
        $musers[$empid] = $info;

        // Store it for later.
        array_push($updatequeue, $info);
        $stattotalupdatedmoodleusers += 1;
    }
}

foreach ($musers as $empid => $info) {
    // If user is missing from peoplesoft list.
    if (!isset($pusers[$empid]) && !preg_match("/deleted_user/", $info[CCID])) {
        // Update the hash to contain the user. This prevents duplicates from being processed.
        $message = "Found deleted user {$empid} with CCID: {$info[CCID]}\n";
        echo $message;
        $pusers[$empid] = $info;

        // Store it for later.
        array_push($deletequeue, $empid);
        $stattotaldeletedmoodleusers += 1;
    } else if (preg_match("/deleted_user/", $info[CCID])) {
        $stattotalpreviouslydeletedmoodleusers += 1;
    }
}

// Run the queries if none of the thresholds have been exceeded.
if (($stattotaldeletedmoodleusers <= $CFG->delete_threshold) AND
    ($stattotalupdatedmoodleusers <= $CFG->update_threshold)
) {
    foreach ($deletequeue as $username) {
        if ($CFG->user_sync_persist_to_db) {
            $user = $DB->get_record('user', array('username' => $username), '*');

            if (ACONNECT_ENABLED) {
                // Make a copy of user record for adobe connect.
                $usercopy = clone($user);
                $tmp = $usercopy->username;
                $usercopy->username = $usercopy->idnumber;
                $usercopy->idnumber = $tmp;
            }

            // Moodle Delete.
            delete_user($user);
            // Now augment user object for updating.
            $newuser = new stdClass();
            $newuser->id = $user->id;
            $newuser->idnumber = 'deleted_user.' . time();
            $newuser->username = $user->username;

            $DB->update_record('user', $newuser);

            if (ACONNECT_ENABLED) {
                connectsync_delete_user($usercopy);
            }
        } else {
            $message = 'Delete of user ' . $username . " deferred by CFG->user_sync_persist_to_db.\n";
            echo $message;
        }
    }
    foreach ($updatequeue as $info) {
        if ($CFG->user_sync_persist_to_db) {
            $sql = "UPDATE {user} SET idnumber=?, deleted=0, email=? where username=?";
            $params = array($info[CCID], $info[CCID] . "@ualberta.ca", $info[EMPID]);
            if (!$DB->execute($sql, $params)) {
                $message = 'Error running query_queue: ' . $info[EMPID] . ',' . $info[CCID];
                cli_problem($message);
                $logger->postmessage($message);
                $logger->setstatus('SUCCESS with some FAILURES');
            } else {
                if (ACONNECT_ENABLED) {
                    $user = $DB->get_record('user', array('username' => $info[EMPID]), '*');
                    $usercopy = clone($user);
                    $tmp = $usercopy->username;
                    $usercopy->username = $usercopy->idnumber;
                    $usercopy->idnumber = $tmp;
                    connectsync_update_user($usercopy);
                }
            }
        } else {
            $message = $sql . "\n";
            echo $message;
        }
    }
    foreach ($createqueue as $userinfo) {
        if ($CFG->user_sync_persist_to_db) {
            $user = new stdClass();
            $user->auth = 'pubcookie';
            $user->confirmed = 1;
            $user->username = $userinfo[EMPID];
            $user->idnumber = $userinfo[CCID];
            $user->firstname = $userinfo[FIRSTNAME];
            $user->lastname = $userinfo[LASTNAME];
            $user->email = $userinfo[CCID] . '@ualberta.ca';
            $user->mnethostid = $CFG->mnet_localhost_id;
            $user->lang = "en";
            $user->autosubscribe = 0;
            $user->trackforums = 1;

            try {
                if (!user_create_user($user)) {
                    $message = 'Unable to create user: ' . $userinfo[EMPID] . ',' . $userinfo[CCID];
                    cli_problem($message);
                    $logger->postmessage($message);
                    $logger->setstatus('SUCCESS with some FAILURES');
                } else {
                    if (ACONNECT_ENABLED) {
                        if (!empty($options['business_list']) and is_business_user($userinfo[EMPID])) {
                            $usercopy = clone($user);
                            $tmp = $usercopy->username;
                            $usercopy->username = $usercopy->idnumber;
                            $usercopy->idnumber = $tmp;
                            connectsync_create_user($usercopy);
                        }
                        if (!empty($options['business_hosts']) and is_business_host($userinfo[EMPID])) {
                            $usercopy = clone($user);
                            $tmp = $usercopy->username;
                            $usercopy->username = $usercopy->idnumber;
                            $usercopy->idnumber = $tmp;
                            $id = connectsync_create_user($usercopy);
                            connectsync_add_user_to_host_group($id);
                        }
                    }
                }
            } catch (Exception $e) {
                $message = "Caught Exception: " . $e->getMessage();
                echo $message;
                $logger->postmessage($message);
                $logger->setstatus('SUCCESS with some FAILURES');
            }
        } else {
            $message = "Create user: " . $userinfo[EMPID] . ',' . $userinfo[CCID] . ',' . $userinfo[FIRSTNAME] . ',' .
                $userinfo[LASTNAME] .
                "\n";
            echo $message;
        }
    }
} else {

    $message = "Threshold Exceeded:\nDelete: $stattotaldeletedmoodleusers \nUpdate: $stattotalupdatedmoodleusers";
    $logger->log($message);
    cli_problem($message);
    $logger->setstatus(<<<FAILWHALE
▄██████████████▄▐█▄▄▄▄█▌
██████▌▄▌▄▐▐▌███▌▀▀██▀▀
████▄█▌▄▌▄▐▐▌▀███▄▄█▌
▄▄▄▄▄██████████████▀
FAILWHALE
    );
}

/**
 * @param $empid String employee id
 * @return bool
 */
function is_business_user($empid) {
    global $busers;
    return isset($busers[$empid]);
}

function is_business_host($empid) {
    global $bhusers;
    return isset($bhusers[$empid]);
}

$stats = <<<STATS
Stats:
Original Moodle User Count: $stattotaloriginalmoodleusers
Original Peoplesoft User Count: $stattotaloriginalpeoplesoftusers
Created Moodle User Count: $stattotalcreatedmoodleusers
Deleted Moodle User Count: $stattotaldeletedmoodleusers
Previously Deleted Moodle User Count: $stattotalpreviouslydeletedmoodleusers
Updated Moodle User Count: $stattotalupdatedmoodleusers

STATS;

echo $stats;
$logger->postmessage($stats);

$logger->setendtime();
$logger->mailandpost();

