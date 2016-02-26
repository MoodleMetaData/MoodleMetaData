<?php
/**
 * Created by IntelliJ IDEA.
 * User: ggibeau
 * Date: 11-06-14
 * Time: 1:25 PM
 * To change this template use File | Settings | File Templates.
 */
 
define("MOODLE_INTERNAL", TRUE);

require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
//Require Login to get course list

if(isset($_POST['username'])) {
    global $DB, $USER;
    $user = $_POST['username'];
    $olduser = $USER;

    if(!$USER = $DB->get_record('user',array('username' => $user), $fields='*')) {
        echo '0';
        exit;
    }
    if(!$courses = enrol_get_my_courses('id, shortname, modinfo', 'visible DESC,sortorder ASC')) {
        echo '0';
        exit;
    }
    echo "1";
}
else {
    echo '-1';
}
