<?php
    global $PAGE, $CFG, $DB;
    require_once('../../config.php');
    require_login();
    require_capability('local/yunita:add', context_system::instance());
    $PAGE->set_context(context_system::instance());
    $PAGE->set_pagelayout('standard');
    $PAGE->set_title(get_string('pluginname', 'local_yunita'));
    $PAGE->set_heading(get_string('pluginname', 'local_yunita'));
    $PAGE->set_url($CFG->wwwroot.'/local/yunita/sample.php');
    $new_form = new create_form_instance();
    
    echo $OUTPUT->header();
    $new_form->display();
    echo $OUTPUT->footer();
?>