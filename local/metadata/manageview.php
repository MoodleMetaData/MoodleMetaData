<?php
global $PAGE, $CFG, $DB;
require_once('../../config.php');

require_login();
require_capability('local/metadata:admin_view', context_system::instance());
require_once($CFG->dirroot.'/local/metadata/manage_form.php');

$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('manage_pluginname', 'local_metadata'));
$PAGE->set_heading(get_string('manage_pluginname', 'local_metadata'));
$PAGE->set_url($CFG->wwwroot.'/local/metadata/manageview.php');
$manage_form = new manage_form();

echo $OUTPUT->header();
$manage_form->display();
echo $OUTPUT->footer();

 ?>
