<?php
global $PAGE, $CFG, $DB;
require_once('../../config.php');

require_login();
require_capability('local/metadata:view', context_system::instance());
require_once($CFG->dirroot.'/local/metadata/sample_form.php');
require_once($CFG->dirroot.'/local/metadata/second_form.php');

$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('pluginname', 'local_metadata'));
$PAGE->set_heading(get_string('pluginname', 'local_metadata'));
$PAGE->set_url($CFG->wwwroot.'/local/metadata/view.php');
$PAGE->requires->js('/local/metadata/tabview.js');

$form_one = new sample_form();
$form_two = new second_form();

echo $OUTPUT->header();
?>

<html>
    <div id="metadata" class="yui3-skin-sam">
  <ul>
    <li><a href="#tab_one">ONE</a></li>
    <li><a href="#tab_two">TWO</a></li>
    <li><a href="#tab_three">THREE</a></li>
  </ul>
  <div>
    <div id="tab_one">
      <!-- content TAB ONE -->
      <?php $form_one->display(); ?>
    </div>
    <div id="tab_two">
      <?php $form_two->display(); ?>
    </div>
    <div id="tab_three">
      <!-- content TAB THREE -->
      <b>This is the third tabview.</b>
    </div>
  </div>
</div>
</html>

<?php echo $OUTPUT->footer(); ?>

