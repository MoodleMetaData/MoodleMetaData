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

function list_module_names() {
    global $DB;
    $modulerecords = $DB->get_records('modules', null, 'name ASC');
    $modulenames = array_map(create_function('$o', 'return $o->name;'), $modulerecords);
    echo "<br />\nAvailable modules:<br />\n";
    foreach ($modulenames as $mod) {
         echo '<a href="' . $_SERVER['REQUEST_URI'] .
              ((strpos($_SERVER['REQUEST_URI'], '?') == false) ? '?' : '&') .
              'mod=' . $mod . '">' . $mod . "<br />\n";
    }
    echo "<br /><br />\n";
}

function replace_cat_in_url($cat, $url) {
    if (strpos($url, '?') == false) {
        $newurl = $url . "?cat=$cat";
    } else {
        if (strpos($url, 'cat=') == false) {
            $newurl = $url . "&cat=$cat";
        } else {
            $newurl = preg_replace('/cat=.*&/', '', $url);
            $newurl = preg_replace('/cat=.*$/', '', $newurl);
            $newurl = $newurl . "&cat=$cat";
            $newurl = str_replace('?&', '?', $newurl);
            $newurl = str_replace('&&', '&', $newurl);
        }
    }
    return $newurl;
}

function echo_category_table($objectarray) {
    if (empty($objectarray)) {
        return;
    }
    echo '<br /><table border="1"><tr>';
    foreach (array_keys(get_object_vars(reset($objectarray))) as $key) {
        echo "<th>$key</th>";
    }
    echo "</tr>\n";
    foreach ($objectarray as $row) {
        echo "<tr>";
        foreach (array_keys(get_object_vars($row)) as $key) {
            if (isset($row->id)) {
                echo '<td><a href="' . replace_cat_in_url($row->id, $_SERVER['REQUEST_URI']) .
                     '">' . $row->$key . '</a></td>';
            } else {
                echo "<td>{$row->$key}</td>";
            }
        }
        echo "</tr>\n";
    }
    echo "</table><br />\n";
}

try {
    if (empty($_GET['mod'])) {
        echo "Require a module type (activity or resource) to generate data for, eg. ?mod=quiz<br />\n";
        list_module_names();
        exit();
    }
    $modulename = $_GET['mod'];
    $modulerecord = $DB->get_record('modules', array('name' => $modulename));
    if ($modulerecord == false) {
        echo "Module '$modulename' not found.<br />\n";
        list_module_names();
        exit();
    }
    $moduleid = $modulerecord->id;
    echo "Displaying data for module: <b>$modulename</b> (moduleid=<b>$moduleid</b>).<br />\n";

    if (empty($_GET['term'])) {
        echo "Displaying data for <tt>all terms</tt>.  Override with eg. &term=1450,1460<br />\n";
    } else {
        $termcodes = explode(',', $_GET['term']);
        $termcodes = array_filter($termcodes, 'is_termcode_valid');
        echo 'Displaying data for <tt>term(s): ' . implode(', ', $termcodes) . "</tt>.<br />\n";
    }

    if (!empty($_GET['path'])) {
        $categoryidunknown = true;
        // The path parameter is an alterntive to specifying the category, for manual testing.
        $catcontextpaths = explode(',', $_GET['path']);
        $categories = array("specified by path");
    } else {
        $categoryidunknown = false;
        if (empty($_GET['cat'])) {
            echo "Displaying data for <i>all credit categories</i>.  Override with eg. &cat=114,184  " .
                 '(List categories with <a href="' . replace_cat_in_url(-1, $_SERVER['REQUEST_URI']) .
                 "\">&cat=-1</a> )<br />\n";
            $sqlcredcoursescategory = "SELECT id FROM {course_categories} WHERE description='UOFAB';";
            $categoryidcredcoursesrecord = $DB->get_record_sql($sqlcredcoursescategory);
            if ($categoryidcredcoursesrecord == false || empty($categoryidcredcoursesrecord->id)) {
                echo "<i><b>WARNING: UofA Credit Courses Category (with description 'UOFAB') not found.</b></i><br />\n";
                $categories = array(31);  // Credit courses category on production server.
            } else {
                $categories = array($categoryidcredcoursesrecord->id);
            }
        } else {
            $requestedcats = $_GET['cat'];
            if (preg_match('/[^\d-]/', $requestedcats)) {
                if (!preg_match("/'/", $requestedcats)) { // If the names are not quoted, add quotes.
                    $requestedcats = "'" . implode("','", explode(',', $requestedcats)) . "'";
                }
                echo "Non-numerals found in 'cat' parameter.  Assuming it's a list of names, not ids.<br />\n";
                $sqlrequestedcats = "SELECT id FROM {course_categories} WHERE name IN ($requestedcats);";
                $requestedcatsrecords = $DB->get_records_sql($sqlrequestedcats);
                if ($requestedcatsrecords == false) {
                    echo "<i><b>ERROR: Could not find category ids corresponding to name(s) $requestedcats.</b></i><br />\n";
                    $categories = array(31);  // Credit courses category on production server.
                } else {
                    $categories = array_map(create_function('$o', 'return $o->id;'), $requestedcatsrecords);
                }
            } else {
                $categories = explode(',', $requestedcats);
            }
        }
        echo "Displaying data for <i>category/ies: " . implode(', ', $categories) .
             "</i>.  (Courses in subcategories included where noted.)<br />\n";
        $sqlcatcontextpaths = "SELECT path FROM {context} " .
                                   "WHERE contextlevel=" . CONTEXT_COURSECAT .
                                   " AND instanceid IN (" . implode(', ', $categories) . ");";
        $catcontextpathsrecords = $DB->get_records_sql($sqlcatcontextpaths);
        if ($catcontextpathsrecords == false || $catcontextpathsrecords == 0) {
            echo "<i><b>ERROR: Could not find any context paths for categories. Check your 'cat' parameter.</b></i><br />\n";
            echo '<br />Available Categories:';
            $sqlcatlist = "SELECT id, description, name, idnumber FROM {course_categories} ORDER BY description;";
            $categoriesrecords = $DB->get_records_sql($sqlcatlist);
            echo_category_table($categoriesrecords);
            if (!empty($_GET['verbose'])) {
                echo "Category Context Paths query: <br /><code>$sqlcatcontextpaths</code><br />\n";
                echo "Category List query: <br /><code>$sqlcatlist</code><br />\n";
            }
            exit;
        } else {
            echo '<br />Selected Categories:';
            $sqlcatlist = "SELECT id, description, name, idnumber FROM {course_categories} " .
                    "WHERE id IN (" . implode(',', $categories) . ") ORDER BY description;";
            $categoriesrecords = $DB->get_records_sql($sqlcatlist);
            echo_category_table($categoriesrecords);
        }
        if (count($catcontextpathsrecords) != count($categories)) {
            echo "<i><b>WARNING: Not all categories returned context paths.  Check your 'cat' parameter.  " .
                 '(List categories with <a href="' . replace_cat_in_url(-1, $_SERVER['REQUEST_URI']) .
                 '">&cat=-1</a> )</b></i><br />' . "\n";
        }
        $catcontextpaths = array_map(create_function('$o', 'return $o->path;'),
                                            $catcontextpathsrecords);
    }
    echo 'Category context paths: <i>' . implode(', ', $catcontextpaths) . "</i>.<br .>\n";

    echo '<hr />';
    echo 'Data generated at <b>' . date('Y-m-d G:i:s T') .
         '</b> on server <b>' . $_SERVER['SERVER_NAME'] . "</b>.<br /><br />\n";

    // Common queries for all module types.
    // Courses filtered by selected terms.
    if (isset($termcodes)) {
        $sqlcoursesinterms = "(SELECT DISTINCT e.courseid FROM {enrol} e, {cohort} co " .
                             "WHERE e.customint1 = co.id AND (co.idnumber LIKE '" .
                                 implode(".%' OR co.idnumber LIKE '", $termcodes) . ".%'))";
        $sqlcredcoursesinterms = "(SELECT DISTINCT e.courseid " .
                                   "FROM {enrol} e, {cohort} co, {course} c " .
                                   "WHERE c.id = e.courseid AND c.idnumber LIKE 'UOFAB%'" .
                                   " AND e.customint1 = co.id AND (co.idnumber LIKE '" .
                                       implode(".%' OR co.idnumber LIKE '", $termcodes) . ".%'))";
    } else {
        $sqlcoursesinterms = "(SELECT DISTINCT id FROM {course} WHERE id<>1)"; // Ignore site-level course.
        $sqlcredcoursesinterms = "(SELECT DISTINCT id FROM {course} WHERE idnumber LIKE 'UOFAB%')";
    }

    // Course counts.
    $sqltotalcourses = "SELECT COUNT(*) FROM {course} WHERE id IN " . $sqlcoursesinterms . ";";
    $totalcoursesinselectedtermsrecord = $DB->get_record_sql($sqltotalcourses);
    $sqlcredcourses = "SELECT COUNT(*) FROM {course} WHERE id IN " . $sqlcredcoursesinterms . ";";
    $credcoursesinselectedtermsrecord = $DB->get_record_sql($sqlcredcourses);
    if (isset($termcodes)) {
        echo "Total courses in <tt>term(s) " . implode(', ', $termcodes) .
             "</tt>: <b>{$totalcoursesinselectedtermsrecord->count}</b>.<br />\n";
        echo "Credit courses in <tt>term(s) " . implode(', ', $termcodes) .
             "</tt>: <b>{$credcoursesinselectedtermsrecord->count}</b>.<br />\n";
    } else {
        echo "Total courses in <tt>all terms</tt>: <b>{$totalcoursesinselectedtermsrecord->count}</b><br />\n";
        echo "Credit courses in <tt>all terms</tt>: <b>{$credcoursesinselectedtermsrecord->count}</b><br />\n";
    }

    // Courses filtered by terms and categories.
    $sqlnumcoursesincatandsub = "SELECT COUNT(*) " .
        "FROM {context} " .
        "WHERE contextlevel=" . CONTEXT_COURSE .
        " AND path LIKE '" . implode("/%' OR path LIKE '", $catcontextpaths) . "/%'" .
        " AND instanceid in $sqlcoursesinterms;";
    $numcoursesincatrecord = $DB->get_record_sql($sqlnumcoursesincatandsub);
    echo "Number of courses" .
             " in <i>category/ies " . implode(', ', $categories) . " and subcategories</i>," .
             " for selected term(s): " .
             "<b>{$numcoursesincatrecord->count}</b><br />\n";
    $sqlnumcredcoursesincatandsub = "SELECT COUNT(*) " .
        "FROM {context} " .
        "WHERE contextlevel=" . CONTEXT_COURSE .
        " AND path LIKE '" . implode("/%' OR path LIKE '", $catcontextpaths) . "/%'" .
        " AND instanceid in $sqlcredcoursesinterms;";
    $numcredcoursesincatrecord = $DB->get_record_sql($sqlnumcredcoursesincatandsub);
    echo "Number of credit courses" .
             " in <i>category/ies " . implode(', ', $categories) . " and subcategories</i>," .
             " for selected term(s): " .
             "<b>{$numcredcoursesincatrecord->count}</b><br />\n";

    if (!$categoryidunknown) {
        if (isset($termcodes)) {
            $sqlnumcredcoursesincatonly =
                "select count (distinct crs.idnumber) " .
                "from {course} crs, {course_modules} cms " .
                "where crs.id=cms.course" .
                " and crs.id in (select e.courseid " .
                                "from {enrol} e, {cohort} co " .
                                "where e.customint1 = co.id" .
                                " AND (co.idnumber like '" . implode(".%' OR co.idnumber LIKE '", $termcodes) . ".%'))" .
                " and crs.idnumber like 'UOFAB%'" .
                " and crs.category in (" . implode(', ', $categories) . ");";
        } else {
            $sqlnumcredcoursesincatonly =
                "select count (distinct crs.idnumber) " .
                "from {course} crs, {course_modules} cms " .
                "where crs.id=cms.course" .
                " and crs.idnumber like 'UOFAB%'" .
                " and crs.category in (" . implode(', ', $categories) . ");";
        }
        $numcredcoursesincatonlyrecord = $DB->get_record_sql($sqlnumcredcoursesincatonly);
        echo "Number of courses" .
                 " in <i>category/ies " . implode(', ', $categories) . "</i> without subcategories," .
                 " for selected term(s): " .
                 "<b>{$numcredcoursesincatonlyrecord->count}</b><br />\n";

        if (isset($termcodes)) {
            $sqlnumcredcourses1belowcat =
                "select count(distinct crs.idnumber) " .
                "from {course} crs, {course_modules} cms " .
                "where crs.id=cms.course" .
                " and crs.id in (select e.courseid " .
                                "from {enrol} e, {cohort} co " .
                                "where e.customint1 = co.id" .
                                " AND (co.idnumber like '" . implode(".%' OR co.idnumber LIKE '", $termcodes) . ".%'))" .
                " and crs.category in (select id from {course_categories} " .
                                      "where parent in (" . implode(', ', $categories) . "))" .
                " and crs.idnumber like 'UOFAB%';";
        } else {
            $sqlnumcredcourses1belowcat =
                "select count(distinct crs.idnumber) " .
                "from {course} crs, {course_modules} cms " .
                "where crs.id=cms.course" .
                " and crs.category in (select id from {course_categories} " .
                                      "where parent in (" . implode(', ', $categories) . "))" .
                " and crs.idnumber like 'UOFAB%';";
        }
        $numcredcourses1levelbelowcatrecord = $DB->get_record_sql($sqlnumcredcourses1belowcat);
        echo "Number of courses" .
                 " <i>1 level below category/ies " . implode(', ', $categories) . "</i>," .
                 " for selected term(s): " .
                 "<b>{$numcredcourses1levelbelowcatrecord->count}</b><br />\n";
    }

    // Courses filtered by terms, categories, and module.
    echo "<p>\n";
    $sqlnumcourseswithmodintree = "SELECT COUNT(DISTINCT cm.course) " .
        "FROM {context} cx, {course_modules} cm " .
        "WHERE cx.contextlevel=" . CONTEXT_COURSE .
        " AND cx.path LIKE '" . implode("/%' OR cx.path LIKE '", $catcontextpaths) . "/%'" .
        " AND cx.instanceid in $sqlcoursesinterms" .
        " AND cx.instanceid = cm.course" .
        " AND cm.module = $moduleid;";
    $numcourseswithmodintreerecord = $DB->get_record_sql($sqlnumcourseswithmodintree);
    echo "Number of courses" .
            " containing module <b>$modulename</b> (moduleid=<b>$moduleid</b>), " .
            " in <i>category/ies " . implode(', ', $categories) . " and all subcategories</i>," .
            " for selected term(s): " .
            "<b>{$numcourseswithmodintreerecord->count}</b><br />\n";
    $sqlnumcredcourseswithmodintree = "SELECT COUNT(DISTINCT cm.course) " .
            "FROM {context} cx, {course_modules} cm " .
            "WHERE cx.contextlevel=" . CONTEXT_COURSE .
            " AND cx.path LIKE '" . implode("/%' OR cx.path LIKE '", $catcontextpaths) . "/%'" .
            " AND cx.instanceid in $sqlcredcoursesinterms" .
            " AND cx.instanceid = cm.course" .
            " AND cm.module = $moduleid;";
    $numcredcourseswithmodintreerecord = $DB->get_record_sql($sqlnumcredcourseswithmodintree);
    echo "Number of credit courses" .
            " containing module <b>$modulename</b> (moduleid=<b>$moduleid</b>)," .
            " in <i>category/ies " . implode(', ', $categories) . " and all subcategories</i>," .
            " for selected term(s): " .
            "<b>{$numcredcourseswithmodintreerecord->count}</b><br />\n";

    if (!$categoryidunknown) {
        if (isset($termcodes)) {
            $sqlnumcourseswithmodincat = "select count(distinct crs.idnumber) " .
                "from {course} crs, {course_modules} cms " .
                "where crs.id=cms.course and cms.module=$moduleid" .
                " and crs.id in (select e.courseid from {enrol} e, {cohort} co " .
                                "where e.customint1 = co.id" .
                                " AND (co.idnumber like '" . implode(".%' OR co.idnumber LIKE '", $termcodes) . ".%'))" .
                " and crs.category in (" . implode(', ', $categories) . ")" .
                " and crs.idnumber like 'UOFAB%';";
        } else {
            $sqlnumcourseswithmodincat = "select count(distinct crs.idnumber) " .
                "from {course} crs, {course_modules} cms " .
                "where crs.id=cms.course and cms.module=$moduleid" .
                " and crs.category in (" . implode(', ', $categories) . ")" .
                " and crs.idnumber like 'UOFAB%';";
        }
        $numcourseswithmodincatrecord = $DB->get_record_sql($sqlnumcourseswithmodincat);
        echo "Number of all credit courses" .
                " with module <b>$modulename</b> (moduleid=<b>$moduleid</b>)," .
                " in <i>category/ies " . implode(', ', $categories) . "</i> (subcategories not included)," .
                " for selected term(s): " .
                "<b>{$numcourseswithmodincatrecord->count}</b><br />\n";

        if (isset($termcodes)) {
            $sqlnumcourseswithmodbelowcat = "select count(distinct crs.idnumber) " .
                "from {course} crs, {course_modules} cms " .
                "where crs.id=cms.course and cms.module=$moduleid" .
                " and crs.id in (select e.courseid from {enrol} e, {cohort} co " .
                                "where e.customint1 = co.id" .
                                " AND (co.idnumber like '" . implode(".%' OR co.idnumber LIKE '", $termcodes) . ".%'))" .
                " and crs.category in (select id from {course_categories} " .
                                      "where parent in (" . implode(', ', $categories) . "))" .
                " and crs.idnumber like 'UOFAB%';";
        } else {
            $sqlnumcourseswithmodbelowcat = "select count(distinct crs.idnumber) " .
                "from {course} crs, {course_modules} cms " .
                "where crs.id=cms.course and cms.module=$moduleid" .
                " and crs.category in (select id from {course_categories} " .
                                      "where parent in (" . implode(', ', $categories) . "))" .
                " and crs.idnumber like 'UOFAB%';";
        }
        $numcourseswithmodbelowcatrecord = $DB->get_record_sql($sqlnumcourseswithmodbelowcat);
        echo "Number of all credit courses" .
                " with module <b>$modulename</b> (moduleid=<b>$moduleid</b>)," .
                " <i>1 level below category/ies " . implode(', ', $categories) . "</i>," .
                " for selected term(s): " .
                "<b>{$numcourseswithmodbelowcatrecord->count}</b><br />\n";
    }

    $sqlcredcourseidswithmodquery = str_replace('COUNT(DISTINCT cm.course)', 'DISTINCT cm.course',
                                                     $sqlnumcredcourseswithmodintree);
    $sqlcredcourseidswithmodsubquery = '(' . str_replace(';', ')',
                                                              $sqlcredcourseidswithmodquery);

    // Module instances.
    echo "<p>\n";
    $sqlnummodincoursesintree = "SELECT COUNT(*) " .
            "FROM {context} cx, {course_modules} cm " .
            "WHERE cx.contextlevel=" . CONTEXT_COURSE .
            " AND cx.path LIKE '" . implode("/%' OR cx.path LIKE '", $catcontextpaths) . "/%'" .
            " AND cx.instanceid in $sqlcoursesinterms" .
            " AND cx.instanceid = cm.course" .
            " AND cm.module = $moduleid;";
    $nummodincoursesintreerecord = $DB->get_record_sql($sqlnummodincoursesintree);
    echo "Number of instances" .
            " of module <b>$modulename</b> (moduleid=<b>$moduleid</b>)," .
            " in <i>category/ies " . implode(', ', $categories) . " and all subcategories</i>," .
            " for selected term(s) in credit & non-credit courses: " .
            "<b>{$nummodincoursesintreerecord->count}</b><br />\n";
    $sqlnummodincredcoursesintree = "SELECT COUNT(*) " .
            "FROM {context} cx, {course_modules} cm " .
            "WHERE cx.contextlevel=" . CONTEXT_COURSE .
            " AND cx.path LIKE '" . implode("/%' OR cx.path LIKE '", $catcontextpaths) . "/%'" .
            " AND cx.instanceid in $sqlcredcoursesinterms" .
            " AND cx.instanceid = cm.course" .
            " AND cm.module = $moduleid;";
    $nummodincredcoursesintreerecord = $DB->get_record_sql($sqlnummodincredcoursesintree);
    echo "Number of instances" .
            " of module <b>$modulename</b> (moduleid=<b>$moduleid</b>)," .
            " in <i>category/ies " . implode(', ', $categories) . " and all subcategories</i>," .
            " for selected term(s) in credit courses: " .
            "<b>{$nummodincredcoursesintreerecord->count}</b><br />\n";

    // Get additional information for specific module types.
    echo "<p>\n";
    switch($modulename) {
        case 'quiz':
            // For quizzes, number of unique attempts.
            $sqlnumquizattempts = "SELECT COUNT(*) FROM {quiz_attempts} qa, {quiz} q " .
                                  "WHERE qa.quiz=q.id" .
                                  " AND q.course IN $sqlcredcourseidswithmodsubquery;";
            $numquizattemptsrecord = $DB->get_record_sql($sqlnumquizattempts);
            if ($numquizattemptsrecord == false || empty($numquizattemptsrecord->count)) {
                echo "<b>0</b> quiz attempts found in specified credit courses.<br />\n";
            } else {
                echo "Number of quiz attempts in specified credit courses: <b>{$numquizattemptsrecord->count}</b><br />\n";
            }
            break;
        case 'forum':
            // For forums, number of topics, posts.
            $sqlnumforumtopics = "SELECT COUNT(*) FROM {forum_discussions} " .
                                 "WHERE course IN $sqlcredcourseidswithmodsubquery;";
            $numforumtopicsrecord = $DB->get_record_sql($sqlnumforumtopics);
            if ($numforumtopicsrecord == false || empty($numforumtopicsrecord->count)) {
                echo "<b>0</b> forum topics found in specified credit courses.<br />\n";
            } else {
                echo "Number of forum topics in specified credit courses: <b>{$numforumtopicsrecord->count}</b><br />\n";
            }
            $sqlforumdiscussionsubsetquery = str_replace('COUNT(*)', 'id AS discussionid',
                                                             $sqlnumforumtopics);
            $sqlforumdiscussionsubsetsubquery = '(' . str_replace(';', ')',
                                                                  $sqlforumdiscussionsubsetquery);
            $sqlnumforumposts = "SELECT COUNT(*) FROM {forum_posts} " .
                                "WHERE discussion IN $sqlforumdiscussionsubsetsubquery;";
            $numforumpostsrecord = $DB->get_record_sql($sqlnumforumposts);
            if ($numforumpostsrecord == false || empty($numforumpostsrecord->count)) {
                echo "<b>0</b> forum posts found in specified credit courses.<br />\n";
            } else {
                echo "Number of forum posts in specified credit courses: <b>{$numforumpostsrecord->count}</b><br />\n";
            }
            break;
        case 'assign':
            // For assignments, number of submissions.
            $sqlnumassignmentsubmissions = "SELECT COUNT(*) FROM {assign_submission} s, {assign} a " .
                                           "WHERE s.assignment = a.id " .
                                           "AND a.course IN $sqlcredcourseidswithmodsubquery;";
            $numassignmentsubmissionsrecord = $DB->get_record_sql($sqlnumassignmentsubmissions);
            if ($numassignmentsubmissionsrecord == false || empty($numassignmentsubmissionsrecord->count)) {
                echo "<b>0</b> assignment submissions found in specified credit courses.<br />\n";
            } else {
                echo "Number of assignment submissions in specified credit courses: <b>" .
                     $numassignmentsubmissionsrecord->count . "</b><br />\n";
            }
            break;
        default:
    }

} catch (Exception $e) {
    echo "<pre>FAIL!<br />\n" . $e;
    return;
}

if (!empty($_GET['verbose'])) {
    echo "<br /><br /><hr /><br />";
    echo "Queries generated:<br /><br />\n";
    if (isset($sqlcatcontextpaths)) {
        echo "Category Context Paths query: <br /><code>$sqlcatcontextpaths</code><br />\n";
    }
    echo "Total Course subquery: <br /><code>$sqlcoursesinterms</code><br />\n";
    echo "Credit Course subquery: <br /><code>$sqlcredcoursesinterms</code><br />\n";
    echo "Count of Total Courses query: <br /><code>$sqltotalcourses</code><br />\n";
    echo "Count of Credit Courses query: <br /><code>$sqlcredcourses</code><br />\n";
    echo "<br />\n";
    echo "Count of Courses in Category and Subcategories: <br /><code>$sqlnumcoursesincatandsub</code><br />\n";
    echo "Count of Credit Courses in Category and Subcategories: <br /><code>$sqlnumcredcoursesincatandsub</code><br />\n";
    if (isset($sqlnumcredcoursesincatonly) && isset($sqlnumcredcourses1belowcat)) {
        echo "Count of Credit Courses in Category Only: <br /><code>$sqlnumcredcoursesincatonly</code><br />\n";
        echo "Count of Credit Courses 1 level below Category: <br /><code>$sqlnumcredcourses1belowcat</code><br />\n";
    }
    echo "<br />\n";
    echo "Count of Courses with Modules in Category Tree query: <br /><code>$sqlnumcourseswithmodintree</code><br />\n";
    echo "Count of Credit Courses with Modules in Category Tree query: <br /><code>$sqlnumcredcourseswithmodintree</code><br />\n";
    if (isset($sqlnumcourseswithmodincat) && isset($sqlnumcourseswithmodbelowcat)) {
        echo "Count of Credit Courses with Modules in Category Only query: <br /><code>$sqlnumcourseswithmodincat</code><br />\n";
        echo "Count of Credit Courses with Modules 1 level below Category query: <br />" .
                "<code>$sqlnumcourseswithmodbelowcat</code><br />\n";
    }
    echo "<br />\n";
    echo "Credit Course IDs with Modules subquery: <br /><code>$sqlcredcourseidswithmodsubquery</code><br />\n";

    echo "<br />\n";
    echo "Count of Module Instances in courses in tree query: <br /><code>$sqlnummodincoursesintree</code><br />\n";
    echo "Count of Module Instances in credit courses in tree query: <br /><code>$sqlnummodincredcoursesintree</code><br />\n";

    echo "<br /><br />Additional Queries:<br /><br />\n";
    switch($modulename) {
        case 'quiz':
            echo "Count of Quiz Attempts query: <br /><code>$sqlnumquizattempts</code><br />\n";
            break;
        case 'forum':
            echo "Count of Forum Topics query: <br /><code>$sqlnumforumtopics</code><br />\n";
            echo "Count of Forum Posts query: <br /><code>$sqlnumforumposts</code><br />\n";
            echo "Forum Discussion IDs (in selected courses) subquery: " .
                    "<br /><code>$sqlforumdiscussionsubsetsubquery</code><br />\n";
            break;
        case 'assign':
            echo "Count of Assignment Submissions query: <br /><code>$sqlnumassignmentsubmissions</code><br />\n";
            break;
    }
    echo "<br />That's all I have to say about that.<br />\n";
} else {
    echo "<br />To see queries used, suffix url with " .
            '<a href="' . $_SERVER['REQUEST_URI'] .
            ((strpos($_SERVER['REQUEST_URI'], '?') == false) ? '?' : '&') .
            'verbose=1">' .
            "<b>&verbose=1</b></a><br />\n";
}
