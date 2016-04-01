<?php
/**
 * This page allows for the upload and maitnence of sets of program objectives.
 */
global $PAGE, $CFG, $DB, $USER;
require_once('../../config.php');
require_once 'lib.php';


// Check that they can access
require_login();

// TODO: Get permissions working


//require_capability('local/metadata:ins_view', $context);

require_once($CFG->dirroot.'/local/metadata/knowledge_form.php');
    
// Set up page information
$categoryId = get_category_id();
$PAGE->set_category_by_id($categoryId);

$PAGE->set_context(context_coursecat::instance($categoryId));
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('admview_pluginname', 'local_metadata'));
$heading = "Learning Objectives Management";
$PAGE->set_heading($heading);

// Create url
$knowledge_url = create_manage_url('knowledge', $categoryId);
$policy_url = create_manage_url('policy', $categoryId);
$course_url = create_manage_url('course', $categoryId);
$exclude_url = create_manage_url('exclude', $categoryId);
$reporting_url = create_manage_url('reporting', $categoryId);
$categories_url = create_manage_url('categories', $categoryId);

$PAGE->set_url($knowledge_url);
$PAGE->requires->css('/local/metadata/insview_style.css');

// Create forms
$knowledge_form = new knowledge_form($knowledge_url);

// Submitted the data
if ($data = $knowledge_form->get_data()) {
	if (!empty($data->delete_groups)) {
		knowledge_form::delete_groups($data);
	} 
	redirect($knowledge_url);
} 

echo $OUTPUT->header();
?>

<html>
	<div class="nav_header">
		<ul>
		<li class="onclick_nav"><a href=" <?php echo $knowledge_url; ?> ">Program Objectives</a></li>
		<li><a href=" <?php echo $categories_url; ?> ">Categories</a></li>
		<li><a href=" <?php echo $policy_url; ?> ">Policy</a></li>
		<li><a href=" <?php echo $course_url; ?> ">Tags</a></li>
		<li><a href=" <?php echo $exclude_url; ?> ">Syllabus Configuration</a></li>
		<li><a href=" <?php echo $reporting_url; ?> ">Reporting</a></li>
		</ul>
	</div>
	
	<div class="form_container">
		<?php $knowledge_form->display(); ?>
	</div>
</html>

<?php echo $OUTPUT->footer(); ?>

