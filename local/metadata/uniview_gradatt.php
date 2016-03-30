<?php
global $PAGE, $CFG, $DB, $USER;
require_once('../../config.php');
require_once 'lib.php';


// Check that they can access
require_login();

require_once($CFG->dirroot.'/local/metadata/university_gradatt.php');

$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('ins_pluginname', 'local_metadata'));
$heading = "University Syllabus Manager";
$PAGE->set_heading($heading);
$PAGE->set_url($CFG->wwwroot.'/local/metadata/uniview_gradatt.php');
$PAGE->requires->css('/local/metadata/insview_style.css');

$policy_url = new moodle_url('/local/metadata/uniview_policy.php', null, null);
$gradatt_url = new moodle_url('/local/metadata/uniview_gradatt.php', null, null);

$gradatt_form = new gradatt_form($gradatt_url);

// Submitted the data
if ($data = $gradatt_form->get_data()) {
	if (!empty($data->delete_gradatt)) {
		$gradatt_form->delete_data($data);
	}
	redirect($gradatt_url);
} 

echo $OUTPUT->header();
?>

<html>
	<div class="nav_header">
		<ul>
		<li><a href=" <?php echo $policy_url; ?> ">University Policy</a></li>
		<li class="onclick_nav"><a href=" <?php echo $gradatt_url; ?> ">Graduate Attributes</a></li>
		</ul>
	</div>
	
	<div class="form_container">
		<?php $gradatt_form->display(); ?>
	</div>
</html>
<html>

<?php echo $OUTPUT->footer(); ?>
