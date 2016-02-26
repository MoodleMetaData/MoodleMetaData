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

require_once('config.php');
require_once('locallib.php');

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
    $initjs = "$(document).ready(function() {
                   $('#faculty').change(function() {
                        if($(this).data('options') == undefined){
                            $(this).data('options',$('#department option').clone());
                            }
                        var id = $(this).val().substring(0, 3);
                        if(id == '-se'){
                            var options = $(this).data('options').filter('[class=' + id + ']');
                            $('#department').html(options);
                            $('#department').prop('disabled', true);
                        }
                        else{
                            var options = $(this).data('options').filter('[class=' + id + ']');
                            $('#department').html(options);
                            $('#department').prop('disabled', false);
                            toggleChild('005', 0);
                        }
                    });
                    $('.parent').click(function(){
                        var is = 2;
                        if($(this).val() == '1'){
                            is = 1;
                        }
                        else{
                            is = 0;
                        }
                        var id = $(this).parent().parent().attr('id');
                        toggleChild(id, is);
                    });
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
                    $('#whyNone').hide();
                    $('#otherActivity').hide();
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

    if ($act == "page2") {
        $row['gender'] = optional_param("gender", null, PARAM_TEXT);
        $row['age'] = optional_param("age", null, PARAM_TEXT);
        $row['year_of_study'] = optional_param("yearOfStudy", null, PARAM_TEXT);
        $row['has_post_secondary_education'] = optional_param("postsecondaryEducation", null, PARAM_TEXT);
        $row['institution'] = optional_param("institution", null, PARAM_TEXT);
        $row['area_of_study'] = optional_param("AreaOfStudy", null, PARAM_TEXT);
        $row['num_of_years'] = optional_param("numOfYears", null, PARAM_TEXT);
        $row['has_cer_dip_deg'] = optional_param("cer/dip/deg", null, PARAM_TEXT);
        $row['has_certificate'] = optional_param("certificate", null, PARAM_TEXT) == "yes" ? 1 : 0;
        $row['has_diploma'] = optional_param("diploma", null, PARAM_TEXT) == "yes" ? 1 : 0;
        $row['has_degree'] = optional_param("Degree", null, PARAM_TEXT) == "yes" ? 1 : 0;
        $row['other_cer_dip_deg'] = optional_param("otherCerDipDeg", null, PARAM_TEXT);
        $row['lives_on_campus'] = optional_param("liveOnCampus", null, PARAM_TEXT);
        $row['is_international_student'] = optional_param("internationalStudent", null, PARAM_TEXT);
        $row['country'] = optional_param("country", null, PARAM_TEXT);
        $row['faculty'] = optional_param("faculty", null, PARAM_TEXT);
        $row['department'] = optional_param("department", null, PARAM_TEXT);
        $row['other_department'] = optional_param("otherDepartment", null, PARAM_TEXT);
        $row['major'] = optional_param("major", null, PARAM_TEXT);
        $row['minor'] = optional_param("minor", null, PARAM_TEXT);
        $row['pursuing_certificate'] = optional_param("havingCertificate", null, PARAM_TEXT);
        $row['certificate'] = optional_param("certificate2", null, PARAM_TEXT);
        $row['student_id'] = $USER->id;
        $row['timestamp'] = time();

        $id = $DB->insert_record("local_gas_student_survey", $row);
    }
    ?>
    <div style="margin-bottom: 0px;" class='block_course_overview  block'>
        <div class="header">
            <div class="title">
                <h3><i class='fa fa-question-circle'></i><?php
                    echo(" " . get_string('survey', 'local_gas'));
    if ($act == "page1") {
                        echo(" ( page 1 / 2 )");
    } else if ($act == "page2") {
                        echo(" ( page 2 / 2 ) ");
    }
                    ?></h3>
            </div>
        </div>
        <div class='content'>
            <?php
    if ($act == "page1") {
                ?>
                <form action="survey.php" method="post">
                    <input type="hidden" name="action" value="page2">
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
                                <label for="age"><?php echo(get_string('ssurvey2', 'local_gas')) ?></label>
                            </td>
                            <td>
                                <input name="age" max="99" min="10" type="number" id="age">
                            </td>
                        </tr>
                        <tr class='active'>
                            <td>
                                <label for="yearOfStudy"><?php echo(get_string('ssurvey3', 'local_gas')) ?></label>
                            </td>
                            <td>
                                <input name="yearOfStudy" min="1" max="10" type="number">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="postsecondaryEducation">
                                    <?php echo(get_string('ssurvey4', 'local_gas')) ?></label>
                            </td>
                            <td id="001">
                                <label><input class="parent" type="radio" name="postsecondaryEducation" value="1" >
                                    <?php echo(get_string('yes', 'local_gas')) ?></label>
                                <label><input class="parent" type="radio" name="postsecondaryEducation" value="0" >
                                    <?php echo(get_string('no', 'local_gas')) ?></label>
                            </td>
                        </tr>
                        <tr class="child child001">
                            <td>
                                <label for="institution"><?php echo(get_string('ssurvey41', 'local_gas')) ?></label>
                            </td>
                            <td>
                                <input type="text" name="institution">
                                <label><i class='fa fa-info-circle' data-toggle='tooltip'  data-placement='bottom' 
                                          <?php echo("title='" . get_string('seperation', 'local_gas') . "'"); ?> ></i>
                                </label>
                            </td>
                        </tr>
                        <tr class="child child001">
                            <td>
                                <label for="AreaOfStudy"><?php echo(get_string('ssurvey42', 'local_gas')) ?>
                                </label>
                            </td>
                            <td>
                                <input type="text" name="AreaOfStudy">
                                <label><i class='fa fa-info-circle' data-toggle='tooltip'  data-placement='bottom' 
                                          <?php echo("title='" . get_string('seperation', 'local_gas') . "'"); ?> ></i>
                                </label>
                            </td>
                        </tr>
                        <tr class="child child001">
                            <td>
                                <label for="numOfYears"><?php echo(get_string('ssurvey43', 'local_gas')) ?></label>
                            </td>
                            <td>
                                <input name="numOfYears" type="number" min="1" max="10">
                            </td>
                        </tr>
                        <tr class='active'>
                            <td>
                                <label for="cer/dip/deg"><?php echo(get_string('ssurvey5', 'local_gas')) ?></label>
                            </td>
                            <td id="002">
                                <label><input class='parent' type="radio" name="cer/dip/deg" value="1">
                                    <?php echo(get_string('yes', 'local_gas')) ?></label>
                                <label><input class='parent' type="radio" name="cer/dip/deg" value="0">
                                    <?php echo(get_string('no', 'local_gas')) ?></label>
                            </td>
                        </tr>
                        <tr class='active child child002'>
                            <td>
                                <label><?php echo(get_string('ssurvey51', 'local_gas')) ?></label>
                            </td>
                            <td>
                                <label class="checkbox"><input type="checkbox" name="certificate" value="yes">
                                    <?php echo(get_string('ssurvey52', 'local_gas')) ?></label>
                                <label class="checkbox"><input type="checkbox" name="diploma" value="yes">
                                    <?php echo(get_string('ssurvey53', 'local_gas')) ?></label>
                                <label class="checkbox"><input type="checkbox" name="Degree" value="yes">
                                    <?php echo(get_string('ssurvey54', 'local_gas')) ?></label>
                                <input type="text" name="otherCerDipDeg" id="otherCerDipDeg" placeholder="Others">
                                <label><i class='fa fa-info-circle' data-toggle='tooltip'  data-placement='bottom' 
                                          <?php echo("title='" . get_string('seperation', 'local_gas') . "'"); ?> ></i>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="liveOnCampus"><?php echo(get_string('ssurvey6', 'local_gas')) ?>
                                </label>
                            </td>
                            <td>
                                <label><input type="radio" name="liveOnCampus" value="1">
                                    <?php echo(get_string('ssurvey61', 'local_gas')) ?></label>
                                <label><input type="radio" name="liveOnCampus" value="0">
                                    <?php echo(get_string('ssurvey62', 'local_gas')) ?></label>
                            </td>
                        </tr>
                        <tr class='active'>
                            <td>
                                <label for="internationalStudent">
                                    <?php echo(get_string('ssurvey7', 'local_gas')) ?></label>
                            </td>
                            <td id='003'>
                                <label><input class='parent' type="radio" name="internationalStudent" value="1">
                                    <?php echo(get_string('yes', 'local_gas')) ?></label>
                                <label><input class='parent' type="radio" name="internationalStudent" value="0">
                                    <?php echo(get_string('no', 'local_gas')) ?></label>
                            </td>
                        </tr>
                        <tr class='active child child003'>
                            <td>
                                <label for="country"><?php echo(get_string('ssurvey71', 'local_gas')) ?></label>
                            </td>
                            <td>
                                <select name='country'>
                                    <option value='Afghanistan'>Afghanistan</option>
                                    <option value='Albania'>Albania</option>
                                    <option value='Algeria'>Algeria</option>
                                    <option value='Andorra'>Andorra</option>
                                    <option value='Angola'>Angola</option>
                                    <option value='Antigua and Barbuda'>Antigua and Barbuda</option>
                                    <option value='Argentina'>Argentina</option>
                                    <option value='Armenia'>Armenia</option>
                                    <option value='Aruba'>Aruba</option>
                                    <option value='Australia'>Australia</option>
                                    <option value='Austria'>Austria</option>
                                    <option value='Azerbaijan'>Azerbaijan</option>
                                    <option value='Bahamas, The'>Bahamas, The</option>
                                    <option value='Bahrain'>Bahrain</option>
                                    <option value='Bangladesh'>Bangladesh</option>
                                    <option value='Barbados'>Barbados</option>
                                    <option value='Belarus'>Belarus</option>
                                    <option value='Belgium'>Belgium</option>
                                    <option value='Belize'>Belize</option>
                                    <option value='Benin'>Benin</option>
                                    <option value='Bhutan'>Bhutan</option>
                                    <option value='Bolivia'>Bolivia</option>
                                    <option value='Bosnia and Herzegovina'>Bosnia and Herzegovina</option>
                                    <option value='Botswana'>Botswana</option>
                                    <option value='Brazil'>Brazil</option>
                                    <option value='Brunei '>Brunei </option>
                                    <option value='Bulgaria'>Bulgaria</option>
                                    <option value='Burkina Faso'>Burkina Faso</option>
                                    <option value='Burma'>Burma</option>
                                    <option value='Burundi'>Burundi</option>
                                    <option value='Cambodia'>Cambodia</option>
                                    <option value='Cameroon'>Cameroon</option>
                                    <option value='Canada'>Canada</option>
                                    <option value='Cape Verde'>Cape Verde</option>
                                    <option value='Central African Republic'>Central African Republic</option>
                                    <option value='Chad'>Chad</option>
                                    <option value='Chile'>Chile</option>
                                    <option value='China'>China</option>
                                    <option value='Colombia'>Colombia</option>
                                    <option value='Comoros'>Comoros</option>
                                    <option value='Congo, Democratic Republic of the'>
                                        Congo, Democratic Republic of the</option>
                                    <option value='Congo, Republic of the'>Congo, Republic of the</option>
                                    <option value='Costa Rica'>Costa Rica</option>
                                    <option value='Cote d'Ivoire'>Cote d'Ivoire</option>
                                    <option value='Croatia'>Croatia</option>
                                    <option value='Cuba'>Cuba</option>
                                    <option value='Curacao'>Curacao</option>
                                    <option value='Cyprus'>Cyprus</option>
                                    <option value='Czech Republic'>Czech Republic</option>
                                    <option value='Denmark'>Denmark</option>
                                    <option value='Djibouti'>Djibouti</option>
                                    <option value='Dominica'>Dominica</option>
                                    <option value='Dominican Republic'>Dominican Republic</option>
                                    <option value='Ecuador'>Ecuador</option>
                                    <option value='Egypt'>Egypt</option>
                                    <option value='El Salvador'>El Salvador</option>
                                    <option value='Equatorial Guinea'>Equatorial Guinea</option>
                                    <option value='Eritrea'>Eritrea</option>
                                    <option value='Estonia'>Estonia</option>
                                    <option value='Ethiopia'>Ethiopia</option>
                                    <option value='Fiji'>Fiji</option>
                                    <option value='Finland'>Finland</option>
                                    <option value='France'>France</option>
                                    <option value='Gabon'>Gabon</option>
                                    <option value='Gambia, The'>Gambia, The</option>
                                    <option value='Georgia'>Georgia</option>
                                    <option value='Germany'>Germany</option>
                                    <option value='Ghana'>Ghana</option>
                                    <option value='Greece'>Greece</option>
                                    <option value='Grenada'>Grenada</option>
                                    <option value='Guatemala'>Guatemala</option>
                                    <option value='Guinea'>Guinea</option>
                                    <option value='Guinea-Bissau'>Guinea-Bissau</option>
                                    <option value='Guyana'>Guyana</option>
                                    <option value='Haiti'>Haiti</option>
                                    <option value='Honduras'>Honduras</option>
                                    <option value='Hong Kong'>Hong Kong</option>
                                    <option value='Hungary'>Hungary</option>
                                    <option value='Iceland'>Iceland</option>
                                    <option value='India'>India</option>
                                    <option value='Indonesia'>Indonesia</option>
                                    <option value='Iran'>Iran</option>
                                    <option value='Iraq'>Iraq</option>
                                    <option value='Ireland'>Ireland</option>
                                    <option value='Israel'>Israel</option>
                                    <option value='Italy'>Italy</option>
                                    <option value='Jamaica'>Jamaica</option>
                                    <option value='Japan'>Japan</option>
                                    <option value='Jordan'>Jordan</option>
                                    <option value='Kazakhstan'>Kazakhstan</option>
                                    <option value='Kenya'>Kenya</option>
                                    <option value='Kiribati'>Kiribati</option>
                                    <option value='Korea, North'>Korea, North</option>
                                    <option value='Korea, South'>Korea, South</option>
                                    <option value='Kosovo'>Kosovo</option>
                                    <option value='Kuwait'>Kuwait</option>
                                    <option value='Kyrgyzstan'>Kyrgyzstan</option>
                                    <option value='Laos'>Laos</option>
                                    <option value='Latvia'>Latvia</option>
                                    <option value='Lebanon'>Lebanon</option>
                                    <option value='Lesotho'>Lesotho</option>
                                    <option value='Liberia'>Liberia</option>
                                    <option value='Libya'>Libya</option>
                                    <option value='Liechtenstein'>Liechtenstein</option>
                                    <option value='Lithuania'>Lithuania</option>
                                    <option value='Luxembourg'>Luxembourg</option>
                                    <option value='Macau'>Macau</option>
                                    <option value='Macedonia'>Macedonia</option>
                                    <option value='Madagascar'>Madagascar</option>
                                    <option value='Malawi'>Malawi</option>
                                    <option value='Malaysia'>Malaysia</option>
                                    <option value='Maldives'>Maldives</option>
                                    <option value='Mali'>Mali</option>
                                    <option value='Malta'>Malta</option>
                                    <option value='Marshall Islands'>Marshall Islands</option>
                                    <option value='Mauritania'>Mauritania</option>
                                    <option value='Mauritius'>Mauritius</option>
                                    <option value='Mexico'>Mexico</option>
                                    <option value='Micronesia'>Micronesia</option>
                                    <option value='Moldova'>Moldova</option>
                                    <option value='Monaco'>Monaco</option>
                                    <option value='Mongolia'>Mongolia</option>
                                    <option value='Montenegro'>Montenegro</option>
                                    <option value='Morocco'>Morocco</option>
                                    <option value='Mozambique'>Mozambique</option>
                                    <option value='Namibia'>Namibia</option>
                                    <option value='Nauru'>Nauru</option>
                                    <option value='Nepal'>Nepal</option>
                                    <option value='Netherlands'>Netherlands</option>
                                    <option value='Netherlands Antilles'>Netherlands Antilles</option>
                                    <option value='New Zealand'>New Zealand</option>
                                    <option value='Nicaragua'>Nicaragua</option>
                                    <option value='Niger'>Niger</option>
                                    <option value='Nigeria'>Nigeria</option>
                                    <option value='North Korea'>North Korea</option>
                                    <option value='Norway'>Norway</option>
                                    <option value='Oman'>Oman</option>
                                    <option value='Pakistan'>Pakistan</option>
                                    <option value='Palau'>Palau</option>
                                    <option value='Palestinian Territories'>Palestinian Territories</option>
                                    <option value='Panama'>Panama</option>
                                    <option value='Papua New Guinea'>Papua New Guinea</option>
                                    <option value='Paraguay'>Paraguay</option>
                                    <option value='Peru'>Peru</option>
                                    <option value='Philippines'>Philippines</option>
                                    <option value='Poland'>Poland</option>
                                    <option value='Portugal'>Portugal</option>
                                    <option value='Qatar'>Qatar</option>
                                    <option value='Romania'>Romania</option>
                                    <option value='Russia'>Russia</option>
                                    <option value='Rwanda'>Rwanda</option>
                                    <option value='Saint Kitts and Nevis'>Saint Kitts and Nevis</option>
                                    <option value='Saint Lucia'>Saint Lucia</option>
                                    <option value='Saint Vincent and the Grenadines'>
                                        Saint Vincent and the Grenadines</option>
                                    <option value='Samoa '>Samoa </option>
                                    <option value='San Marino'>San Marino</option>
                                    <option value='Sao Tome and Principe'>Sao Tome and Principe</option>
                                    <option value='Saudi Arabia'>Saudi Arabia</option>
                                    <option value='Senegal'>Senegal</option>
                                    <option value='Serbia'>Serbia</option>
                                    <option value='Seychelles'>Seychelles</option>
                                    <option value='Sierra Leone'>Sierra Leone</option>
                                    <option value='Singapore'>Singapore</option>
                                    <option value='Sint Maarten'>Sint Maarten</option>
                                    <option value='Slovakia'>Slovakia</option>
                                    <option value='Slovenia'>Slovenia</option>
                                    <option value='Solomon Islands'>Solomon Islands</option>
                                    <option value='Somalia'>Somalia</option>
                                    <option value='South Africa'>South Africa</option>
                                    <option value='South Korea'>South Korea</option>
                                    <option value='South Sudan'>South Sudan</option>
                                    <option value='Spain '>Spain </option>
                                    <option value='Sri Lanka'>Sri Lanka</option>
                                    <option value='Sudan'>Sudan</option>
                                    <option value='Suriname'>Suriname</option>
                                    <option value='Swaziland '>Swaziland </option>
                                    <option value='Sweden'>Sweden</option>
                                    <option value='Switzerland'>Switzerland</option>
                                    <option value='Syria'>Syria</option>
                                    <option value='Taiwan'>Taiwan</option>
                                    <option value='Tajikistan'>Tajikistan</option>
                                    <option value='Tanzania'>Tanzania</option>
                                    <option value='Thailand '>Thailand </option>
                                    <option value='Timor-Leste'>Timor-Leste</option>
                                    <option value='Togo'>Togo</option>
                                    <option value='Tonga'>Tonga</option>
                                    <option value='Trinidad and Tobago'>Trinidad and Tobago</option>
                                    <option value='Tunisia'>Tunisia</option>
                                    <option value='Turkey'>Turkey</option>
                                    <option value='Turkmenistan'>Turkmenistan</option>
                                    <option value='Tuvalu'>Tuvalu</option>
                                    <option value='Uganda'>Uganda</option>
                                    <option value='Ukraine'>Ukraine</option>
                                    <option value='United Arab Emirates'>United Arab Emirates</option>
                                    <option value='United Kingdom'>United Kingdom</option>
                                    <option value='Uruguay'>Uruguay</option>
                                    <option value='Uzbekistan'>Uzbekistan</option>
                                    <option value='Vanuatu'>Vanuatu</option>
                                    <option value='Venezuela'>Venezuela</option>
                                    <option value='Vietnam'>Vietnam</option>
                                    <option value='Yemen'>Yemen</option>
                                    <option value='Zambia'>Zambia</option>
                                    <option value='Zimbabwe'>Zimbabwe</option>
                                    <option value='Other'>Other</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="faculty"><?php echo(get_string('ssurvey8', 'local_gas')) ?></label>
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
                        <tr class='active'>
                            <td>
                                <label for="department"><?php echo(get_string('ssurvey9', 'local_gas')) ?></label>
                            </td>
                            <td id='005'>
                                <select class='parent' name='department' id='department' disabled="true">
                                    <option class='-se' value='empty' hidden>-select-</option>
                                    <option class='Agr' value='Agricultural, Food & Nutritional Science' hidden>
                                        Agricultural, Food & Nutritional Science</option>
                                    <option class='Agr' value='Human Ecology'>Human Ecology</option>
                                    <option class='Agr' value='Renewable Resources'>Renewable Resources</option>
                                    <option class='Agr' value='Resource Economics and Environmental Sociology'>
                                        Resource Economics and Environmental Sociology</option>
                                    <option class='Agr' value='Alberta School of Forest Science & Management'>
                                        Alberta School of Forest Science & Management</option>
                                    <option class='Agr' value='Other'>Other</option>
                                    <option class='Alb' value='Accounting, Operations and Information Systems'>
                                        Accounting, Operations and Information Systems</option>
                                    <option class='Alb' value='Finance and Statistical Analysis'>
                                        Finance and Statistical Analysis</option>
                                    <option class='Alb' value='Marketing, Business Economics, and Law'>
                                        Marketing, Business Economics, and Law</option>
                                    <option class='Alb' value='Strategic Management and Organization'>
                                        Strategic Management and Organization</option>
                                    <option class='Alb' value='Other'>Other</option>
                                    <option class='Art' value='Anthropology'>Anthropology</option>
                                    <option class='Art' value='Art and Design'>Art and Design</option>
                                    <option class='Art' value='Drama'>Drama</option>
                                    <option class='Art' value='East Asian Studies'>East Asian Studies</option>
                                    <option class='Art' value='Economics'>Economics</option>
                                    <option class='Art' value='English and Film Studies'>
                                        English and Film Studies</option>
                                    <option class='Art' value='History and Classics'>History and Classics</option>
                                    <option class='Art' value='Linguistics'>Linguistics</option>
                                    <option class='Art' value='Modern Languages and Cultural Studies'>
                                        Modern Languages and Cultural Studies</option>
                                    <option class='Art' value='Music'>Music</option>
                                    <option class='Art' value='Philosophy'>Philosophy</option>
                                    <option class='Art' value='Political Science'>Political Science</option>
                                    <option class='Art' value='Psychology (Arts)'>Psychology (Arts)</option>
                                    <option class='Art' value='Sociology'>Sociology</option>
                                    <option class='Art' value="Women's and Gender Studies">
                                        Women's and Gender Studies </option>
                                    <option class='Art' value='Other'>Other</option>
                                    <option class='Aug' value='Augustana Campus - Fine Arts'>
                                        Augustana Campus - Fine Arts</option>
                                    <option class='Aug' value='Augustana Campus - Humanities'>
                                        Augustana Campus - Humanities</option>
                                    <option class='Aug' value='Augustana Campus - Science'>
                                        Augustana Campus - Science</option>
                                    <option class='Aug' value='Augustana Campus -Social Sciences'>
                                        Augustana Campus -Social Sciences</option>
                                    <option class='Aug' value='Other'>Other</option>
                                    <option class='Cam' value='Campus Saint-Jean'>Campus Saint-Jean</option>
                                    <option class='Cam' value='Other'>Other</option>
                                    <option class='Edu' value='Educational Policy Studies'>
                                        Educational Policy Studies</option>
                                    <option class='Edu' value='Educational Psychology'>
                                        Educational Psychology</option>
                                    <option class='Edu' value='Elementary Education'>Elementary Education</option>
                                    <option class='Edu' value='School of Library and Information Studies'>
                                        School of Library and Information Studies</option>
                                    <option class='Edu' value='Secondary Education'>Secondary Education</option>
                                    <option class='Edu' value='Other'>Other</option>
                                    <option class='Eng' value='Biomedical Engineering'>
                                        Biomedical Engineering</option>
                                    <option class='Eng' value='Chemical and Materials Engineering'>
                                        Chemical and Materials Engineering</option>
                                    <option class='Eng' value='Civil and Environmental Engineering'>
                                        Civil and Environmental Engineering</option>
                                    <option class='Eng' value='Electrical and Computer Engineering'>
                                        Electrical and Computer Engineering</option>
                                    <option class='Eng' value='Mechanical Engineering
                                            Mechanical Engineering</option>
                                            <option class='Eng' value='School of Mining and Petroleum Engineering'>
                                            School of Mining and Petroleum Engineering</option>
                                    <option class='Eng' value='Other'>Other</option>
                                    <option class='Nat' value='Native Studies'>Native Studies</option>
                                    <option class='Nat' value='Other'>Other</option>
                                    <option class='Nur' value='Nursing'>Nursing</option>
                                    <option class='Nur' value='Other'>Other</option>
                                    <option class='Pha' value='Pharmacy and Pharmaceutical Sciences'>
                                        Pharmacy and Pharmaceutical Sciences</option>
                                    <option class='Pha' value='Other'>Other</option>
                                    <option class='Phy' value='Physical Education and Recreation'>
                                        Physical Education and Recreation</option>
                                    <option class='Phy' value='Other'>Other</option>
                                    <option class='Sci' value='Biological Sciences'>Biological Sciences</option>
                                    <option class='Sci' value='Chemistry'>Chemistry</option>
                                    <option class='Sci' value='Computing Science'>Computing Science</option>
                                    <option class='Sci' value='Earth and Atmospheric Sciences'>
                                        Earth and Atmospheric Sciences</option>
                                    <option class='Sci' value='Mathematical and Statistical Sciences'>
                                        Mathematical and Statistical Sciences</option>
                                    <option class='Sci' value='Physics'>Physics</option>
                                    <option class='Sci' value='Psychology (Science)'>Psychology (Science)</option>
                                    <option class='Sci' value='Other'>Other</option>
                                    <option class='Med' value='Anesthesiology and Pain Medicine'>
                                        Anesthesiology and Pain Medicine</option>
                                    <option class='Med' value='Biochemistry'>Biochemistry</option>
                                    <option class='Med' value='Biomedical Engineering'>
                                        Biomedical Engineering</option>
                                    <option class='Med' value='Cell Biology'>Cell Biology</option>
                                    <option class='Med' value='Dentistry and Dental Hygiene'>
                                        Dentistry and Dental Hygiene</option>
                                    <option class='Med' value='Emergency Medicine'>Emergency Medicine</option>
                                    <option class='Med' value='Family Medicine'>Family Medicine</option>
                                    <option class='Med' value='Laboratory Medicine and Pathology'>
                                        Laboratory Medicine and Pathology</option>
                                    <option class='Med' value='Medical Genetics'>Medical Genetics</option>
                                    <option class='Med' value='Medical Microbiology and Immunology'>
                                        Medical Microbiology and Immunology</option>
                                    <option class='Med' value='Medicine'>Medicine</option>
                                    <option class='Med' value='Obstetrics and Gynecology'>
                                        Obstetrics and Gynecology</option>
                                    <option class='Med' value='Oncology'>Oncology</option>
                                    <option class='Med' value='Ophthalmology'>Ophthalmology</option>
                                    <option class='Med' value='Pediatrics'>Pediatrics</option>
                                    <option class='Med' value='Pharmacology'>Pharmacology</option>
                                    <option class='Med' value='Physiology'>Physiology</option>
                                    <option class='Med' value='Psychiatry'>Psychiatry</option>
                                    <option class='Med' value='Radiology and Diagnostic Imaging'>
                                        Radiology and Diagnostic Imaging</option>
                                    <option class='Med' value='Surgery'>Surgery</option>
                                    <option class='Med' value='Other'>Other</option>
                                </select>
                            </td>
                        </tr>
                        <tr class='active child child005'>
                            <td>
                                <label><?php echo(get_string('ssurvey91', 'local_gas')) ?></label>
                            </td>
                            <td>
                                <input type="text" name="otherDepartment" id="otherDepartment">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="major"><?php echo(get_string('ssurvey10', 'local_gas')) ?></label>
                            </td>
                            <td>
                                <input name="major" type="text" id="major/minor">
                                <label><i class='fa fa-info-circle' data-toggle='tooltip'  data-placement='bottom' 
                                          <?php echo("title='" . get_string('seperation', 'local_gas') . "'"); ?> ></i>
                                </label>
                            </td>
                        </tr>
                        <tr class='active'>
                            <td>
                                <label for="minor"><?php echo(get_string('ssurvey-11', 'local_gas')) ?></label>
                            </td>
                            <td>
                                <input name="minor" type="text" id="major/minor">
                                <label><i class='fa fa-info-circle' data-toggle='tooltip'  data-placement='bottom' 
                                          <?php echo("title='" . get_string('seperation', 'local_gas') . "'"); ?> >
                                    </i></label>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label><?php echo(get_string('ssurvey-12', 'local_gas')) ?></label>
                            </td>
                            <td id='004'>
                                <label><input class='parent' type="radio" name="havingCertificate" value="yes">
                                    <?php echo(get_string('yes', 'local_gas')) ?></label>
                                <label><input class='parent' type="radio" name="havingCertificate" value="no">
                                    <?php echo(get_string('no', 'local_gas')) ?></label>
                                <label><input class='parent' type="radio" name="havingCertificate" value="unsure">
                                    <?php echo(get_string('ssurvey-121', 'local_gas')) ?></label>
                            </td>
                        </tr>
                        <tr class='child child004'>
                            <td>
                                <label><?php echo(get_string('ssurvey-122', 'local_gas')) ?></label>
                            </td>
                            <td>
                                <input type="text" name='certificate2'>
                            </td>
                        </tr>
                    </table>


                    <input type="submit" value="Next">
                </form>
                <?php
    } else if ($act == "page2") {
                ?>
                <form action="index.php" method="post">
                    <input type="hidden" name="action" value="submited">
                    <input type="hidden" name="survey" value="student">
                    <input type="hidden" name="surveyID" value="<?php echo($id); ?>">
                    <table class='table'>
                        <tr class='active'>
                            <td>
                                <label><?php echo(get_string('ssurvey-13', 'local_gas')) ?></label>
                            </td>
                            <td>
                                <label class='checkbox'><input type='checkbox' name='activity1' value='1'>
                                    <?php echo(get_string('ssurvey-131', 'local_gas')) ?></label>
                                <label class='checkbox'><input type='checkbox' name='activity2' value='1'>
                                    <?php echo(get_string('ssurvey-132', 'local_gas')) ?></label>
                                <label class='checkbox'><input type='checkbox' name='activity3' value='1'>
                                    <?php echo(get_string('ssurvey-133', 'local_gas')) ?></label>
                                <label class='checkbox'><input type='checkbox' name='activity4' value='1'>
                                    <?php echo(get_string('ssurvey-134', 'local_gas')) ?></label>
                                <label class='checkbox'><input type='checkbox' name='activity5' value='1'>
                                    <?php echo(get_string('ssurvey-135', 'local_gas')) ?></label>
                                <label class='checkbox'><input type='checkbox' name='activity6' value='1'>
                                    <?php echo(get_string('ssurvey-136', 'local_gas')) ?></label>
                                <label class='checkbox'><input type='checkbox' name='activity7' value='1'>
                                    <?php echo(get_string('ssurvey-137', 'local_gas')) ?></label>
                                <label class='checkbox'><input type='checkbox' name='noActivity' value='1' 
                                                               onchange="if (this.checked) {
                                                                                   $('#whyNone').show();
                                                                               } else {
                                                                                   $('#whyNone').hide();
                                                                               }">None</label>
                                <input type="text" id="whyNone" name='whyNone' placeholder="please explain why">
                                <label class='checkbox'><input type='checkbox' name='hasOtherActivity' value='1' 
                                                               onchange="if (this.checked) {
                                                                                   $('#otherActivity').show();
                                                                               } else {
                                                                                   $('#otherActivity').hide();
                                                                               }">Other</label>
                                <input type="text" id="otherActivity" name='otherActivity' 
                                       placeholder="please specify">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label><?php echo(get_string('ssurvey14', 'local_gas')) ?></label>
                            </td>
                            <td>
                                <label class="radio"><input type="radio" name="hoursOfActivity" value="0">
                                    <?php echo(get_string('ssurvey141', 'local_gas')) ?></label>
                                <label class="radio"><input type="radio" name="hoursOfActivity" value="less than 1">
                                    <?php echo(get_string('ssurvey142', 'local_gas')) ?></label>
                                <label class="radio"><input type="radio" name="hoursOfActivity" value="1 - 2">
                                    <?php echo(get_string('ssurvey143', 'local_gas')) ?></label>
                                <label class="radio"><input type="radio" name="hoursOfActivity" value="3 - 5">
                                    <?php echo(get_string('ssurvey144', 'local_gas')) ?></label>
                                <label class="radio"><input type="radio" name="hoursOfActivity" value="more than 5">
                                    <?php echo(get_string('ssurvey145', 'local_gas')) ?></label>
                                <input type="text" name='hoursOfActivityText' placeholder="please specify hours">
                            </td>
                        </tr>
                        <tr class="active">
                            <td>
                                <label><?php echo(get_string('ssurvey15', 'local_gas')) ?></label>
                            </td>
                            <td>
                                <label class="radio"><input type="radio" name="hoursOfStudy" value="0">
                                    <?php echo(get_string('ssurvey141', 'local_gas')) ?></label>
                                <label class="radio"><input type="radio" name="hoursOfStudy" value="less than 1">
                                    <?php echo(get_string('ssurvey142', 'local_gas')) ?></label>
                                <label class="radio"><input type="radio" name="hoursOfStudy" value="1 - 2">
                                    <?php echo(get_string('ssurvey143', 'local_gas')) ?></label>
                                <label class="radio"><input type="radio" name="hoursOfStudy" value="3 - 5">
                                    <?php echo(get_string('ssurvey144', 'local_gas')) ?></label>
                                <label class="radio"><input type="radio" name="hoursOfStudy" value="more than 5">
                                    <?php echo(get_string('ssurvey145', 'local_gas')) ?></label>
                                <input type="text" name='hoursOfStudyText' placeholder="please specify hours">
                            </td>
                        </tr>
                    </table>
                    <input type="submit" value="Submit">
                </form>
                <?php
    } else if ($act == "page3") {
                ?>
                <form action="survey.php" method="post">
                    <input type="hidden" name="action" value="submited">

                    <label><?php echo(get_string('ssurvey16', 'local_gas')) ?></label><br/>
                    <textarea name="reasonToParticipate" class="form-control" rows="3"></textarea>
                    <br/>
                    <label><?php echo(get_string('ssurvey17', 'local_gas')) ?></label><br/>
                    <textarea name="gainFromParticipating" class="form-control" rows="3"></textarea>
                    <br/>
                    <input type="submit" value="Submit">
                </form>
                <?php
    } else {
                echo(get_string('ssurvey0', 'local_gas'));
                ?>


                <form action="survey.php" method="post">
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
