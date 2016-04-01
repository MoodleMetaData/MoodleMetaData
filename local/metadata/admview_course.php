<?php
global $PAGE, $CFG, $DB, $USER;
require_once('../../config.php');
require_once 'lib.php';


// Check that they can access
require_login();

// TODO: Get permissions working


//require_capability('local/metadata:ins_view', $context);

require_once($CFG->dirroot.'/local/metadata/course_select_form.php');
    
// Set up page information
$categoryId = get_category_id();

$PAGE->set_context(context_coursecat::instance($categoryId));
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('admview_pluginname', 'local_metadata'));
$heading = "Program Learning Assessment";
$PAGE->set_heading($heading);

// Create url
$knowledge_url = create_manage_url('knowledge', $categoryId);
$policy_url = create_manage_url('policy', $categoryId);
$course_url = create_manage_url('course', $categoryId);
$exclude_url = create_manage_url('exclude', $categoryId);
$reporting_url = create_manage_url('reporting', $categoryId);
$categories_url = create_manage_url('categories', $categoryId);

$PAGE->set_url($course_url);
$PAGE->requires->css('/local/metadata/insview_style.css');

// Create forms
$course_form = new course_select_form($course_url);

// Submit the data
if ($data = $course_form->get_data()) {
	$courseid = $course_form->get_course_id($data);
	$tag_url = new moodle_url('/local/metadata/admview_tag.php', array('categoryid' => $categoryId,'id' => $courseid));
	redirect($tag_url);
} 

echo $OUTPUT->header();
?>

<html>
	<div class="nav_header">
		<ul>
		<li><a href=" <?php echo $knowledge_url; ?> ">Program Objectives</a></li>
		<li><a href=" <?php echo $categories_url; ?> ">Categories</a></li>
		<li><a href=" <?php echo $policy_url; ?> ">Policy</a></li>
		<li class="onclick_nav"><a href=" <?php echo $course_url; ?> ">Tags</a></li>
		<li><a href=" <?php echo $exclude_url; ?> ">Syllabus Configuration</a></li>
		<li><a href=" <?php echo $reporting_url; ?> ">Reporting</a></li>
		</ul>
	</div>
	
	<div class="form_container">
		<?php $course_form->display(); ?>
	</div>
</html>

<?php echo $OUTPUT->footer(); ?>

