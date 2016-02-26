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

$displaypage = function () {
    // CHECK And PREPARE DATA.
    global $CFG, $OUTPUT, $SESSION, $PAGE, $DB, $COURSE, $USER;

    // Cheking if the user is valid.
    if (!GAAT\functions\isuservalid($USER->id)) {
        redirect(new moodle_url('/local/gas/consent.php'));
    }

    $id = optional_param('id', 0, PARAM_INT); // List id.

    require_login(1, false); // Use course 1 because this has nothing to do with an actual course, just like course 1.

    $context = context_system::instance();

    $pagetitle = get_string('pluginname', 'local_gas');
    $pageheading = get_string('pluginname', 'local_gas');

    echo local_gas_page('/index.php?id=' . $id, $pagetitle, $pageheading, $context);

    echo $OUTPUT->header();

    $initjs = "$(document).ready(function() {
               });";
    echo html_writer::script($initjs);

    $act = optional_param("action", null, PARAM_TEXT);
    $survey = optional_param("survey", null, PARAM_TEXT);
    $datasaved = false;

    if ($act == "submited") {
        if ($survey == "student") {
            $row['id'] = optional_param("surveyID", null, PARAM_TEXT);
            $row['activity1'] = optional_param("activity1", null, PARAM_TEXT) == "1" ? 1 : 0;
            $row['activity2'] = optional_param("activity2", null, PARAM_TEXT) == "1" ? 1 : 0;
            $row['activity3'] = optional_param("activity3", null, PARAM_TEXT) == "1" ? 1 : 0;
            $row['activity4'] = optional_param("activity4", null, PARAM_TEXT) == "1" ? 1 : 0;
            $row['activity5'] = optional_param("activity5", null, PARAM_TEXT) == "1" ? 1 : 0;
            $row['activity6'] = optional_param("activity6", null, PARAM_TEXT) == "1" ? 1 : 0;
            $row['activity7'] = optional_param("activity7", null, PARAM_TEXT) == "1" ? 1 : 0;
            $row['no_activity'] = optional_param("noActivity", null, PARAM_TEXT) == "1" ? 1 : 0;
            $row['why_no_activity'] = optional_param("whyNone", null, PARAM_TEXT);
            $row['has_other_activity'] = optional_param("hasOtherActivity", null, PARAM_TEXT) == "1" ? 1 : 0;
            $row['other_activity'] = optional_param("otherActivity", null, PARAM_TEXT);
            $row['hours_of_activity'] = optional_param("hoursOfActivity", null, PARAM_TEXT);
            $row['hours_of_activity_text'] = optional_param("hoursOfActivityText", null, PARAM_TEXT);
            $row['hours_of_study'] = optional_param("hoursOfStudy", null, PARAM_TEXT);
            $row['hours_of_study_text'] = optional_param("hoursOfStudyText", null, PARAM_TEXT);

            $DB->update_record("local_gas_student_survey", $row);
            $datasaved = true;
        }
        if ($survey == "instructor") {
            $row["gender"] = optional_param("gender", null, PARAM_TEXT);
            $row["position"] = optional_param("position", null, PARAM_TEXT);
            $row["otherporition"] = optional_param("otherPorition", null, PARAM_TEXT);
            $row["faculty"] = optional_param("faculty", null, PARAM_TEXT);
            $row["subject"] = optional_param("subject", null, PARAM_TEXT);
            $row["years_of_teaching"] = optional_param("yearsOfTeaching", null, PARAM_TEXT);
            $row["discipline"] = optional_param("discipline", null, PARAM_TEXT);
            $row["expand_on_answers"] = optional_param("expandOnAnswers", null, PARAM_TEXT);
            $row["user_id"] = $USER->id;
            $row["timestamp"] = time();

            $id = $DB->insert_record("local_gas_instructor_survey", $row);
            $datasaved = true;
        }
    }
    ?>
    <div style="margin-bottom: 0px;" class='block_course_overview  block'>
        <div class="header">
            <div class="title">
                <h2><i class='fa fa-book'></i><?php echo(" " . get_string('generalInfo', 'local_gas')) ?> </h2>
            </div>
        </div>
        <div class='content'>
            <?php
    if ($datasaved == true) {
                echo("<div class='alert alert-success'><b>".get_string('thanksmes', 'local_gas')."</b></div>");
    }
            ?>
            <p>
                <?php echo(get_string('generalDescription', 'local_gas')) ?>
            </p>
            <br/>
            <?php
    if (GAAT\functions\isstudent($USER->id)) {
                ?>
                <p>
                    <b><?php echo(get_string('surveyguide', 'local_gas')); ?>
                        <a href="survey.php"><?php echo(get_string('clickhere', 'local_gas')); ?></a></b>
                    <?php echo(get_string('studentssurvey', 'local_gas')); ?>
                </p>
                <?php
    }
    if (\GAAT\functions\isteacher($USER->id)) {
                ?>
                <p>
                    <b><?php echo(get_string('surveyguide', 'local_gas')); ?>
                        <a href="instructorsurvey.php"><?php echo(get_string('clickhere', 'local_gas')); ?></a> </b>
                    <?php echo(get_string('teacherssurvey', 'local_gas')); ?>
                </p>
                <?php
    }
            ?>
        </div>
    </div>
    <?php
    echo $OUTPUT->footer();
};

$displaypage();
