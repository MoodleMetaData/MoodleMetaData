<?php
    global $CFG, $PAGE, $DB; 
    require_once('../../config.php');
    require_login(); //You should always want a user to be logged in
    require_capability('local/myplugin:add', context_system::instance());
    require_once($CFG->dirroot.'/local/myplugin/example_form.php');
    $PAGE->set_context(context_system::instance());
    $PAGE->set_pagelayout('standard');
    $PAGE->set_title(get_string('pluginname', 'local_myplugin'));
    $PAGE->set_heading(get_string('pluginname', 'local_myplugin'));
    $PAGE->set_url($CFG->wwwroot.'/local/myplugin/view.php');
    $new_form = new create_form_instance();

    echo $OUTPUT->header();
    $new_form->display();
    echo $OUTPUT->footer();
?>