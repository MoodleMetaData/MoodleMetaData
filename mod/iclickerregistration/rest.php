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

require_once(dirname(__FILE__).'./../../config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/locallib.php');
require_once($CFG->dirroot . '/local/slim/Slim/Slim.php');

// Do these to ensure $COURSE is properly initialized.
$cmid = optional_param('course_module_id', 0, PARAM_INT);

// Note: This optional parameter is only here due to .NET iclicker app
//       can't really have any authentication access.
if ($cmid) {
    $cm = get_coursemodule_from_id('iclickerregistration', $cmid, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $iclickerregistration = $DB->get_record('iclickerregistration', array('id' => $cm->instance), '*', MUST_EXIST);
    require_login($course, true, $cm);
}
// If course_module_id is not given, it is assumed what course is not relevant.

\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();
// Routing.
$app->get('/iclickers', 'get_iclickers');
$app->get('/iclickers/:idnumber', 'get_iclicker');
$app->post('/iclickers/:idnumber', 'register_iclicker');
$app->put('/iclickers/:idnumber', 'update_iclicker');
$app->delete('/iclickers/:idnumber', 'delete_iclicker');
$app->get('/users/:idnumber', 'get_user');
$app->get('/generate_roster_file', 'generate_roster_file');
$app->get('/iclickers/show/:iclickerid', 'get_iclicker_by_iclicker_id');  // For i>grader rest call.
$app->run();

/**
 * Handles retrieving more than one  iclicker data.
 */
function get_iclickers() {
    global $app, $COURSE, $cm, $iru;

    $orderby = $app->request()->get('order_by') ? $app->request()->get('order_by') : "name";
    $ascending = boolean_string_to_boolean($app->request()->get('ascending'));
    $query = $app->request()->get('query') ? $app->request()->get('query') : "";
    $indexpair = $app->request()->get('index_pair') ? $app->request()->get('index_pair') : null;
    $adminmode = boolean_string_to_boolean($app->request()->get('admin_mode'));
    $hideunregistered = boolean_string_to_boolean($app->request()->get('hide_unregistered'));
    $filterconflicts = boolean_string_to_boolean($app->request()->get('filter_conflicts'));

    if ($adminmode) {
        // Only manager can view all of the users.
        if (!has_capability('mod/iclickerregistration:viewallusers', context_module::instance($cm->id))) {
            echo json_encode(array( "status" => "access denied"));
            return;
        }
    } else {
        // Only teachers can view all students in a course.
        if (!has_capability('mod/iclickerregistration:viewenrolled', context_module::instance($cm->id))) {
            echo json_encode(array( "status" => "access denied"));
            return;
        }
    }

    $usercount = 0;
    $allusers = $iru->get_all_users_left_join_iclickers(array(
        "courseid" => ($adminmode ? null : $COURSE->id),
        "hideunregistered" => ($hideunregistered ? true : false),
        "filterconflicts" => $filterconflicts,
        "orderby" => $orderby,
        "ascending" => $ascending,
        "query" => $query
    ));

    $allusers = array_values($allusers);
    $usercount = count($allusers);

    if ($indexpair) {
        $validindexpairarg = preg_match('/^(\d+)-(\d+)$/', "$indexpair", $matches) === 1;
        if ($validindexpairarg) {
            $index1 = $matches[1];
            $index2 = $matches[2];
            $indexoffset = $index2 - $index1;

            $usersresult = array_splice($allusers, $index1, $indexoffset);
        } else {
            $usercount = 0;
            $usersresult = array();
        }
    } else {
        $usersresult = $allusers;
    }

    $duplicatecount = $adminmode ? $iru->get_iclicker_id_duplicate_count() :
        $iru->get_iclicker_id_duplicate_count_in_course($COURSE->id);
    echo json_encode(array(
        "users" => $usersresult,
        "user_count" => $usercount,
        "duplicate_count" => $duplicatecount));
}

/**
 * @param $idnumber ccid of the user that we retrieve an iclicker registration from
 *                (one-one correspondence between iclicker and user).
 *                Set this to "current_user" to acquire the current user's
 *                registration.
 *
 * Note: if the user don't have an iclicker registered, the following is echoed back:
 * {
 *   id: null,
 *   idnumber: $idnumber,
 *   iclicker_id: $null
 * }
 */
function get_iclicker($idnumber) {
    global $USER, $cm, $course, $iru, $app;

    $adminmode = $app->request()->get('admin_mode') ? $app->request()->get('admin_mode') : false;
    $adminmode = filter_var($adminmode, FILTER_VALIDATE_BOOLEAN);  // Convert "true" string to boolean...

    // Return current user.
    if (is_current_user($idnumber)) {
        // A user can view his/her own iclicker.
        if (!has_capability('mod/iclickerregistration:viewown', context_module::instance($cm->id))) {
            echo json_encode(array( "status" => "access denied"));
            return;
        }

        $idnumber = $USER->idnumber;
    } else {
        // Only teachers/admins can view iclicker that is not their own.
        if (!has_capability('mod/iclickerregistration:viewenrolled', context_module::instance($cm->id))) {
            echo json_encode(array( "status" => "access denied"));
            return;
        }
    }

    $userismanuallyenrolled = isset($idnumber) === false || $idnumber === "";
    if ($userismanuallyenrolled === false &&
        $iru->is_user_already_registered_by_idnumber($idnumber)) {
        // Retrieve the id of the registered iclicker.
        $iclickeruser = $iru->get_iclicker_by_idnumber($idnumber);
        $duplicateprofile = $adminmode ?
            $iru->get_iclicker_user_duplicate_profile($iru->get_user_left_join_iclickers($idnumber)) :
            $iru->get_iclicker_user_duplicate_profile_in_course($iru->get_user_left_join_iclickers($idnumber), $course->id);
        echo json_encode(array(
            "id" => "$iclickeruser->id",
            "idnumber" => $idnumber,
            "iclicker_id" => "$iclickeruser->iclickerid",
            "duplicateprofile" => $duplicateprofile));
    } else if ($userismanuallyenrolled) {
        echo json_encode(array(
            "id" => null,
            "idnumber" => null,
            "iclicker_id" => null,
            "duplicateprofile" => []));
    } else {  // Not registered.
        echo json_encode(array(
            "id" => null,
            "idnumber" => $idnumber,
            "iclicker_id" => null,
            "duplicateprofile" => []));
    }
}

/**
 * Mainly used by i>grader rest calls for acquiring the user given an iclicker id.
 * @param $iclickerid
 */
function get_iclicker_by_iclicker_id($iclickerid) {
    global $iru;

    // TODO: Security hole!!! I can't place capability checks here unless we modify the .NET iclicker app to send
    //       some sort of authentication (proff types his/her own authentication).
    if ($iru->is_iclicker_id_already_registered($iclickerid)) {
        $iclickeruser = $iru->get_iclicker_by_iclicker_id($iclickerid);
        $time = strftime("%m/%e/%Y %l:%M:%S %p", $iclickeruser->timemodified);
        echo "1\t$iclickeruser->iclickerid\tfn\tln\t$iclickeruser->idnumber\t$time\r\n";
    } else {
        echo "";
    }
}

/**
 * @param $idnumber ccid of the user that we register an  iclicker for.
 */
function register_iclicker($idnumber) {
    global $app, $USER, $PAGE, $cm, $course, $iru;

    $requestobj = (array)json_decode($app->request->getBody());

    if (is_current_user($idnumber)) {
        // A user can edit his/her own iclicker.
        if (!has_capability('mod/iclickerregistration:viewown', context_module::instance($cm->id))) {
            trigger_access_denied_event();
            echo json_encode(array( "status" => "access denied"));
            return;
        }

        $idnumber = $USER->idnumber;
    } else {
        // Only admins can edit iclicker that is not their own.
        if (!has_capability('mod/iclickerregistration:editiclickerid', context_module::instance($cm->id))) {
            trigger_access_denied_event();
            echo json_encode(array( "status" => "access denied"));
            return;
        }
    }

    $iclickerobj = new stdClass;
    $iclickerobj->idnumber = $idnumber;
    $iclickerobj->iclickerid = $requestobj['iclicker_id'];
    $iclickerregistrationuserid = null;

    try {
        /*
         * Ensure that if iclicker is already registered, that it is ours.
         * Else, "throw" an error.
         */
        if ($iru->is_iclicker_id_duplicate_in_course($iclickerobj->iclickerid, $idnumber, $course->id)) {
            echo json_encode(array( "status" => "duplicate iclicker_id in same course"));
            return;
        }

        $iclickerregistrationuserid = $iru->register_iclicker_id($iclickerobj);
    } catch (invalid_iclicker_id $iii) {
        echo json_encode(array( "status" => "invalid iclicker_id format"));
        return;
    } catch (Exception $e) {
        echo json_encode(array( "status" => $e->getMessage()));
        return;
    }

    // If it reaches here, then we are successful.
    $event = \mod_iclickerregistration\event\register_iclicker::create(array(
        'objectid' => $iclickerregistrationuserid,
        'context' => $PAGE->context,
    ));
    $event->add_record_snapshot('course', $PAGE->course);
    $event->trigger();

    echo json_encode(array( "status" => 0));;
}

/**
 * @param $idnumber ccid of the user that we edit the iclicker id.
 */
function update_iclicker($idnumber) {
    global $app, $USER, $PAGE, $cm, $course, $iru;

    $requestobj = (array)json_decode($app->request->getBody());
    if (is_current_user($idnumber)) {
        // A user can edit his/her own iclicker.
        if (!has_capability('mod/iclickerregistration:viewown', context_module::instance($cm->id))) {
            trigger_access_denied_event();
            echo json_encode(array( "status" => "access denied"));
            return;
        }

        $idnumber = $USER->idnumber;
    } else {
        // Only admins can edit iclicker that is not their own.
        if (!has_capability('mod/iclickerregistration:editiclickerid', context_module::instance($cm->id))) {
            trigger_access_denied_event();
            echo json_encode(array( "status" => "access denied"));
            return;
        }
    }

    $iclickerobj = new stdClass;
    $iclickerobj->id = $requestobj['id'];
    $iclickerobj->idnumber = $requestobj['idnumber'];
    $iclickerobj->iclickerid = $requestobj['iclicker_id'];

    try {
        /*
         * Ensure that if iclicker is already registered, that it is ours.
         * Else, "throw" an error.
         */
        if ($iru->is_iclicker_id_duplicate_in_course($iclickerobj->iclickerid, $idnumber, $course->id)) {
            echo json_encode(array( "status" => "duplicate iclicker_id in same course"));
            return;
        }

        $iru->update_iclicker_id($iclickerobj);
    } catch (invalid_iclicker_id $iii) {
        echo json_encode(array( "status" => "invalid iclicker_id format"));
        return;
    } catch (Exception $e) {
        echo json_encode(array( "status" => $e->getMessage()));
        return;
    }

    // If it reaches here, then we are successful.
    $event = \mod_iclickerregistration\event\edit_iclicker::create(array(
        'objectid' => $iclickerobj->id,
        'context' => $PAGE->context,
    ));
    $event->add_record_snapshot('course', $PAGE->course);
    $event->trigger();

    echo json_encode(array( "status" => 0));;
}

/**
 * Note: DELETE request parameters are all in url (like GET).
 * @param $idnumber ccid of the user that we delete the iclicker id.
 */
function delete_iclicker($idnumber) {
    global $app, $USER, $PAGE, $cm, $iru;

    $iscurrentuser = is_current_user($idnumber);
    if ($iscurrentuser) {
        $idnumber = $USER->idnumber;
    }

    // Only admins can delete their own and all other iclicker.
    if ($iscurrentuser === false &&
        !has_capability('mod/iclickerregistration:editiclickerid', context_module::instance($cm->id))) {
        trigger_access_denied_event();
        echo json_encode(array( "status" => "access denied"));
        return;
    }

    try {
        $iru->delete_iclicker_id(array("idnumber" => $idnumber));
    } catch (Exception $e) {
        echo $e->getMessage();
        return;
    }

    // If it reaches here, then we are successful.
    $event = \mod_iclickerregistration\event\delete_iclicker::create(array(
        'context' => $PAGE->context,
    ));
    $event->add_record_snapshot('course', $PAGE->course);
    $event->trigger();

    echo json_encode(array( "status" => 0));;
}

/**
 * @param $idnumber ccid of the user that we retrieve.
 */
function get_user($idnumber) {
    global $app, $USER, $cm;

    if (is_current_user($idnumber)) {
        if (!has_capability('mod/iclickerregistration:viewown', context_module::instance($cm->id))) {
            trigger_access_denied_event();
            echo json_encode(array("status" => "access denied"));
            return;
        }

        $idnumber = $USER->idnumber;
    } else if ($idnumber === "") {
        // The idnumber (aka ccid) is what identifies students in this institution. Thus, this case is ignored.
        echo "No idnumber provided.";
        return;
    } else {
        // Only admins can edit iclicker that is not their own.
        if (!has_capability('mod/iclickerregistration:viewenrolled', context_module::instance($cm->id))) {
            echo json_encode(array( "status" => "access denied"));
            return;
        }
    }

    try {
        echo json_encode(get_user_by_idnumber($idnumber));
    } catch (Exception $e) {
        echo $e->getMessage();
        return;
    }
}

function trigger_access_denied_event() {
    global $PAGE;

    // If it reaches here, then we are successful.
        $event = \mod_iclickerregistration\event\access_denied::create(array(
        'context' => $PAGE->context,
    ));
    $event->add_record_snapshot('course', $PAGE->course);
    $event->trigger();
}

function generate_roster_file() {
    global $cm, $iru, $COURSE, $USER;

    // Only teachers/admins can view iclicker that is not their own.
    if (!has_capability('mod/iclickerregistration:viewenrolled', context_module::instance($cm->id))) {
        echo json_encode(array( "status" => "access denied"));
        return;
    }

    // RESOURCE LOCK.
    // For more info: https://docs.moodle.org/dev/Lock_API
    // Get an instance of the currently configured lock_factory.
    $resource = 'user: ' . $USER->id;
    $lockfactory = \core\lock\lock_config::get_lock_factory('mod_iclickerregistration');

    // ACQUIRE LOCK.
    $lock = $lockfactory->get_lock("$resource", 5 /* seconds */ );

    $allusers = $iru->get_all_users_left_join_iclickers(array("courseid" => $COURSE->id));

    $responsetext = "";
    foreach ($allusers as $iclickeruser) {
        $responsetext .= "$iclickeruser->lastname, $iclickeruser->firstname, $iclickeruser->idnumber\r\n";
    }

    $fs = get_file_storage();
    $fs->delete_area_files($cm->id, 'mod_iclickerregistration');

    $dummyfilename = 'roster.txt';
    $fs = get_file_storage();
    $filerecord = array('contextid'=>$cm->id, 'component'=>'mod_iclickerregistration', 'filearea'=>'intro',
        'itemid'=>0, 'filepath'=>'/', 'filename'=>$dummyfilename,
        'timecreated'=>time(), 'timemodified'=>time());
    $file = $fs->create_file_from_string($filerecord, $responsetext);

    // RELEASE LOCK.
    $lock->release();

    if (!$file) {
        return false;  // File don't exist.
    }

    // Force it to be csv. Otherwise, moodle throws an error for some reason.
    send_file($file, $dummyfilename, 0, true, false, false, get_mimetypes_array()['csv']['type']);
}