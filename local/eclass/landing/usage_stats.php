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

define("MOODLE_INTERNAL", true);
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');

require_login();
require_capability('moodle/site:config', context_system::instance());

global $DB;

function is_termcode_valid($termcode) {
    if (!preg_match("/^[0-9]{4}$/", $termcode)) {
        echo "Invlid termcode ($termcode): Must be a 4 digit number.<br />\n";
        return false;
    }
    return true;
}

function echo_enrolment_table($objectarray, $totalcourses) {
    if (empty($objectarray)) {
        echo "<b>ERROR: No enrolment data found.</b><br />\n";
        return;
    }
    if (!property_exists(reset($objectarray), 'numstudents') ||
        !property_exists(reset($objectarray), 'courses')) {
        echo "<b>ERROR: Can't parse enrolment info without 'numcourses' and 'courses' keys.</b><br />\n";
        return;
    }
    if ($totalcourses <= 0) {
        echo "<b>WARNING: Invalid value for total number of courses ($totalcourses).</b>";
        echo "Setting total courses to 1. Percentage values will be useless.</b><br />\n";
        $totalcourses = 1;
    }

    echo "<br />\n<table border=\"1\">\n\n  <tr>\n";
    echo "    <th>Number of Students Enrolled (n)</th>\n";
    echo "    <th>Number of UOFAB Courses with n Students</th>\n";
    echo "    <th>Percent of Total UOFAB Courses</th>\n";
    echo "    <th>Number of UOFAB Courses with Up To n Students</th>\n";
    echo "    <th>Cumulative Percent</th>\n";
    echo "  </tr>\n\n";

    $runningsum = 0;
    $sumformean = 0;
    foreach ($objectarray as $row) {
        echo "  <tr>";
        if (($row->numstudents == 15) || ($row->numstudents == 80)) {
            echo "<b>";
        }
        echo " <td>$row->numstudents</td>";
        echo " <td>$row->courses</td>";
        echo " <td>" . $row->courses / $totalcourses * 100 . "</td>";
        $runningsum += $row->courses;
        $sumformean += ($row->courses * $row->numstudents);
        echo " <td>$runningsum</td>";
        echo " <td>" . $runningsum / $totalcourses * 100 . "</td>";
        if (($row->numstudents == 15) || ($row->numstudents == 80)) {
            echo "</b>";
        }
        echo "  </tr>\n";
    }
    echo "</table>\n\n";
    echo "<small>(Total UOFAB courses includes courses with 0 students enrolled.\n";
    echo "UOFAB courses in non-credit categories are included in this table.)</small><br /><br />\n\n";

    $mean = $sumformean / $totalcourses;
    echo "Mean number of students enrolled per credit course: $mean<br />\n";

    $sumforvariance = 0;
    foreach ($objectarray as $row) {
        $difference = $row->numstudents - $mean;
        $sumforvariance += (($difference * $difference) * $row->courses);
    }
    $variance = $sumforvariance / $totalcourses;
    echo "Variance: $variance<br />\n";
}

function echo_records_table($objectarray) {
    if (empty($objectarray)) {
        echo "<b>ERROR: No data to print.</b><br />\n";
        return;
    }

    echo "<br />\n<table border=\"1\">\n <tr>";
    foreach (array_keys(get_object_vars(reset($objectarray))) as $key) {
        echo " <th>$key</th>";
    }
    echo " </tr>\n";

    foreach ($objectarray as $row) {
        echo " <tr>";
        foreach (array_keys(get_object_vars($row)) as $key) {
            echo " <td>{$row->$key}</td>";
        }
        echo " </tr>\n";
    }
    echo "</table><br />\n";
}

function plot_bar_graph($dataarray, $legendx, $legendy) {
    $margin = 20;
    $minwidth = 100;
    $minheight = 150;
    $width = max(array_keys($dataarray));
    $height = max($dataarray);
    if ($width < $minwidth) {
        $width = $minwidth;
    }
    if ($height < $minheight) {
        $height = $minheight;
    }

    echo "\n<svg height=\"" . ($height + 2 * $margin) .
              '" width="' . ($width + 2 * $margin) . "\">\n";
    echo '<g transform="scale(1,-1) translate(0,' . (-($height + 2 * $margin)) . ')">';
    // Axes.
    echo "  <g style=\"stroke:rgb(0,0,0);stroke-width:1\" >\n";
    // X.
    echo '    <line x1="' . (0 + $margin) . '" y1="' . (0 + $margin) .
                 '" x2="' . ($width + $margin + 1) . '" y2="' . (0 + $margin) .
                 "\" />\n";
    echo '    <line x1="' . (0 + $margin) . '" y1="' . (0 + $margin - 1) .
                 '" x2="' . ($width + $margin + 1) . '" y2="' . (0 + $margin - 1) .
                 "\" stroke-dasharray=\"1,1\" />\n";
    echo '    <line x1="' . (0 + $margin) . '" y1="' . (0 + $margin - 2) .
                 '" x2="' . ($width + $margin + 1) . '" y2="' . (0 + $margin - 2) .
                 "\" stroke-dasharray=\"1,9\" />\n";
    echo '    <line x1="' . (0 + $margin) . '" y1="' . (0 + $margin - 3) .
                 '" x2="' . ($width + $margin + 1) . '" y2="' . (0 + $margin - 3) .
                 "\" stroke-dasharray=\"1,99\" />\n";
    // Y.
    echo '    <line x1="' . (-1 + $margin) . '" y1="' . (0 + $margin) .
                 '" x2="' . (-1 + $margin) . '" y2="' . ($height + $margin + 1) .
                 "\" />\n";
    echo '    <line x1="' . (-2 + $margin) . '" y1="' . (0 + $margin) .
                 '" x2="' . (-2 + $margin) . '" y2="' . ($height + $margin + 1) .
                 "\" stroke-dasharray=\"1,1\" />\n";
    echo '    <line x1="' . (-3 + $margin) . '" y1="' . (0 + $margin) .
                 '" x2="' . (-3 + $margin) . '" y2="' . ($height + $margin + 1) .
                 "\" stroke-dasharray=\"1,9\" />\n";
    echo '    <line x1="' . (-4 + $margin) . '" y1="' . (0 + $margin) .
                 '" x2="' . (-4 + $margin) . '" y2="' . ($height + $margin + 1) .
                 "\" stroke-dasharray=\"1,99\" />\n";
    echo "  </g>\n";
    // Data.
    echo "  <g style=\"stroke:rgb(255,0,0);stroke-width:1\">\n";
    for ($x = 0; $x <= $width; $x++) {
        if (isset($dataarray[$x])) {
            echo '    <line x1="' . ($x + $margin) . '" y1="' . (0 + $margin) .
                         '" x2="' . ($x + $margin) . '" y2="' . ($dataarray[$x] + $margin) .
                         "\" />\n";
        }
    }
    echo "  </g>\n";
    echo '</g>';
    // Axis labels.
    echo '  <text x="' . $margin . '" y="' . ($height + .5 * $margin) . '"' .
                ' font-size="15px" transform="rotate(270 ' .
                 ($margin) . ',' . ($height + $margin) . ')" >' .
                 $legendy . "</text>\n";
    echo '  <text x="' . $margin . '" y="' . ($height + 1.9 * $margin) . '" font-size="15px" >' . $legendx . "</text>\n";
    echo "  Sorry, your browser does not support inline SVG.\n</svg>\n\n";
}

try {
    $termparam = optional_param('term', null, PARAM_SEQUENCE);
    if (empty($termparam)) {
        echo "Displaying data for <tt>all terms</tt>.  Override with eg. ?term=1450,1460<br />\n";
    } else {
        $termcodes = str_getcsv($termparam);
        $termcodes = array_filter($termcodes, 'is_termcode_valid');
        echo 'Displaying data for <tt>term(s): ' . implode(', ', $termcodes) . "</tt>.<br />\n";
    }

    $verbose = optional_param('verbose', null, PARAM_INT);

    echo '<hr />';
    echo 'Data generated at <b>' . date('Y-m-d G:i:s T') .
    '</b> on server <b>' . $_SERVER['SERVER_NAME'] . "</b>.<br /><br />\n\n";

    // Courses filtered by selected terms.
    if (isset($termcodes)) {
        $sqlcoursesinterms = "(SELECT DISTINCT e.courseid FROM {enrol} e, {cohort} co " .
                "WHERE e.customint1 = co.id AND (co.idnumber LIKE '" .
                implode(".%' OR co.idnumber LIKE '", $termcodes) . ".%'))";
        $sqluofabcoursesinterms = "(SELECT DISTINCT e.courseid " .
                "FROM {enrol} e, {cohort} co, {course} c " .
                "WHERE c.id = e.courseid AND c.idnumber LIKE 'UOFAB%'" .
                " AND e.customint1 = co.id AND (co.idnumber LIKE '" .
                implode(".%' OR co.idnumber LIKE '", $termcodes) . ".%'))";
    } else {
        $sqlcoursesinterms = "(SELECT DISTINCT id FROM {course} WHERE id<>1)"; // Ignore site-level course.
        $sqluofabcoursesinterms = "(SELECT DISTINCT id FROM {course} WHERE idnumber LIKE 'UOFAB%')";
    }

    // Course counts.
    $sqltotalcourses = "SELECT COUNT(*) FROM {course} WHERE id IN " . $sqlcoursesinterms . ";";
    $totalcoursesinselectedtermsrecord = $DB->get_record_sql($sqltotalcourses);
    $sqltotaluofabcourses = "SELECT COUNT(*) FROM {course} WHERE id IN " . $sqluofabcoursesinterms . ";";
    $uofabcoursesinselectedtermsrecord = $DB->get_record_sql($sqltotaluofabcourses);
    if (isset($termcodes)) {
        echo "Total courses in <tt>term(s) " . implode(', ', $termcodes) .
        "</tt>: <b>{$totalcoursesinselectedtermsrecord->count}</b>.<br />\n";
        echo "Course idnumbers beginning with UOFAB in <tt>term(s) " . implode(', ', $termcodes) .
        "</tt>: <b>{$uofabcoursesinselectedtermsrecord->count}</b>.<br />\n";
    } else {
        echo "Total courses in <tt>all terms</tt>: <b>{$totalcoursesinselectedtermsrecord->count}</b><br />\n";
        echo "Course idnumbers beginning with UOFAB in <tt>all terms</tt>: " .
                "<b>{$uofabcoursesinselectedtermsrecord->count}</b><br />\n";
        echo "<small>(UOFAB courses included in non-credit categories are included in this total.)</small><br />\n";
    }

    // Student enrolments.
    $rolerecord = $DB->get_record('role', array('id' => '5'), 'name', MUST_EXIST);
    echo "\n<br />Enrolment statistics for <b>$rolerecord->name role (roleid=5)</b>.<br /><br />\n\n";

    $sqluofabcourseenrolments = "SELECT s.count numstudents, COUNT(DISTINCT s.courseid) courses " .
                               "FROM (SELECT e.courseid, COUNT(ue.userid) " .
                                     "FROM {enrol} e, {user_enrolments} ue" .
                                     " WHERE ue.enrolid=e.id AND e.roleid=5" .
                                     " AND e.courseid IN $sqluofabcoursesinterms" .
                                     " GROUP BY e.courseid ORDER BY count) s " .
                               "GROUP BY s.count;";

    $uofabcourseenrolmentsrecords = $DB->get_records_sql($sqluofabcourseenrolments, null);
    echo_enrolment_table($uofabcourseenrolmentsrecords, $uofabcoursesinselectedtermsrecord->count);

    if (count($uofabcourseenrolmentsrecords)) {
        // Draw a chart of the enrolment data.
        $maxnumcourses = max(array_map("get_courses_from_object", $uofabcourseenrolmentsrecords));
        $maxnumstudents = max(array_map("get_numstudents_from_object", $uofabcourseenrolmentsrecords));
        $numcourseswith = array();
        for ($x = 0; $x <= $maxnumstudents; $x++) {
            if (isset($uofabcourseenrolmentsrecords[$x])) {
                $numcourseswith[$uofabcourseenrolmentsrecords[$x]->numstudents] =
                                $uofabcourseenrolmentsrecords[$x]->courses;
            }
        }
        plot_bar_graph($numcourseswith, "Student Enrolment", "Number of Credit Courses");
    }

    // Find category context path of credit course category.
    $sqlcontextpath = "SELECT cc.id cat_id, cx.path, cx.depth FROM {context} cx JOIN {course_categories} cc" .
            " ON cx.instanceid=cc.id WHERE cx.contextlevel=40 AND cc.description='UOFAB';";

    $contextpathrecord = $DB->get_record_sql($sqlcontextpath);
    if ($contextpathrecord == false || empty($contextpathrecord->path)) {
        echo "<i><b>ERROR: UofA Credit Courses Category (with description 'UOFAB') not found.</b></i><br />\n";
        return;
    }
    $contextpath = $contextpathrecord->path;
    $contextdepth = $contextpathrecord->depth;

    // Courses by faculty.
    echo "\n\n<p /><b>Number of credit courses by Faculty</b><br />\n";
    echo "<small>(Categories 1/+ level(s) below UOFAB category.\n";
    echo "Table does not include courses above faculty categories.)</small><br />\n";

    $sqlcredcoursesbyfaculty = "SELECT faculty, name, num_courses," .
        " ROUND(100*num_courses/SUM(num_courses) OVER (), 2) AS percent_of_total FROM (" .
            "SELECT cc.description faculty, cc.name, count(*) num_courses " .
            "FROM {context} cat_cx, {context} cour_cx, {course_categories} cc" .
            " WHERE cat_cx.contextlevel=40 AND cat_cx.path LIKE '$contextpath/%'" .
            " AND cat_cx.depth=($contextdepth + 1)" .
            " AND cat_cx.instanceid=cc.id AND cour_cx.contextlevel=50" .
            " AND cour_cx.path LIKE '$contextpath/' || cat_cx.id || '/%'" .
            " AND cour_cx.instanceid IN $sqluofabcoursesinterms" .
            " GROUP BY cc.description, cc.name ORDER BY num_courses DESC" .
        ") AS foo;";

    $credcoursesbyfacultyrecords = $DB->get_records_sql($sqlcredcoursesbyfaculty, null);
    echo_records_table($credcoursesbyfacultyrecords);

    // Courses by dept.
    echo "\n\n<p /><b>Number of credit courses by Department</b><br />\n";
    echo "<small>(Categories 2/+ levels below UOFAB category.\n";
    echo "Table does not include courses above department categories.)</small><br />\n";

    $sqlcredcoursesbydept = "SELECT dept, name, num_courses," .
        " ROUND(100*num_courses/SUM(num_courses) OVER (), 2) AS percent_of_total FROM (" .
            "SELECT cc.description dept, cc.name, count(*) num_courses " .
            "FROM {context} cat_cx, {context} cour_cx, {course_categories} cc" .
            " WHERE cat_cx.contextlevel=40 AND cat_cx.path LIKE '$contextpath/%'" .
            " AND cat_cx.depth=($contextdepth + 2)" .
            " AND cat_cx.instanceid=cc.id AND cour_cx.contextlevel=50" .
            " AND cour_cx.path LIKE '$contextpath/%/' || cat_cx.id || '/%'" .
            " AND cour_cx.instanceid IN $sqluofabcoursesinterms" .
            " GROUP BY cc.description, cc.name ORDER BY dept" .
        ") AS foo;";

    $credcoursesbydeptrecords = $DB->get_records_sql($sqlcredcoursesbydept, null);
    echo_records_table($credcoursesbydeptrecords);

} catch (Exception $e) {
    $fail = <<<fail
<pre>
                                    ','. '. ; : ,','
                                      '..'FAIL,..'
                                         ';.' ,'
                                          ;;
                                          ;'
                            :._   _.------------.___
                    __      :__:-'                  '--.
             __   ,' .'    .'             ______________'.
           /__ '.-  _\___.'          0  .' .'  .'  _.-_.'
              '._                     .-': .' _.' _.'_.'
                 '----'._____________.'_'._:_:_.-'--'
</pre>
fail;
    echo $fail.'<br>'.$e;
    return;
}

function get_courses_from_object($o) {
    return $o->courses;
}

function get_numstudents_from_object($o) {
    return $o->numstudents;
}

function echo_name_query($title, $query) {
    echo "<br /><b>$title:</b> <br /><code>" . str_replace('}', '', str_replace('{', 'mdl_', $query)) .
            "</code><br />\n";
}

if (!empty($verbose)) {
    echo "\n<br /><br /><hr /><br />\n\n";
    echo "Queries generated:<br />\n\n";

    echo_name_query("Total Course subquery", $sqlcoursesinterms);
    echo_name_query("UOFAB Course subquery", $sqluofabcoursesinterms);
    echo_name_query("Count of Total Courses query", $sqltotalcourses);
    echo_name_query("Count of UOFAB Courses query", $sqltotaluofabcourses);
    echo "<br />\n";

    echo_name_query("UOFAB course enrolments", $sqluofabcourseenrolments);
    echo_name_query("Credit Course Category Context Path", $sqlcontextpath);

    echo "<br />\n";
    echo "<b>Credit course category:</b>\n<pre>";
    var_export($contextpathrecord);
    echo "</pre>\n\n";

    echo_name_query("Courses by Faculty", $sqlcredcoursesbyfaculty);
    echo_name_query("Courses by Department", $sqlcredcoursesbydept);

} else {
    $suffix = ((strpos($_SERVER['REQUEST_URI'], '?') == false) ? '?' : '&') . 'verbose=1';
    echo "<br />To see queries used, suffix url with " .
            '<a href="' . $_SERVER['REQUEST_URI'] . $suffix . '">' .
            "<b>$suffix</b></a><br />\n";
}
