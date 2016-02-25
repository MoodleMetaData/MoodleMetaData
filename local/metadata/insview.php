<?php
global $PAGE, $CFG, $DB, $USER;
require_once('../../config.php');
require_once 'lib.php';


// Check that they can access
require_login();

// TODO: Get permissions working
$courseId = get_course_id();
$context = context_course::instance($courseId);
//require_capability('local/metadata:ins_view', $context);


require_once($CFG->dirroot.'/local/metadata/general_form.php');
require_once($CFG->dirroot.'/local/metadata/assessment_form.php');
require_once($CFG->dirroot.'/local/metadata/session_form.php');

// Define global variable for DB result
$course = $DB->get_record('course', array('id'=>$courseId), '*', MUST_EXIST);
    
// Set up page information
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('ins_pluginname', 'local_metadata'));
$heading = sprintf(get_string('instructor_heading', 'local_metadata'), $course->shortname, $course->fullname);
$PAGE->set_heading($heading);
$PAGE->set_url($CFG->wwwroot.'/local/metadata/insview.php');
$PAGE->requires->js('/local/metadata/tabview.js');

// Create forms
$base_url = create_insview_url($courseId);
$general_form = new general_form($base_url);
$assessment_form = new assessment_form($base_url);

$sessions = get_table_data_for_course('coursesession');
$session_form = new session_form($base_url, array('sessions' => $sessions));

$general_url = create_insview_url($courseId);
$assessment_url = create_insview_url($courseId, 1);
$session_url = create_insview_url($courseId, 2);


// Case where they cancelled the form. Just redirect to it, to reset values
if ($general_form->is_cancelled()) {
    redirect($general_url);
} else if ($assessment_form->is_cancelled()) {
    redirect($assessment_url);
} else if ($session_form->is_cancelled()) {
    redirect($session_url);
}


// Submitted the data
if ($data = $general_form->get_data()) {
    // TODO: Save the submission data, use a function/class from different file
    echo "General";
    print_object($data);

    $course_info = new stdClass();
    $course_info->courseid = $courseId;
    $course_info->courseinstructor = $USER->id;   
    $course_info->coursedescription = $data->course_description;
    $course_info->coursetopic = $data->course_topic;
    $course_info->coursefaculty = $data->course_faculty;
    $course_info->assessmentnumber = $data->assessment_counter;
    $course_info->sessionnumber = $data->session_counter;

//   $insert_courseinfo = $DB->insert_record('courseinfo', $course_info, false);
    echo 'Saved';
    // TODO: Then, redirect
    // redirect($general_url);

} else if ($data = $assessment_form->get_data()) {
    // TODO: Save the submission data, use a function/class from different file
    echo "Assessment";
    print_object($data);

    // TODO: Then, redirect
    // redirect($assessment_url);

} else if ($data = $session_form->get_data()) {
    session_form::save_data($data);
    
    redirect($session_url);
}




echo $OUTPUT->header();
?>

<html>
    <div id="metadata" class="yui3-skin-sam">
        <ul>
            <li><a href="#general_tab">General</a></li>
            <li><a href="#assessment_tab">Assessment</a></li>
            <li><a href="#session_tab">Session</a></li>
        </ul>
        <div>
            <div id="general_tab">
                <!-- content TAB ONE -->
                <?php $general_form->display(); ?>
                </div>
            <div id="assessment_tab">
                <!-- content TAB TWO -->
                <?php $assessment_form->display(); ?>
                </div>
            <div id="session_tab">
                <!-- content TAB THREE -->
                <?php $session_form->display(); ?>
            </div>
        </div>
    </div>
</html>

<?php echo $OUTPUT->footer(); ?>

