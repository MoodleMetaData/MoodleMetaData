<?php
global $PAGE, $CFG, $DB, $USER;
require_once('../../config.php');
require_once 'lib.php';


// Check that they can access
require_login();

// TODO: Get permissions working


//require_capability('local/metadata:ins_view', $context);

require_once($CFG->dirroot.'/local/metadata/policy_form.php');
    
// Set up page information
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('ins_pluginname', 'local_metadata'));
$heading = "Learning Objectives Management";
$PAGE->set_heading($heading);

// TODO: Improve how this is done
$PAGE->set_url($CFG->wwwroot.'/local/metadata/admview_policy.php');
$PAGE->requires->css('/local/metadata/insview_style.css');

// Create url
$base_url = create_manage_url('policy');
$knowledge_url = create_manage_url('knowledge');
$skills_url = create_manage_url('skills');
$attitudes_url = create_manage_url('attitudes');
$policy_url = create_manage_url('policy');

// Create forms
$policy_form = new policy_form($base_url);


// Submitted the data
if ($data = $policy_form->get_data()) {
	policy_form::save_data($data);
	redirect($policy_url);
} 

echo $OUTPUT->header();
?>

<html>
	<div class="nav_header">
		<ul>
		<li><a href=" <?php echo $knowledge_url; ?> ">Knowledge</a></li>
		<li><a href=" <?php echo $skills_url; ?> ">Skills</a></li>
		<li><a href=" <?php echo $attitudes_url; ?> ">Attitudes</a></li>
		<li class="onclick_nav"><a href=" <?php echo $policy_url; ?> ">Policy</a></li>
		</ul>
	</div>
	
	<div class="form_container">
		<?php $policy_form->display(); ?>
	</div>
</html>

<?php echo $OUTPUT->footer(); ?>

