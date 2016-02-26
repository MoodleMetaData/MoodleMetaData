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

    // Cheking if the user accessing this page is an instructor.
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
    G\makenavigatorlinkactive($PAGE, "courseAssessmentReport");

    echo $OUTPUT->header();

    $choosencourse = null;

    $act = optional_param("action", null, PARAM_TEXT);

    // If the form submited to select a course.
    if ($act == "choosingCourse") {
        $choosencourse = optional_param("courseID", null, PARAM_INT);
    }

    $initjs = "$(document).ready(function () {
                    $('#myTabs a').click(function (e) {
                        e.preventDefault()
                        $(this).tab('show')
                      });";
    if ($choosencourse != null) {
        $initjs .= "RefreshChart();";
    }
    $initjs .= "});
            var w = 200, h = 400;
            var mycfg = {
                w: w,
                h: h,
                maxValue: 5,
                levels: 5,
                ExtraWidthX: 150
            };";
    echo html_writer::script($initjs);

    $currentyear = date("Y");
    $currentsem = G\semofdate(date("d"), date("m"));

    // Getting the list of courses instructor has this semester.
    $courses = G\coursesas($USER->id, "teacher");
    ?>
    <div style="margin-bottom: 0px;" class='block_course_overview  block'>
        <div class="header">
            <div class="title">
                <h2><i class='fa fa-book'></i><?php
                    echo(" "
                    . get_string('courseAssessmentReport', 'local_gas'))
                    ?> </h2>
            </div>
        </div>
        <div class='content'>
            <div>
                <form action="coursereport.php" method="post">
                    <input type="hidden" name='action' value='choosingCourse'>
                    <label><?php echo(get_string('courseToCheckReport', 'local_gas')); ?>
                        <select name='courseID'>
                            <option value='0' <?php
    if ($choosencourse == null) {
                                echo("selected");
    }
                            ?> >-Select Course-</option>
                                    <?php
                                    // Listing the courses for instructor to choose.
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
                            <div>
                                <?php
                                // This data is needed for the plot.
                                $data = G\coursereportpage($choosencourse);
                                ?>
                            </div>
                        </td>
                        <td style="vertical-align: top">
                            <div style='text-align: center;'>
                                <?php
                                // Creating the division for the chart.
                                echo("<div id='chartbody'>
                                        <div id='chart'></div>
                                    </div>");
                                ?>
                            </div>
                        </td>
                    </tr>
                </table>
                <?php
    }
            ?>
        </div>
    </div>
    <script>

        function RefreshChart() {
            var d = [];
            var d2 = [];
            var d3 = [];
            d.push(d2);
            d.push(d3);
    <?php
    $labels = G\getattributes(current_language(), time());
    $data2 = G\loadteacherassessment($USER->id, $choosencourse);

    // Inserting the data for plot into the js function in order to plot.
    for ($i = 1; $i <= count($labels); $i++) {
        $labelname = $labels[$i - 1]->name;
        $attvalue = G\assessattvalue($data, $labels[$i - 1]->attribute_id);
        $attvalue2 = G\assessattvalue($data2, $labels[$i - 1]->attribute_id);

        echo("d[0].push({axis: '$labelname', value: $attvalue2 });");
        echo("d[1].push({axis: '$labelname', value: $attvalue });");
    }
    echo("RadarChart.drawChart(d, ['".get_string('yourassessment', 'local_gas')."', '"
            .get_string('studentsassessment', 'local_gas')."']);");
    ?>
        }
    </script>


    <?php
    echo $OUTPUT->footer();
};

$displaypage();
