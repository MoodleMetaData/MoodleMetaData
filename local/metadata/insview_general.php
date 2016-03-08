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

// Define global variable for DB result
$course = $DB->get_record('course', array('id'=>$courseId), '*', MUST_EXIST);
    
// Set up page information
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('ins_pluginname', 'local_metadata'));
$heading = sprintf(get_string('instructor_heading', 'local_metadata'), $course->shortname, $course->fullname);
$PAGE->set_heading($heading);
$PAGE->set_url($CFG->wwwroot.'/local/metadata/insview_general.php');
$PAGE->requires->css('/local/metadata/insview_style.css');

// Create url
$base_url = create_insview_url('general', $courseId);
$general_url = create_insview_url('general',$courseId);
$assessment_url = create_insview_url('assessment', $courseId);
$session_url = create_insview_url('session', $courseId);

// Create forms
$general_form = new general_form($base_url);



// Case where they cancelled the form. Just redirect to it, to reset values
if ($general_form->is_cancelled()) {
    redirect($general_url);
}

// Submitted the data
if ($data = $general_form->get_data()) {
    general_form::save_data($data);
    //print_object($data);
    redirect($general_url);
}

echo $OUTPUT->header();
?>

<html>
	<div class="nav_header">
		<ul>
		<li class="onclick_nav"><a href=" <?php echo $general_url; ?> ">General</a></li>
		<li><a href=" <?php echo $assessment_url; ?> ">Assessment</a></li>
		<li><a href=" <?php echo $session_url; ?> ">Session</a></li>
		</ul>
	</div>
	
	<div class="form_container">
		<?php $general_form->display(); ?>
	</div>
</html>

<?php echo $OUTPUT->footer(); ?>

