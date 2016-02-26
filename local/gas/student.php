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

use GAAT\functions as G;

require_once(dirname(__FILE__) . '/lib/functions.php');
require_once(dirname(__FILE__) . '/config.php');
require_once(dirname(__FILE__) . '/locallib.php');

$displaypage = function () {
    // CHECK And PREPARE DATA.
    global $CFG, $OUTPUT, $SESSION, $PAGE, $DB, $COURSE, $USER;

    // Check if the user accessing this page is student.
    if (!G\isstudent($USER->id)) {
        redirect(new moodle_url('/local/gas/index.php'));
    }

    $PAGE->set_pagelayout('report');

    $id = optional_param('id', 0, PARAM_INT); // List id.

    require_login(1, false); // Use course 1 because this has nothing to do with an actual course, just like course 1.

    $context = context_system::instance();

    $pagetitle = get_string('pluginname', 'local_gas');
    $pageheading = get_string('pluginname', 'local_gas');

    echo local_gas_page('/index.php?id=' . $id, $pagetitle, $pageheading, $context);

    // This part added as the highlighting of the navigators was not working.
    G\makenavigatorlinkactive($PAGE, "assessment");

    echo $OUTPUT->header();

    // NextCourse: function to change the course in "Contributed Courses" Coloumn
    // HighlightDescription: highliting the value description based on the range input.
    $initjs = " var formSubmitting = false;
                var changesHappened = false;
                var setFormSubmitting = function () {
                    formSubmitting = true;
                };
                var setChanges = function () {
                    changesHappened = true;
                };
                window.onload = function () {
                    window.addEventListener('beforeunload', function (e) {
                        if (formSubmitting || !changesHappened) {
                            return undefined;
                        }
                        var confirmationMessage = 'It looks like you have been editing something. '
                                + 'If you leave before saving, your changes will be lost.';

                        (e || window.event).returnValue = confirmationMessage; //Gecko + IE
                        return confirmationMessage; //Gecko + Webkit, Safari, Chrome etc.
                    });
                };
                function ShowDes(val1, val2, num) {
                    for (var i = 0; i < num; i++) {
                        document.getElementById('attDes' + val1 + i).style.display = 'none';
                    }
                    document.getElementById('attDes' + val1 + val2).style.display = 'table';
                }
                function highLightDescription(id, val) {

                    newId = id + val;
                    $('[class^=' + id + ']').removeClass('info');
                    $('.' + newId).addClass('info');
                }
                $(document).ready(function () {

                    $('#myTabs a').click(function (e) {
                        e.preventDefault()
                        $(this).tab('show')
                      });

                    $('.rangeInput').keyup(function( event ) {
                        id = this.id.substring(10);
                        val = $(this).val();
                        if(val > 0 && val < 6)
                            highLightDescription('descriptionRow'+id+val);
                    });

                    $('.rangeInput').mouseup(function( event ) {
                        id = this.id.substring(10);
                        val = $(this).val();
                        if(val > 0 && val < 6)
                            highLightDescription('descriptionRow'+id , val);
                    });
                });

                function nextCourse(id, counter, max){
                    if(counter == 1){
                        var option1={direction: 'right'};
                        var option2={direction: 'left'};
                    }
                    if(counter == -1){
                        var option1={direction: 'left'};
                        var option2={direction: 'right'};
                    }
                    currentID = parseInt($('.'+id+':visible').attr('id'));
                    nextID = (parseInt(currentID) + counter + max)%max;
                    $('#'+id+currentID).toggle('slide', option1, 300);
                    setTimeout(function(){
                        $('#'+id+nextID).toggle('slide', option2, 300);
                    }, 300);
                }";

    echo html_writer::script($initjs);

    $act = optional_param("action", null, PARAM_TEXT);
    $currentsem = G\semofdate(date("d"), date("m"));
    $datasaved = false;
    // If the form submited to save an assessment.
    if ($act == "assessment") {

        $studentid = $USER->id;
        // Variable storing the amount of time user spent on assessment.
        $timetaken = time() - optional_param("AssStartTime", null, PARAM_INT);

        // Creating a row in assessment table.
        $assid = G\newassessment($studentid, time(), $timetaken, $currentsem, "Student");

        $rangeinput = optional_param_array("rangeInput", null, PARAM_NUMBER);
        $subattids = optional_param_array("subattID", null, PARAM_INT);

        foreach ($subattids as $subattid) {

            // Creating a row for each of the sub-attributes of assessment.
            $subattassessmentid = G\newsubattassessment($assid, $subattid, current($rangeinput), "Student");
            next($rangeinput);

            $courseid = optional_param_array("CourseIDFor$subattid", null, PARAM_INT);
            if (count($courseid) > 0) {
                foreach ($courseid as $cids) {

                    $ccradio = optional_param("CourseRadio$subattid$cids", null, PARAM_INT);
                    $ccvalue = 0;
                    if ($ccradio != null) {
                        $ccvalue = $ccradio;
                    }

                    // Creating a row in ContributedCourses table for each of courses having impact on improvement of sub-attribute.
                    G\newcontributedcourse($subattassessmentid, $cids, $ccvalue);
                }
            }
        }
        $datasaved = true;
    }

    // Getting the courses user enroled in in current year and semester.
    $courses = G\coursesas($USER->id, "student");
    ?>
    <div style="margin-bottom: 0px; width:1100px;" class='block_course_overview  block'>
        <div class="header">
            <div class="title">
                <h2><i class='fa fa-question-circle'></i> Assessment</h2>
            </div>
        </div>
        <div class='content'>
            <?php
    if ($datasaved) {
            echo("<div class='alert alert-success'><b>" . get_string('savedmes', 'local_gas') . "</b></div>");
    }
            ?>
            <form action='student.php' method='post' onsubmit="setFormSubmitting()">
                <input type='hidden' value='assessment' name='action'>
                <input type='hidden' value='<?php echo(time()); ?>' name='AssStartTime'>
                <div class='panel-body'>
                    <?php
                    // Creating tabs for attributes.
                    ?>
                    <ul class="nav nav-tabs" id="myTabs">
                        <?php
                        $labels = G\getattributes(current_language(), time());
    for ($i = 1; $i <= count($labels); $i++) {
                $label = $labels[$i - 1];
                            echo ("<li class='tab  ");
        if ($i == 1) {
                    echo("active");
        }
                            echo(" '><a href='#tabs$i'>$label->name</a></li>");
    }
                        ?>
                    </ul>
                    <div class="tab-content">
                        <?php
    for ($i = 1; $i <= count($labels); $i ++) {
        echo ("<div class='tab-pane ");
        if ($i == 1) {
            echo("active");
        }
                            echo("' id='tabs$i'>");

                            // Each sliderSet is the assessment of one attribute.
                            G\sliderset($USER->id, $labels[$i - 1]->attribute_id, $courses);

                            echo ("</div>");
    }
                        ?>
                    </div>
                </div>
                <input type="submit" value="Save Changes">
            </form>
        </div>
    </div>

    <?php
    echo $OUTPUT->footer();
};

$displaypage();
