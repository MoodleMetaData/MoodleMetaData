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
 * Bulk user creation
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

require(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->libdir.'/clilib.php');      // cli only functions
require_once($CFG->dirroot.'/user/lib.php');

$_SERVER['HTTP_USER_AGENT'] = '';
// now get cli options
list($options, $unrecognized) =
    cli_get_params(array('help'=>false,'account_list'=>'','hosts'=>NULL),
                   array('h'=>'help','a'=>'account_list','o'=>'hosts'));

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    $help =
        "Bulk creates users on the adobe system

        Options:
        -h, --help            Print out this help
        -a, --account_list (Required) Path to the Peoplesoft csv file, expected format: ccid, emplid, firstname, lastname
        -o, --hosts         (Optional) Make accounts hosts
        Example:
        php bulk_create_accounts.php -a=/path/users.csv -o
        ";

    echo $help;
    die;
}

require_once($CFG->libdir.'/adminlib.php');


define('LASTNAME',3);
define('FIRSTNAME',2);
define('EMPID', 1);
define('CCID', 0);

$host_input_file = $options['account_list'];


if(file_exists($CFG->dirroot . '/mod/adobeconnect')){
    require_once('lib/connect_sync.php');
    define("ACONNECT_ENABLED", true);
} else {
    define("ACONNECT_ENABLED", false);
}
$stat_total_created_moodle_users = 0;
// Build hash of business people
if(ACONNECT_ENABLED){
    if(!empty($host_input_file)){
        $fh_bof = fopen($host_input_file, "r");
        while ($a_line = fgetcsv($fh_bof)) {
            $user = new stdClass();
            $user->auth = 'pubcookie';
            $user->confirmed = 1;
            $user->username = $a_line[CCID];
            $user->idnumber = $a_line[EMPID];
            $user->firstname = $a_line[FIRSTNAME];
            $user->lastname = $a_line[LASTNAME];
            $user->email = $a_line[CCID].'@ualberta.ca';
            $user->mnethostid = $CFG->mnet_localhost_id;
            $user->lang = "en";
            $user->autosubscribe = 0;
            $user->trackforums = 1;

            if(ACONNECT_ENABLED){
                $id = connectsync_create_user($user);
                if(isset($options['hosts'])){
                    connectsync_add_user_to_host_group($id);
                }
                $stat_total_created_moodle_users += 1;
            }
        }
        fclose($fh_bof);
    }
}

echo <<<STATS
Stats:
Created User Count: $stat_total_created_moodle_users

STATS;
