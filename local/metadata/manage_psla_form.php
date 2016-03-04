<?php
global $PAGE, $CFG, $DB, $USER;
require_once('../../config.php');
require_once 'lib.php';


// Check that they can access
require_login();

// TODO: Get permissions working
//$courseId = '1';
//$context = context_course::instance($courseId);
//require_capability('local/metadata:ins_view', $context);


require_once($CFG->dirroot.'/local/metadata/knowledge_form.php');
require_once($CFG->dirroot.'/local/metadata/skills_form.php');
require_once($CFG->dirroot.'/local/metadata/attitudes_form.php');
require_once($CFG->dirroot.'/local/metadata/policy_form.php');

// Define global variable for DB result
//$course = $DB->get_record('course', array('id'=>$courseId), '*', MUST_EXIST);
    
// Set up page information
//$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('ins_pluginname', 'local_metadata'));
$heading = "Learning Objectives Management";
$PAGE->set_heading($heading);
$PAGE->set_url($CFG->wwwroot.'/local/metadata/manage_psla_form.php');
$PAGE->requires->js('/local/metadata/tabview.js');
$PAGE->requires->js('/local/metadata/util.php');


// Create forms
$base_url = new moodle_url('/local/metadata/manage_psla_form.php', null, null);
$knowledge_form = new knowledge_form($base_url); // #
$skills_form = new skills_form($base_url.'#tab=1'); // #tab=1
$attitudes_form = new attitudes_form($base_url.'#tab=2'); // #tab=2
$policy_form = new policy_form($base_url.'#tab=3'); // #tab=3

$knowledge_url = new moodle_url('/local/metadata/manage_psla_form.php', null, null);
$skills_url = new moodle_url('/local/metadata/manage_psla_form.php', null, 'tab=1');
$attitudes_url = new moodle_url('/local/metadata/manage_psla_form.php', null, 'tab=2');
$policy_url = new moodle_url('/local/metadata/manage_psla_form.php', null, 'tab=3');

// Handle Button events for knowledge tab
if ($data = $knowledge_form->get_data()) {
	if (!empty($data->delete_knowledge)) {
		knowledge_form::delete_data($data);
		redirect($knowledge_url);
	} else {
    	knowledge_form::save_data($data);
   		redirect($knowledge_url);
	}
	
} 

// Handle Button events for skills tab
if ($data = $skills_form->get_data()) {
	if (!empty($data->delete_skills)) {
		skills_form::delete_data($data);
		redirect($skills_url);
	} else {
    	skills_form::save_data($data);
   		redirect($skills_url);
	}
	
}

// Handle Button events for attitudes tab
if ($data = $attitudes_form->get_data()) {
	if (!empty($data->delete_attitudes)) {
		attitudes_form::delete_data($data);
		redirect($attitudes_url);
	} else {
    	attitudes_form::save_data($data);
   		redirect($attitudes_url);
	}
	
}

// Hande Button events for policy tab
if ($data = $policy_form->get_data()) {
	//policy_form::save_data($data);
	redirect($policy_url);
}

echo $OUTPUT->header();
?>

<html>
    <div id="metadata" class="yui3-skin-sam">
        <ul>
            <li><a href="#knowledge_tab">Edit Knowledge</a></li>
            <li><a href="#skills_tab">Edit Skills</a></li>
            <li><a href="#attitudes_tab">Edit Attitudes</a></li>
            <li><a href="#policy_tab">Edit Policies</a>
        </ul>
        <div>
            <div id="knowledge_tab">
                <!-- content TAB ONE -->
                <?php $knowledge_form->display(); ?>
                </div>
            <div id="skills_tab">
                <!-- content TAB TWO -->
				<?php $skills_form->display(); ?>
                </div>
            <div id="attitutdes_tab">
                <!-- content TAB THREE -->
               <?php $attitudes_form->display(); ?>
            </div>
            <div id="policy_tab">
            	<!-- content TAB FOUR -->
            	<?php $policy_form->display(); ?>
            </div>
        </div>
    </div>
</html>

<?php echo $OUTPUT->footer(); ?>

