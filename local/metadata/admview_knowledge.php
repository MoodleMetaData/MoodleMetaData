<?php
global $PAGE, $CFG, $DB, $USER;
require_once('../../config.php');
require_once 'lib.php';


// Check that they can access
require_login();

// TODO: Get permissions working


//require_capability('local/metadata:ins_view', $context);

require_once($CFG->dirroot.'/local/metadata/knowledge_form.php');
    
// Set up page information
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('ins_pluginname', 'local_metadata'));
$heading = "Learning Objectives Management";
$PAGE->set_heading($heading);

// TODO: Improve how this is done
$PAGE->set_url($CFG->wwwroot.'/local/metadata/admview_knowledge.php');
$PAGE->requires->css('/local/metadata/insview_style.css');

// Create url
$base_url = create_manage_url('knowledge');
$knowledge_url = create_manage_url('knowledge');
$policy_url = create_manage_url('policy');
$course_url = create_manage_url('course');

// Create forms
$knowledge_form = new knowledge_form($base_url);

// Submitted the data
if ($data = $knowledge_form->get_data()) {
	if (!empty($data->delete_knowledge)) {
		knowledge_form::delete_knowledge($data);
	} elseif (!empty($data->create_knowledge)) {
    	knowledge_form::save_knowledge($data);
	} elseif (!empty($data->create_skills)) {
		knowledge_form::save_skills($data);
	} elseif (!empty($data->delete_skills)) {
		knowledge_form::delete_skills($data);
	} elseif (!empty($data->create_attitudes)) {
		knowledge_form::save_attitudes($data);
	} elseif (!empty($data->delete_attitudes)) {
		knowledge_form::delete_attitudes($data);
	}
	redirect($knowledge_url);
} 

echo $OUTPUT->header();
?>

<html>
	<div class="nav_header">
		<ul>
		<li class="onclick_nav"><a href=" <?php echo $knowledge_url; ?> ">Program Objectives</a></li>
		<li><a href=" <?php echo $policy_url; ?> ">Policy</a></li>
		<li><a href=" <?php echo $course_url; ?> ">Tags</a></li>
		</ul>
	</div>
	
	<div class="form_container">
		<?php $knowledge_form->display(); ?>
	</div>
</html>

<?php echo $OUTPUT->footer(); ?>

