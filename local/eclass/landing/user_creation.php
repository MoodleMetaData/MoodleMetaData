<?php
/**
 * Created by IntelliJ IDEA.
 * User: tdjones
 * Date: 11-05-11
 * Time: 9:15 AM
 * To change this template use File | Settings | File Templates.
 */


define("MOODLE_INTERNAL", TRUE);

require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
//Require Login to get course list
try {
    require_login(NULL, false, NULL, false, false);
        $context = get_context_instance(CONTEXT_SYSTEM);
    require_capability('moodle/user:create', $context);
}
    //if an exception is thrown then user was not logged in
catch (Exception $e) {
    echo "Not Authorized";
    return;
}
echo "You are authorized!";

try {
    $a_users = array();
    if (isset($_FILES) && isset($_FILES['users'])) {
        $file = $_FILES['users'];
        if ($file['error'] != UPLOAD_ERR_OK) {
            throw new Exception("File Upload Error: " . $file['error']);
        }
        $fh = fopen($file['tmp_name'], 'r');
        //      expects: username,firstname,lastname,email,idnumber
        fgets($fh); //skip the first line
        while ($a_line = fgetcsv($fh, 255, ',', '"')) {
            $a_user = array();
            $a_user['username'] = $a_line[0];
            $a_user['password'] = "empty";
            $a_user['firstname'] = $a_line[1];
            $a_user['lastname'] = $a_line[2];
            $a_user['email'] = $a_line[3];
            $a_user['auth'] = 'ldap';
            $a_user['idnumber'] = $a_line[4];
            $a_user['lang'] = 'en';
            array_push($a_users, $a_user);
        }
        print_r(create_users($a_users));
    }
    else {
        echo "Error: Missing File!";
        return;
    }
}
catch (Exception $e) {
    echo $e->getMessage();
    return;
}


/**
 * array(
'username'    => new external_value(PARAM_RAW, 'Username policy is defined in Moodle security config'),
'password'    => new external_value(PARAM_RAW, 'Plain text password consisting of any characters'),
'firstname'   => new external_value(PARAM_NOTAGS, 'The first name(s) of the user'),
'lastname'    => new external_value(PARAM_NOTAGS, 'The family name of the user'),
'email'       => new external_value(PARAM_EMAIL, 'A valid and unique email address'),
'auth'        => new external_value(PARAM_SAFEDIR, 'Auth plugins include manual, ldap, imap, etc', VALUE_DEFAULT, 'manual', NULL_NOT_ALLOWED),
'idnumber'    => new external_value(PARAM_RAW, 'An arbitrary ID code number perhaps from the institution', VALUE_DEFAULT, ''),
'lang'        => new external_value(PARAM_SAFEDIR, 'Language code such as "en", must exist on server', VALUE_DEFAULT, $CFG->lang, NULL_NOT_ALLOWED),
 */

/**
 * Create one or more users
 *
 * @param array $users  An array of users to create.
 * @return array An array of arrays
 */
function create_users($users) {
    global $CFG, $DB;
    require_once($CFG->dirroot . "/user/lib.php");
    require_once($CFG->dirroot . "/user/profile/lib.php"); //required for customfields related function
    //TODO: move the functions somewhere else as
    //they are "user" related
    $availableauths = get_plugin_list('auth');
    $availablethemes = get_plugin_list('theme');
    $availablelangs = get_string_manager()->get_list_of_translations();

    $transaction = $DB->start_delegated_transaction();

    $userids = array();
    foreach ($users as $user) {
        // Make sure that the username doesn't already exist
        if ($DB->record_exists('user', array('username' => $user['username'], 'mnethostid' => $CFG->mnet_localhost_id))) {
            //            $userids[] = array('id' => $user['id'], 'username' => $user['username'], 'error'=>'Username already exists: ' . $user['username']);
            $user_rec = $DB->get_record('user',array('username'=>$user['username']));
            $user['id'] = $user_rec->id;
            unset($user['password']);
            unset($user['auth']);
            user_update_user($user);
            $userids[] = array('id' => $user['id'], 'username' => $user['username'], 'error' => 'Updated');
            continue;
        }

        // Make sure auth is valid
        if (empty($availableauths[$user['auth']])) {
            $userids[] = array('id' => $user['id'], 'username' => $user['username'], 'error' => 'Invalid authentication type: ' . $user['auth']);
            continue;
        }

        // Make sure lang is valid
        if (empty($availablelangs[$user['lang']])) {
            $userids[] = array('id' => $user['id'], 'username' => $user['username'], 'error' => 'Invalid language code: ' . $user['lang']);
            continue;
        }

        // Make sure lang is valid
        if (!empty($user['theme']) && empty($availablethemes[$user['theme']])) { //theme is VALUE_OPTIONAL,
            // so no default value.
            // We need to test if the client sent it
            // => !empty($user['theme'])
            $userids[] = array('id' => $user['id'], 'username' => $user['username'], 'error' => 'Invalid theme: ' . $user['theme']);
            continue;
        }

        // make sure there is no data loss during truncation
        $truncated = truncate_userinfo($user);
        foreach ($truncated as $key => $value) {
            if ($truncated[$key] !== $user[$key]) {
                $userids[] = array('id' => $user['id'], 'username' => $user['username'], 'error' => 'Property: ' . $key . ' is too long: ' . $user[$key]);
                continue;
            }
        }

        $user['confirmed'] = true;
        $user['mnethostid'] = $CFG->mnet_localhost_id;
        $user['id'] = user_create_user($user);

        //        // custom fields
        //        if (!empty($user['customfields'])) {
        //            foreach ($user['customfields'] as $customfield) {
        //                $user["profile_field_" . $customfield['type']] = $customfield['value']; //profile_save_data() saves profile file
        //                //it's expecting a user with the correct id,
        //                //and custom field to be named profile_field_"shortname"
        //            }
        //            profile_save_data((object)$user);
        //        }
        //
        //        //preferences
        //        if (!empty($user['preferences'])) {
        //            foreach ($user['preferences'] as $preference) {
        //                set_user_preference($preference['type'], $preference['value'], $user['id']);
        //            }
        //        }

        $userids[] = array('id' => $user['id'], 'username' => $user['username'], 'error' => "");
    }

    $transaction->allow_commit();

    return $userids;
}