<?php
global $PAGE, $CFG, $DB;
require_once('../../config.php');

require_login();
require_capability('local/demo:add', context_system::instance());
require_once($CFG->dirroot.'/local/demo/sample_form.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('pluginname', 'local_demo'));
$PAGE->set_heading(get_string('pluginname', 'local_demo'));
$PAGE->set_url($CFG->wwwroot.'/local/demo/view.php');
$PAGE->requires->js('/local/demo/tabview.js');
$form_one = new sample_form();
$form_two = new second_form();

echo $OUTPUT->header();
?>
<html>
    <div id="demo" class="yui3-skin-sam">
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
    </div>
  </div>
</div>
</html>

<?php
echo $OUTPUT->footer();
?>

