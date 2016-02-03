<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Initial page for the plug-in
 *
 * @package     local
 * @subpackage  demo_plug-in
 * @copyright   Eric Cheng ec10@ualberta.ca
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

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
$mform = new sample_form();

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();

?>

<html>
    <div id="demo" class="yui3-skin-sam">
  <ul>
    <li><a href="#asparagus">Asparagus</a></li>
    <li><a href="#bird">Bird</a></li>
    <li><a href="#coffee">Coffee</a></li>
  </ul>
  <div>
    <div id="asparagus">
      <a href="http://www.flickr.com/photos/allenr/4686935131/">
        <img src="http://farm5.static.flickr.com/4005/4686935131_253e921bf7_m.jpg" alt="Asparagus">
      </a>
    </div>
    <div id="bird">
      <a href="http://www.flickr.com/photos/allenr/66307916/">
        <img src="http://farm1.static.flickr.com/26/66307916_811efccdfc_m.jpg" alt="Bird">
      </a>
    </div>
    <div id="coffee">
      <a href="http://www.flickr.com/photos/allenr/4638474362/">
        <img src="http://farm4.static.flickr.com/3336/4638474362_093edb7565_m.jpg" alt="Coffee">
      </a>
    </div>
  </div>
</div>
</html>