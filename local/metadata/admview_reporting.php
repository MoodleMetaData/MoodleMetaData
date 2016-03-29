<?php
global $PAGE, $CFG, $DB, $USER;
require_once('../../config.php');
require_once 'lib.php';


// Check that they can access
require_login();

// TODO: Get permissions working


//require_capability('local/metadata:ins_view', $context);

require_once($CFG->dirroot.'/local/metadata/reporting_form.php');
    
// Set up page information
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('ins_pluginname', 'local_metadata'));
$heading = "Program learning objectives report";
$PAGE->set_heading($heading);

// TODO: Improve how this is done
$PAGE->set_url($CFG->wwwroot.'/local/metadata/admview_reporting.php');
$PAGE->requires->css('/local/metadata/insview_style.css');

// Create url
$base_url = create_manage_url('reporting');
$knowledge_url = create_manage_url('knowledge');
$policy_url = create_manage_url('policy');
$course_url = create_manage_url('course');
$gradatt_url = create_manage_url('gradatt');
$required_url = create_manage_url('required');
$reporting_url = create_manage_url('reporting');
$categories_url = create_manage_url('categories');

// Create forms
$reporting_form = new reporting_form($base_url);


// Submit the data
if ($data = $reporting_form->get_data()) {
	$courseid = $reporting_form->get_course_id($data);
	$tag_url = new moodle_url('/local/metadata/admview_reporting.php', array('id' => $courseid));
	redirect($tag_url);
} 

echo $OUTPUT->header();
?>

<html>
	<div class="nav_header">
		<ul>
		<li><a href=" <?php echo $knowledge_url; ?> ">Program Objectives</a></li>
		<li><a href=" <?php echo $categories_url; ?> ">Categories</a></li>
		<li><a href=" <?php echo $gradatt_url; ?> ">Graduate Attribute</a></li>
		<li><a href=" <?php echo $policy_url; ?> ">Policy</a></li>
		<li><a href=" <?php echo $course_url; ?> ">Tags</a></li>
		<li><a href=" <?php echo $required_url; ?> ">Required</a></li>
		<li class="onclick_nav"><a href=" <?php echo $reporting_url; ?> ">Reporting</a></li>
		</ul>
	</div>
	
	<div class="form_container">
		<?php $reporting_form->display(); ?>
	</div>
</html>

<?php echo $OUTPUT->footer(); ?>

