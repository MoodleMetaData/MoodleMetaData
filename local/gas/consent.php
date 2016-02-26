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
//
// Author: Behdad Bakhshinategh!
// Description: This page is for the general information and description users may need.

require_once(dirname(__FILE__) . '/lib/functions.php');
require_once(dirname(__FILE__) . '/config.php');
require_once(dirname(__FILE__) . '/locallib.php');

$displaypage = function() {
    // CHECK And PREPARE DATA.
    global $OUTPUT, $USER;

    $id = optional_param('id', 0, PARAM_INT); // List id.

    require_login(1, false); // Use course 1 because this has nothing to do with an actual course, just like course 1.

    $context = context_system::instance();

    $pagetitle = get_string('pluginname', 'local_gas');
    $pageheading = get_string('pluginname', 'local_gas');

    echo local_gas_page('/consent.php?id=' . $id, $pagetitle, $pageheading, $context);

    echo $OUTPUT->header();

    $initjs = "";
    echo html_writer::script($initjs);

    $check = optional_param("agreed", null, PARAM_TEXT);
    $datasaved = false;

    if ($check == "yes") {
        $row['user_id'] = $USER->id;
        $row['email'] = null;
        if (optional_param("check", null, PARAM_TEXT) != null) {
            $row['email'] = optional_param("email", null, PARAM_EMAIL);
            if ($row['email'] == null || trim($row['email']) == '') {
                $row['email'] = $USER->email;
            }
        }
        $row['timestamp'] = time();
        GAAT\functions\addvaliduser($row);
        $datasaved = true;
    }
    ?>
    <div style="margin-bottom: 0px;" class='block_course_overview  block'>
        <div class="header">
            <div class="title">
                <h2><i class='fa fa-book'></i><?php echo(" " . get_string('consentForm', 'local_gas')) ?> </h2>
            </div>
        </div>
        <div class='content'>
            <?php
    if ($datasaved == true) {
                echo("<div class='alert alert-success'><b>".get_string('thanksmes', 'local_gas')."</b></div>");
    }
            echo(get_string('consentDescription', 'local_gas'));
            ?>
            <br/>
            <h3>
                <?php echo(get_string('consent', 'local_gas')); ?>
            </h3>
            <form action="consent.php" method="post">
                <p>
                    <label><input type="checkbox" name="agreed" value="yes" required="true"/>
                        <?php echo(get_string('consentContent1', 'local_gas')); ?></label>
                </p>
                <p>
                    <label><input type="checkbox" id="check" name="check" value="yes" onchange="if ($('#check').is(':checked')) {
                                    $('#address').prop('disabled', false);
                                } else {
                                    $('#address').prop('disabled', true);
                                }"/><?php echo(get_string('consentaddress', 'local_gas')); ?></label>
                    <label>Email: <input id="address" type="email" name="email" disabled="true"/></label>
                    <br/><br/>
                    <input type="submit" style="width: 180px; height:30px; font-size: larger;" value="submit" />
                </p>
            </form>
        </div>
    </div>

    <?php
    echo $OUTPUT->footer();
};

$displaypage();
