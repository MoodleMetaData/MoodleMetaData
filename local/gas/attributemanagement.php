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
    global $OUTPUT, $PAGE, $DB, $COURSE, $USER;

    if (!has_capability('local/gas:administrator', context_course::instance($COURSE->id))) {
        redirect(new moodle_url('/local/gas/index.php'));
    }

    $id = optional_param('id', 0, PARAM_INT); // List id.

    require_login(1, false); // Use course 1 because this has nothing to do with an actual course, just like course 1.

    $context = context_system::instance();

    $pagetitle = get_string('pluginname', 'local_gas');
    $pageheading = get_string('pluginname', 'local_gas');

    local_gas_page('/index.php?id=' . $id, $pagetitle, $pageheading, $context);

    // This part added as the highlighting of the navigators was not working.
    G\makenavigatorlinkactive($PAGE, "attributeManagement");

    echo $OUTPUT->header();

    $initjs = "function ShowSubInfo(val1) {
                    if (document.getElementById('SubInfo' + val1).style.display == 'none') {
                        document.getElementById('SubInfo' + val1).style.display = 'table';
                    }
                    else {
                        document.getElementById('SubInfo' + val1).style.display = 'none';
                    }
                }
                function ShowSubs(val1) {
                    if (document.getElementById('Subs' + val1).style.display == 'none') {
                        document.getElementById('Subs' + val1).style.display = 'table';
                    }
                    else {
                        document.getElementById('Subs' + val1).style.display = 'none';
                    }
                }
                function ShowAddSubAtt(val1) {
                    if (document.getElementById('AddSubAttAdd' + val1).style.display == 'none') {
                        document.getElementById('AddSubAttAdd' + val1).style.display = 'inline';
                    }
                    else {
                        document.getElementById('AddSubAttAdd' + val1).style.display = 'none';
                    }
                }";

    echo html_writer::script($initjs);
    ?>
    <div class='block_course_overview  block'>
        <div class="header">
            <div class="title">
                <h2><i class='fa fa-magic'></i><?php echo(" " . get_string('attributeManagement', 'local_gas')) ?> </h2>
            </div>
        </div>
        <div class='content'>
            <div>
                <?php
                $delete = get_string('delete', 'local_gas');
                $act = optional_param("action", null, PARAM_TEXT);
                $data = null;

                // If the form submited to add attribute.
    if ($act == "addAtt") {
                    $enname = optional_param("attEnName", null, PARAM_TEXT);
                    $frname = optional_param("attFrName", null, PARAM_TEXT);
        if ($enname != null && $frname != null) {
                        G\insertAttribute($enname, $frname);
        }
    } else if ($act == "addAllAtt") {
                    $str2 = optional_param("text", null, PARAM_TEXT);
                    $str = str_replace(PHP_EOL, '', $str2);
                    $str = preg_replace('/\s+/', ' ', trim($str));
                    $strs = explode("#", $str);
                    $attnum = intval(trim(current($strs)));
        for ($i = 0; $i < $attnum; $i++) {
                        $enname = trim(next($strs));
                        $frname = trim(next($strs));
                        $subattnum = intval(trim(next($strs)));
                        $attid = G\insertattribute($enname, $frname);

            for ($j = 0; $j < $subattnum; $j++) {
                            $enname = trim(next($strs));
                            $frname = trim(next($strs));
                            $endes = trim(next($strs));
                            $frdes = trim(next($strs));
                            $endes1 = trim(next($strs));
                            $frdes1 = trim(next($strs));
                            $endes2 = trim(next($strs));
                            $frdes2 = trim(next($strs));
                            $endes3 = trim(next($strs));
                            $frdes3 = trim(next($strs));
                            $endes4 = trim(next($strs));
                            $frdes4 = trim(next($strs));
                            $endes5 = trim(next($strs));
                            $frdes5 = trim(next($strs));

                            G\insertsubattribute($attid, $enname, $frname, $endes, $frdes,
                                    $endes1, $frdes1, $endes2, $frdes2, $endes3, $frdes3, $endes4, $frdes4, $endes5, $frdes5);
            }
        }
    } else if ($act == "addSubAtt") {
                    // If the form submited to add sub-attribute.
                    $enname = optional_param("sattEnName", null, PARAM_TEXT);
                    $frname = optional_param("sattFrName", null, PARAM_TEXT);
                    $endes = optional_param("sattEnDes", null, PARAM_TEXT);
                    $frdes = optional_param("sattFrDes", null, PARAM_TEXT);
                    $endes1 = optional_param("sattEnDes1", null, PARAM_TEXT);
                    $frdes1 = optional_param("sattFrDes1", null, PARAM_TEXT);
                    $endes2 = optional_param("sattEnDes2", null, PARAM_TEXT);
                    $frdes2 = optional_param("sattFrDes2", null, PARAM_TEXT);
                    $endes3 = optional_param("sattEnDes3", null, PARAM_TEXT);
                    $frdes3 = optional_param("sattFrDes3", null, PARAM_TEXT);
                    $endes4 = optional_param("sattEnDes4", null, PARAM_TEXT);
                    $frdes4 = optional_param("sattFrDes4", null, PARAM_TEXT);
                    $endes5 = optional_param("sattEnDes5", null, PARAM_TEXT);
                    $frdes5 = optional_param("sattFrDes5", null, PARAM_TEXT);
                    $attid = optional_param("attid", null, PARAM_TEXT);
        if ($enname != null && $frname != null) {
                        G\insertsubattribute($attid, $enname, $frname, $endes, $frdes, $endes1,
                                $frdes1, $endes2, $frdes2, $endes3, $frdes3, $endes4, $frdes4, $endes5, $frdes5);
        }
    } else if ($act == "deleteAtt") {
                    // If form submited to delete Attribute.
                    $deleteid = optional_param("deleteid", null, PARAM_TEXT);
                    G\deleteattribute($deleteid);
    } else if ($act == "deleteSubAtt") {
                    // If form submited to delete sub-attribute.
                    $deleteid = optional_param("deleteid", null, PARAM_TEXT);
                    G\deletesubattribute($deleteid);
    } else if ($act == "cohorts") {
                    $str2 = optional_param("cohorts", null, PARAM_TEXT);
                    $strs = explode(",", $str2);
        foreach ($strs as $str) {
                        G\addtermid($str);
        }
    } else if ($act == "deletecohorts") {
                    $DB->delete_records("local_gas_activeterm");
    } else if ($act == "deletesems") {
                    $DB->delete_records("local_gas_semesters");
    } else if ($act == "addsems") {
                    $str2 = optional_param("text", null, PARAM_TEXT);
                    $str = str_replace(PHP_EOL, '', $str2);
                    $str = preg_replace('/\s+/', ' ', trim($str));
                    $strs = explode("#", $str);
                    $semnum = intval(trim(current($strs)));
        for ($i = 0; $i < $semnum; $i++) {
                        $row = array();
                        $row['semester'] = trim(next($strs));
                        $row['startmonth'] = intval(trim(next($strs)));
                        $row['startday'] = intval(trim(next($strs)));
                        $row['endmonth'] = intval(trim(next($strs)));
                        $row['endday'] = intval(trim(next($strs)));
                        $DB->execute("insert into {local_gas_semesters} values (?,?,?,?,?)", $row);
        }
    } else if ($act == "getdata") {
                    $table = optional_param("table", null, PARAM_TEXT);
                    $table = "local_gas_" . $table;
                    $data = $DB->get_records($table, array());
    }
                ?>
                <legend><?php echo(" " . get_string('currentAttributes', 'local_gas')) ?>:</legend>
                <table class="table">
                    <tr>
                        <?php
                        $attributes = G\getattributes(current_language(), time());
                        $en = get_string('English', 'local_gas');
                        $fr = get_string('French', 'local_gas');
    foreach ($attributes as $att) {
                            $subattributes = G\getsubattributes($att->attribute_id, current_language(), time());
                            echo("<tr class='success' onclick='ShowSubs($att->attribute_id);'><td>$att->name</td><td>"
                                    . "<form action='attributemanagement.php' "
                            . "method='post'><input type='hidden' name='action' value='deleteAtt' ><input type='hidden'"
                                    . " name='deleteid' "
                            . "value='$att->attribute_id' ><input type='submit' value='$delete' /></form></td></tr>");
                            echo("<tr><td colspan='2'><table class='table' style='display:none;' id='Subs$att->attribute_id'>");
        foreach ($subattributes as $satt) {
                                echo("<tr class='warning' onclick='ShowSubInfo($satt->subattribute_id);'><td>$satt->name</td><td>"
                                . "<form action='attributemanagement.php' method='post'><input type='hidden' name='action'"
                                        . " value='deleteSubAtt' >"
                                . "<input type='hidden' name='deleteid' value='$satt->subattribute_id' >"
                                . "<input type='submit' value='$delete' /></form></td></tr>");
                                $des = get_string('description', 'local_gas');
                                echo("<tr><td colspan='2'><table class='table' style='display:none;'"
                                        . " id='SubInfo$satt->subattribute_id'>"
                                . "<tr><td>$des:</td><td>$satt->description</td></tr>"
                                . "<tr><td>$des 1:</td><td>$satt->description1</td></tr>"
                                . "<tr><td>$des 2:</td><td>$satt->description2</td></tr>"
                                . "<tr><td>$des 3:</td><td>$satt->description3</td></tr>"
                                . "<tr><td>$des 4:</td><td>$satt->description4</td></tr>"
                                . "<tr><td>$des 5:</td><td>$satt->description5</td></tr>"
                                . "</table></td></tr>");
        }
                            $subattlabel = get_string('sub-attribute-label', 'local_gas');
                            $subattdeslabel = get_string('sub-attribute-Des-label', 'local_gas');
                            $subattdesofvaluelabel = get_string('sub-attribute-Des-ofValue_label', 'local_gas');
                            $addnewsubatt = get_string('add_new_sub_att', 'local_gas');
                            echo("<tr><td colspan='2'><div>
                                                <button onclick='ShowAddSubAtt($att->attribute_id);'>$addnewsubatt</button><br/>
                                                    <br/>
                                                <form action='attributemanagement.php' method='post'>
                                                    <fieldset>
                                                        <div style='display:none;' id='AddSubAttAdd$att->attribute_id'>
                                                        <label>$subattlabel($en): <input type='text' name='sattEnName' required/>
                                                            </label>
                                                        <label>$subattlabel($fr): <input type='text' name='sattFrName' required/>
                                                            </label>
                                                        <label>$subattdeslabel ($en): <input type='text' name='sattEnDes'
                                                            required/></label>
                                                        <label>$subattdeslabel ($fr): <input type='text' name='sattFrDes'
                                                            required/></label>
                                                        <label>$subattdesofvaluelabel 1 ($en): <input type='text'
                                                            name='sattEnDes1' required/></label>
                                                        <label>$subattdesofvaluelabel 1 ($fr): <input type='text'
                                                            name='sattFrDes1' required/></label>
                                                        <label>$subattdesofvaluelabel 2 ($en): <input type='text'
                                                            name='sattEnDes2' required/></label>
                                                        <label>$subattdesofvaluelabel 2 ($fr): <input type='text'
                                                            name='sattFrDes2' required/></label>
                                                        <label>$subattdesofvaluelabel 3 ($en): <input type='text'
                                                            name='sattEnDes3' required/></label>
                                                        <label>$subattdesofvaluelabel 3 ($fr): <input type='text'
                                                            name='sattFrDes3' required/></label>
                                                        <label>$subattdesofvaluelabel 4 ($en): <input type='text'
                                                            name='sattEnDes4' required/></label>
                                                        <label>$subattdesofvaluelabel 4 ($fr): <input type='text'
                                                            name='sattFrDes4' required/></label>
                                                        <label>$subattdesofvaluelabel 5 ($en): <input type='text'
                                                            name='sattEnDes5' required/></label>
                                                        <label>$subattdesofvaluelabel 5 ($fr): <input type='text'
                                                            name='sattFrDes5' required/></label>
                                                        <input type='submit' />
                                                        <input type='hidden' name='action' value='addSubAtt' >
                                                        <input type='hidden' name='attid' value='$att->attribute_id' >
                                                        </div>
                                                    </fieldset>
                                                </form>
                                            </div>");
                            echo("</td></tr></table></td></tr>");
    }
                        ?>
                    </tr>
                </table>
                <form  action='attributemanagement.php' method='post'>
                    <fieldset>
                        <legend><?php
                            $attlabel = get_string('attribute-label', 'local_gas');
                            $addnewatt = get_string('add_new_att', 'local_gas');
                            echo($addnewatt . ":");
                            ?></legend>
                        <label><?php echo($attlabel . " ($en):") ?><input type='text' name='attEnName' required/></label><br/>
                        <label><?php echo($attlabel . " ($fr):") ?><input type='text' name='attFrName' required/></label><br/>
                        <input type='submit' />
                        <input type='hidden' name='action' value='addAtt' >
                    </fieldset>
                </form>
                <br/>
                <form  action='attributemanagement.php' method='post'>
                    <fieldset>
                        <legend>Add all attributes:</legend>
                        <textarea name="text" type="text"></textarea>
                        <input type='submit' />
                        <input type='hidden' name='action' value='addAllAtt' >
                    </fieldset>
                </form>

                <br/>
                <form  action='attributemanagement.php' method='post'>
                    <fieldset>
                        <legend>Manage Semesters:</legend>
                        <textarea name="text" type="text"></textarea>
                        <input type='submit' />
                        <input type='hidden' name='action' value='addsems' >
                    </fieldset>
                </form>
                <form  action='attributemanagement.php' method='post'>
                    <fieldset>
                        <input type='submit' value="Delete all semester description" />
                        <input type='hidden' name='action' value='deletesems' >
                    </fieldset>
                </form>

                <br/>
                <form  action='attributemanagement.php' method='post'>
                    <fieldset>
                        <legend>Manage cohorts(active term ids):</legend>
                        <label>Cohorts: <input name="cohorts" type="text"/></label>
                        <input type='submit' />
                        <input type='hidden' name='action' value='cohorts' >
                    </fieldset>
                </form>
                <form  action='attributemanagement.php' method='post'>
                    <fieldset>
                        <input type='submit' value="Delete all active terms" />
                        <input type='hidden' name='action' value='deletecohorts' >
                    </fieldset>
                </form>

                <br/>
                <form  action='attributemanagement.php' method='post'>
                    <fieldset>
                        <legend>Getting data:</legend>
                        <label>table: <input name="table" type="text"/></label>
                        <input type='submit' />
                        <input type='hidden' name='action' value='getdata' >
                        <?php
    if ($data != null) {
                            $result = "";
        foreach ($data as $sub) {
            foreach ($sub as $rec) {
                                    $result .= $rec . ",";
            }
                                $result .= "\n";
        }
                            echo("<textarea type='text'>" . $result . "</textarea>");
    }
                        ?>
                    </fieldset>
                </form>
            </div>
        </div>
    </div>
    <?php
    echo $OUTPUT->footer();
};

$displaypage();
