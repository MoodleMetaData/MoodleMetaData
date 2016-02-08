<?php
global $PAGE, $CFG, $DB, $COURSE, $USER;
require_once('../../config.php');
require_once 'lib.php';


// Check that they can access
require_login();

// TODO: Get permissions working
$csid = get_course_id();
$context = context_course::instance($csid);
//require_capability('local/metadata:ins_view', $context);


require_once($CFG->dirroot.'/local/metadata/general_form.php');
require_once($CFG->dirroot.'/local/metadata/assessment_form.php');
require_once($CFG->dirroot.'/local/metadata/session_form.php');

// TODO: Work around until can get the other stuff working
$course = $DB->get_record('course', array('id'=>$csid));


// Set up page information
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('ins_pluginname', 'local_metadata'));
$heading = sprintf(get_string('instructor_heading', 'local_metadata'), $course->shortname, $course->fullname);
$PAGE->set_heading($heading);
$PAGE->set_url($CFG->wwwroot.'/local/metadata/insview.php');
$PAGE->requires->js('/local/metadata/tabview.js');

// Create forms
$general_form = new general_form();
$assessment_form = new assessment_form();
$session_form = new session_form();


echo $OUTPUT->header();
?>

<html>
    <div id="metadata" class="yui3-skin-sam">
        <ul>
            <li><a href="#general_tab">General</a></li>
            <li><a href="#assessment_tab">Assessment</a></li>
            <li><a href="#session_tab">Session</a></li>
        </ul>
        <div>
            <div id="general_tab">
                <!-- content TAB ONE -->
                <?php $general_form->display(); ?>
                </div>
            <div id="assessment_tab">
                <!-- content TAB TWO -->
                <?php $assessment_form->display(); ?>
                </div>
            <div id="session_tab">
                <!-- content TAB THREE -->
                <?php $session_form->display(); ?>
            </div>
        </div>
    </div>
</html>

<?php echo $OUTPUT->footer(); ?>

