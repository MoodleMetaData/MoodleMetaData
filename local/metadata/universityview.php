<?php
global $PAGE, $CFG, $DB, $USER;
require_once('../../config.php');
require_once 'lib.php';


// Check that they can access
require_login();

require_once($CFG->dirroot.'/local/metadata/university_policy.php');

$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('ins_pluginname', 'local_metadata'));
$heading = "Univeristy Syllabus Policy";
$PAGE->set_heading($heading);
$PAGE->set_url($CFG->wwwroot.'/local/metadata/universityview.php');

$base_url = new moodle_url('/local/metadata/universityview.php', null, null);

$university_form = new university_form($base_url);

// Hande Button events for policy tab
if ($data = $university_form->get_data()) {
	university_form::save_data($data);
	redirect($base_url);
}

echo $OUTPUT->header();
?>

<html>
    <div id="metadata" class="yui3-skin-sam">
    	<?php $university_form->display(); ?>
    </div>
</html>

<?php echo $OUTPUT->footer(); ?>
