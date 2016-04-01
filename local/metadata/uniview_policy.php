<?php
/**
 * Renders the page for the creation of Univeristy specific policy
 */
global $PAGE, $CFG, $DB, $USER;
require_once('../../config.php');
require_once 'lib.php';


// Check that they can access
require_login();
if(!is_siteadmin()) {
	print_error('nopermissions', 'error', '', '');
}

require_once($CFG->dirroot.'/local/metadata/university_policy.php');

$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('ins_pluginname', 'local_metadata'));
$heading = "University Syllabus Manager";
$PAGE->set_heading($heading);
$PAGE->set_url($CFG->wwwroot.'/local/metadata/uniview_policy.php');
$PAGE->requires->css('/local/metadata/insview_style.css');

$base_url = new moodle_url('/local/metadata/uniview_policy.php', null, null);
$gradatt_url = new moodle_url('/local/metadata/uniview_gradatt.php', null, null);

$university_form = new university_form($base_url);

// Hande Button events for policy tab
if ($data = $university_form->get_data()) {
	university_form::save_data($data);
	redirect($base_url);
}

echo $OUTPUT->header();
?>

<html>
	<div class="nav_header">
		<ul>
		<li class="onclick_nav"><a href=" <?php echo $base_url; ?> ">University Policy</a></li>
		<li><a href=" <?php echo $gradatt_url; ?> ">Graduate Attributes</a></li>
		</ul>
	</div>
	
	<div class="form_container">
		<?php $university_form->display(); ?>
	</div>
</html>
<html>

<?php echo $OUTPUT->footer(); ?>
