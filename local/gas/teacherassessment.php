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

    // Check if the user accessing the page is instructor.
    if (!G\isteacher($USER->id)) {
        redirect(new moodle_url('/local/gas/index.php'));
    }

    $PAGE->set_pagelayout('report');

    $id = optional_param('id', 0, PARAM_INT); // List id.

    require_login(1, false); // Use course 1 because this has nothing to do with an actual course, just like course 1.

    $context = context_system::instance();

    $pagetitle = get_string('pluginname', 'local_gas');
    $pageheading = get_string('pluginname', 'local_gas');

    echo local_gas_page('/index.php?id=' . $id, $pagetitle, $pageheading, $context);

    // Code for manualy activing a link in navigator.
    G\makenavigatorlinkactive($PAGE, "courseAssessment");

    echo $OUTPUT->header();

    $initjs = "var formSubmitting = false;
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
                (e || window.event).returnValue = confirmationMessage;
                return confirmationMessage;
            });
        };

        function RefreshChart() {
            var d = [];
            var d2 = [];
            d.push(d2);";
    $labels = G\getattributes(current_language(), time());

    for ($i = 1; $i <= count($labels); $i++) {
        $labelname = $labels[$i - 1]->name;
        $labelid = $labels[$i - 1]->attribute_id;
        $initjs .= "d[0].push({axis: '$labelname', value: $('#aveOf$labelid').val() });";
    }

    $initjs .= "    RadarChart.drawChart(d, []);
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
                    RefreshChart();
                    $('.rangeInput').keyup(function (event) {
                        RefreshChart();
                    });
                    $('.rangeInput').mouseup(function (event) {
                        RefreshChart();
                    });
                });
            var w = 200, h = 200;

            var mycfg = {
                w: w,
                h: h,
                maxValue: 5,
                levels: 5,
                ExtraWidthX: 150
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
            }";
    echo html_writer::script($initjs);
    // Print_object($USER);.

    $cyear = date("Y");
    $csem = G\semofdate(date("d"), date("m"));
    // Getting all courses this users in enrolled in as instructor.
    $courses = G\coursesas($USER->id, "teacher");

    $act = optional_param("action", null, PARAM_TEXT);
    $choosencourse = null;
    $datasaved = false;

    // If the form submited for choosing a course.
    if ($act == "choosingCourse") {
        $choosencourse = optional_param("courseID", null, PARAM_INT);
    } else if ($act == "assessment") {
        // If the form submited for assessing a course.

        $teacherid = $USER->id;
        $timetaken = time() - optional_param("AssStartTime", null, PARAM_INT);
        $courseid = optional_param("course_id", null, PARAM_INT);

        // Creating a new row for course assessment.
        $assid = G\newassessment($teacherid, time(), $timetaken, $csem, "Teacher", $courseid);

        $rangeinput = optional_param_array("rangeInput", null, PARAM_NUMBER);
        $subattids = optional_param_array("subattID", null, PARAM_INT);

        // Creating a new row for each sub-attributed assessed.
        foreach ($subattids as $subattid) {
            G\newsubattassessment($assid, $subattid, current($rangeinput), "Teacher");
            next($rangeinput);
        }

        $datasaved = true;
    }
    ?>
    <div  style="margin-bottom: 0px; width:1100px;" class='block_course_overview  block'>
        <div class="header">
            <div class="title">
                <h2><i class='fa fa-question-circle'></i>
                    <?php echo(" " . get_string('courseAssessment', 'local_gas')) ?> </h2>
            </div>
        </div>
        <div class='content'>
            <?php
    if ($datasaved) {
                echo("<div class='alert alert-success'><b>".get_string('savedmes', 'local_gas')."</b></div>");
    }
            ?>
            <div>
                <form action="teacherassessment.php" method="post">
                    <input type="hidden" name='action' value='choosingCourse'>
                    <label><?php echo(get_string('courseToAsess', 'local_gas')); ?>
                        <select name='courseID'>
                            <option value='0' <?php
    if ($choosencourse == null) {
                                echo("selected");
    }
                            ?> >-Select Course-</option>
                                    <?php
                                    // Listing the courses for instructor to choose from.
    foreach ($courses as $course) {
                                        $cn = $course->shortname;
                                        $ci = $course->id;
                                        $ct = G\semandyearofcourse($ci);
                                        $cs = $ct['sem'];
                                        $cy = $ct['year'];
                                        $selected = "";
        if ($ci == $choosencourse) {
                                            $selected = "selected";
        }
                                        echo("<option value='$ci' $selected>$cn ($cy-$cs)</option>");
    }
                                    ?>
                        </select><br/>
                        <input type="submit">
                    </label>
                </form>
            </div>
            <?php 
    if ($choosencourse != null) {
    ?>
                <table border="0">
                    <tr>
                        <td>
                            <div style="width:800px; display:inline-block;">
                                <br/>
                                <form action="teacherassessment.php" method="post" onsubmit="setFormSubmitting()">
                                    <input type='hidden' value='assessment' name='action'>
                                    <input type='hidden' value='<?php echo(time()); ?>' name='AssStartTime'>
                                    <input type='hidden' value=<?php echo("'" + $choosencourse + "'"); ?> name='course_id'>
                                    <ul class="nav nav-tabs" id="myTabs">
                                        <?php
                                        // Creating tabs for each attribute.
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
                                        $i = 1;
        for ($i = 1; $i <= count($labels); $i ++) {
                                            echo ("<div class='tab-pane ");
            if ($i == 1) {
                                                echo("active");
            }
                                            echo("' id='tabs$i'>");
                                            // This function will create the divison needed for assessment of one attribute.
                                            G\teachersliderset($USER->id, $choosencourse, $labels[$i - 1]->attribute_id);

                                            echo ("</div>");
        }
                                        ?>
                                    </div><br/>
                                    <input type="submit" value="Save Changes">
                                </form>
                            </div>
                        </td>
                        <td style="vertical-align: top">
                            <div style='width: 250px; text-align: center;'>
                                <br/>
                                <br/>
                                <?php
                                // The division for the plot.
                                echo("
                                    <div id='chartbody'>
                                        <div id='chart'></div>
                                    </div>
                                    ");
                                ?>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
            <?php
    }
        ?>
    </div>
    </div>

    <?php
    echo $OUTPUT->footer();
};

$displaypage();
