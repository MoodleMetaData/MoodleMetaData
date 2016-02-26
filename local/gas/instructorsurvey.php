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
// Description: This page is for the survey of instructors.

require_once(dirname(__FILE__) . '/config.php');
require_once(dirname(__FILE__) . '/locallib.php');

$displaypage = function () {
    // CHECK And PREPARE DATA.
    global $CFG, $OUTPUT, $SESSION, $PAGE, $DB, $COURSE, $USER;

    $id = optional_param('id', 0, PARAM_INT); // List id.

    require_login(1, false); // Use course 1 because this has nothing to do with an actual course, just like course 1.

    $context = context_system::instance();

    $pagetitle = get_string('pluginname', 'local_gas');
    $pageheading = get_string('pluginname', 'local_gas');

    echo local_gas_page('/index.php?id=' . $id, $pagetitle, $pageheading, $context);

    echo $OUTPUT->header();

    // JS functions below is for changing the content of department input based on the faculty.
    $initjs = "
             $(document).ready(function() {
                    $('.parent').change(function(){
                        var is = 2;
                        if($(this).val() == 'Other'){
                            is = 1;
                        }
                        else{
                            is = 0;
                        }
                        var id = $(this).parent().attr('id');
                        toggleChild(id, is);
                    });
            });
            function toggleChild(id, is) {
                if (is == 1) {
                    $('tr').filter('.child' + id).show();
                }
                else if(is == 0){
                    $('tr').filter('.child' + id).hide();
                }
            }";

    echo html_writer::script($initjs);

    $act = optional_param("action", null, PARAM_TEXT);
    ?>
    <div style="margin-bottom: 0px;" class='block_course_overview  block'>
        <div class="header">
            <div class="title"><h2><i class='fa fa-question-circle'></i><?php
                    echo(" " . get_string('survey', 'local_gas'));
    if ($act == "page1") {
                        echo(" ( page 1 / 1 )");
    }
                    ?> </h2>
            </div>
        </div>
        <div class='content'>
            <?php
    if ($act == "page1") {
                ?>
                <form action="index.php" method="post">
                    <input type="hidden" name="action" value="submited">
                    <input type="hidden" name="survey" value="instructor">
                    <table class='table'>
                        <tr class='active'>
                            <td style="width:70%;">
                                <label for="gender"><?php echo(get_string('ssurvey1', 'local_gas')) ?> </label>
                            </td>
                            <td>
                                <select name='gender'>
                                    <option value='male'>
                                        <?php echo(get_string('ssurvey11', 'local_gas')) ?> </option>
                                    <option value='female'>
                                        <?php echo(get_string('ssurvey12', 'local_gas')) ?> </option>
                                    <option value='unspecified'>
                                        <?php echo(get_string('ssurvey13', 'local_gas')) ?> </option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="position"><?php echo(get_string('isurvey2', 'local_gas')); ?></label>
                            </td>
                            <td id='001'>
                                <select class='parent' name='position' id='position'>
                                    <option value='empty'><?php echo(get_string('select', 'local_gas')); ?></option>
                                    <option value='Graduate teaching assistant'><?php echo(get_string('position1', 'local_gas')); ?>
                                    </option>
                                    <option value='Sessional'><?php echo(get_string('position2', 'local_gas')); ?></option>
                                    <option value='Lecturer'><?php echo(get_string('position3', 'local_gas')); ?></option>
                                    <option value='Instructor'><?php echo(get_string('position4', 'local_gas')); ?></option>
                                    <option value='Adjunct Professor'><?php echo(get_string('position5', 'local_gas')); ?></option>
                                    <option value='Visiting Professor'><?php echo(get_string('position6', 'local_gas')); ?></option>
                                    <option value='Assistant Professor'><?php echo(get_string('position7', 'local_gas')); ?>
                                    </option>
                                    <option value='Associate Professor'><?php echo(get_string('position8', 'local_gas')); ?>
                                    </option>
                                    <option value='Full Professor'><?php echo(get_string('position9', 'local_gas')); ?></option>
                                    <option value='Emeritus Professor'><?php echo(get_string('position10', 'local_gas')); ?>
                                    </option>
                                    <option value='Other'><?php echo(get_string('other', 'local_gas')); ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr class='child child001'>
                            <td>
                                <label><?php echo(get_string('isurvey21', 'local_gas')) ?></label>
                            </td>
                            <td>
                                <input type="text" name="otherPorition" id="otherPorition">
                            </td>
                        </tr>
                        <tr class="active">
                            <td>
                                <label for="faculty"><?php echo(get_string('isurvey3', 'local_gas')) ?></label>
                            </td>
                            <td>
                                <select name='faculty' id='faculty'>
                                    <option value='-select-'>-select-</option>
                                    <option value='Alberta School of Business'>Alberta School of Business</option>
                                    <option value='Agricultural, Life and Environmental Sciences'>
                                        Agricultural, Life and Environmental Sciences</option>
                                    <option value='Arts'>Arts</option>
                                    <option value='Augustana'>Augustana</option>
                                    <option value='Campus Saint-Jean '>Campus Saint-Jean</option>
                                    <option value='Education'>Education</option>
                                    <option value='Engineering'>Engineering</option>
                                    <option value='Law'>Law</option>
                                    <option value='Medicine & Dentistry'>Medicine & Dentistry</option>
                                    <option value='Native Studies'>Native Studies</option>
                                    <option value='Nursing'>Nursing</option>
                                    <option value='Pharmacy and Pharmaceutical Sciences'>
                                        Pharmacy and Pharmaceutical Sciences</option>
                                    <option value='Physical Education and Recreation'>
                                        Physical Education and Recreation</option>
                                    <option value='Science'>Science</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label><?php echo(get_string('isurvey4', 'local_gas')) ?></label>
                            </td>
                            <td>
                                <input type="text" name="subject" id="subject">
                            </td>
                        </tr>
                        <tr class="active">
                            <td>
                                <label><?php echo(get_string('isurvey5', 'local_gas')) ?></label>
                            </td>
                            <td>
                                <input type="number" name="yearsOfTeaching" id="yearsOfTeaching">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label><?php echo(get_string('isurvey6', 'local_gas')) ?></label>
                            </td>
                            <td>
                                <input type="text" name="discipline" id="discipline">
                            </td>
                        </tr>
                        <tr class="active">
                            <td colspan="2">
                                <label><?php echo(get_string('isurvey7', 'local_gas')) ?></label><br/>
                                <textarea name="expandOnAnswers" class="form-control" rows="3"></textarea>
                            </td>
                        </tr>
                    </table>
                    <input type="submit" value="Submit">
                </form>
                <?php
    } else {
                echo(get_string('isurvey0', 'local_gas'));
                ?>

                <form action="instructorsurvey.php" method="post">
                    <input type="hidden" name="action" value="page1">
                    <input type="submit" value="Start Survey">
                </form>
                <p>
                    <i><?php echo(get_string('ssurvey0ps', 'local_gas')) ?></i>
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
