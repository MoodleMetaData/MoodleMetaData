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

require_once($CFG->dirroot.'/local/metadata/session_form.php');

// Define global variable for DB result
$course = $DB->get_record('course', array('id'=>$courseId), '*', MUST_EXIST);
    
// Set up page information
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('ins_pluginname', 'local_metadata'));
$heading = sprintf(get_string('instructor_heading', 'local_metadata'), $course->shortname, $course->fullname);
$PAGE->set_heading($heading);
$PAGE->set_url($CFG->wwwroot.'/local/metadata/insview_session.php');
$PAGE->requires->css('/local/metadata/insview_style.css');

// Create url
$base_url = create_insview_url('session', $courseId);
$general_url = create_insview_url('general',$courseId);
$assessment_url = create_insview_url('assessment', $courseId);
$session_url = create_insview_url('session', $courseId);

// Create forms
$sessions = get_table_data_for_course('coursesession');
$session_form = new session_form($base_url, array('sessions' => $sessions));

// Case where they cancelled the form. Just redirect to it, to reset values
if ($session_form->is_cancelled()) {
    redirect($session_url);
}

// Submitted the data
if ($data = $session_form->get_data()) {
    session_form::save_data($data);
    redirect($session_url);
}

echo $OUTPUT->header();
?>

<link rel="stylesheet" type="text/css" href="session_element_style.css">
<html>
	<div class="nav_header">
		<ul>
		<li><a href=" <?php echo $general_url; ?> ">General</a></li>
		<li><a href=" <?php echo $assessment_url; ?> ">Assessment</a></li>
		<li class="onclick_nav"><a href=" <?php echo $session_url; ?> ">Session</a></li>
		</ul>
	</div>
	
	<div class="form_container">
		<?php $session_form->display(); ?>
	</div>
</html>

<?php echo $OUTPUT->footer(); ?>
