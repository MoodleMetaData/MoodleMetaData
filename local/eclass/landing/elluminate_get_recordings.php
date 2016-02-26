<?php
/**
 * Created by IntelliJ IDEA.
 * User: ggibeau
 * Date: 11-11-28
 * Time: 9:09 AM
 * To change this template use File | Settings | File Templates.
 */

/// Function to be run periodically according to the moodle cron
/// This function searches for things that need to be done, such
/// as sending out mail, toggling flags etc ...

define("MOODLE_INTERNAL", TRUE);
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_login();
require_capability('moodle/site:config', get_context_instance(CONTEXT_SYSTEM));

global $CFG;
global $DB;

require_once($CFG->dirroot . '/mod/elluminate/lib.php');

/// If the plug-in is not configured to connect to Elluminate, return.
if (empty ($CFG->elluminate_auth_username) || empty ($CFG->elluminate_auth_username)) {
    return true;
}
// Recordings that have not been synced
$new_recordings = 0;
// Number of meetings that exist in the moodle database
$num_meetings = 0;
// Recordings that exist in the moodle database
$existing_recordings = 0;


$timenow = time();

$sql = "SELECT el.id, el.meetingid FROM {elluminate} el WHERE el.timestart <= $timenow " .
       "AND el.meetingid IS NOT NULL";
$sql_params = array('timestart'=>$timenow);

/// Ensure that any new recordings on the server are stored for meetings created by Moodle.
if ($sessions = $DB->get_records_sql($sql, $sql_params)) {
    foreach ($sessions as $session) {
        $num_meetings++;
        $filter = 'meetingId = ' . $session->meetingid;
        if ($recordings = elluminate_list_all_recordings_for_meeting($session->meetingid)) {
            foreach ($recordings as $recording) {
                if ($DB->record_exists('elluminate', array('meetingid'=>$recording->meetingid))) {
                    if (!$DB->record_exists('elluminate_recordings', array('recordingid'=>$recording->recordingid))) {
                        $er = new stdClass;
                        $er->meetingid = $recording->meetingid;
                        $er->recordingid = $recording->recordingid;
                        $er->created = $recording->created;
                        $er->recordingsize = $recording->size;
                        $er->visible = 1;
                        $er->groupvisible = 1;
                        $DB->insert_record('elluminate_recordings', $er);
                        $new_recordings++;
                    }
                    else {
                         $existing_recordings++;
                    }
                }
            }
        }
    }
}

echo '<html><body>';
echo 'Number of Meetings: ' . $num_meetings . '<br>';
echo 'Number of recordings previously: ' . $existing_recordings . '<br>';
echo 'Number of new recordings found: ' . $new_recordings . '<br>';

echo '</body></html>';