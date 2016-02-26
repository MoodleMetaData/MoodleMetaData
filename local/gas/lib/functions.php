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

namespace GAAT\functions {

    function getteacherroleid() {
        global $DB;

        $teacherroleid = $DB->get_field('role', 'id', array('shortname' => 'editingteacher'));
        return $teacherroleid;
    }

    /**
     * manually making the navigator link active
     * 
     * @param type $page
     * @param type $name
     */
    function makenavigatorlinkactive($page, $name) {
        $mainnode = $page->navigation->find('local_gas', \navigation_node::TYPE_CONTAINER);
        $activenode = $mainnode->find_active_node();
        $activenode->make_inactive();
        $node = $mainnode->get($name);
        $node->make_active();
    }

    function addvaliduser($row) {
        Global $DB;

        $id = $DB->insert_record("local_gas_users", $row);
    }

    /**
     * returns true if user have consent
     * 
     * @param type $userid
     */
    function isuservalid($userid) {
        Global $DB;

        $res = $DB->get_records("local_gas_users", array('user_id' => $userid));

        if (count($res) >= 1) {
            return true;
        }
        return false;
    }

    /**
     * returns true if the user is student in a course
     * 
     * @param type $userid
     * @return boolean
     */
    function isstudent($userid) {

        if (!isuservalid($userid)) {
            return false;
        }
        $allcourses = enrol_get_all_users_courses($userid);
        foreach ($allcourses as $c) {
            $cntx = \context_course::instance($c->id);
            $hasviewcap = has_capability('moodle/grade:view', $cntx, $userid);
            $haseditcap = has_capability('moodle/grade:edit', $cntx, $userid);

            if ($hasviewcap && !$haseditcap) {
                return true;
            }
        }

        return false;
    }

    /**
     * returns true if the user is student in the specified course
     * 
     * @param type $userid
     * @param type $courseid
     * @return boolean
     */
    function isstudentincourse($userid, $courseid) {

        if (!isuservalid($userid)) {
            return false;
        }
        $cntx = \context_course::instance($courseid);
        $hasviewcap = has_capability('moodle/grade:view', $cntx, $userid);
        $haseditcap = has_capability('moodle/grade:edit', $cntx, $userid);

        if ($hasviewcap && !$haseditcap) {
            return true;
        }

        return false;
    }

    /**
     * Returns true if the user is teacher in a course
     * 
     * @param type $userid
     * @return boolean
     */
    function isteacher($userid) {

        if (!isuservalid($userid)) {
            return false;
        }
        $allcourses = enrol_get_all_users_courses($userid);
        foreach ($allcourses as $c) {
            $cntx = \context_course::instance($c->id);
            $haseditcap = has_capability('moodle/grade:edit', $cntx, $userid);

            if ($haseditcap) {
                return true;
            }
        }

        return false;
    }

    /**
     * returns true if the user is teacher in the course
     * 
     * @param type $userid
     * @param type $courseid
     * @return boolean
     */
    function isteacherincourse($userid, $courseid) {

        if (!isuservalid($userid)) {
            return false;
        }
        $cntx = \context_course::instance($courseid);
        $haseditcap = has_capability('moodle/grade:edit', $cntx, $userid);
        if ($haseditcap) {
            return true;
        }

        return false;
    }

    /**
     * creating a new row in DB for storing the assessment
     * returns the id of row added
     * 
     * @global type $DB
     * @param type $id
     * @param type $time
     * @param type $timetaken
     * @param type $sem
     * @param type $mode
     * @param type $courseid
     * @return type
     */
    function newassessment($id, $time, $timetaken, $sem, $mode, $courseid = null) {

        global $DB;

        $row['timestamp'] = $time;
        $row['semester'] = $sem;
        $row['timetaken'] = $timetaken;
        $r = null;
        if ($mode == "Student") {
            $row['student_id'] = $id;
            $r = $DB->insert_record("local_gas_assessment", $row);
        } else if ($mode == "Teacher") {
            $row['user_id'] = $id;
            $row['course_id'] = $courseid;
            $r = $DB->insert_record("local_gas_course_assessment", $row);
        }

        return $r;
    }

    /**
     * adding a row in DB for assessment of one sub-attribute
     * returns id of the row added
     * 
     * @global type $DB
     * @param type $assid
     * @param type $subattid
     * @param type $value
     * @param type $mode
     * @return type
     */
    function newsubattassessment($assid, $subattid, $value, $mode) {

        global $DB;

        $row['subattribute_id'] = $subattid;
        $row['value'] = $value;
        $r = null;
        if ($mode == "Student") {
            $row['assessment_id'] = $assid;
            $r = $DB->insert_record("local_gas_subatt_assessment", $row);
        } else if ($mode == "Teacher") {
            $row['cassessment_id'] = $assid;
            $r = $DB->insert_record("local_gas_subatt_cassessment", $row);
        }

        return $r;
    }

    /**
     * adding a row in DB for each of the courses having impact on improvement of a sub-attribute
     * returns the id of row added
     * 
     * @global type $DB
     * @param type $subattassid
     * @param type $courseid
     * @param type $value
     * @return type
     */
    function newcontributedcourse($subattassid, $courseid, $value) {

        global $DB;

        $row['subatt_assessment_id'] = $subattassid;
        $row['course_id'] = $courseid;
        $row['value'] = $value;

        $r = $DB->insert_record("local_gas_contributed_course", $row);

        return $r;
    }

    /**
     * set the expire date for an attribute
     * 
     * @global type $DB
     * @param type $id
     */
    function deleteattribute($id) {
        global $DB;

        $t = time();
        $DB->execute("UPDATE {local_gas_attributes} SET exp_time = ? WHERE id = ?", array($t, $id));
    }

    /**
     * set the expire date for a sub-attribute
     * 
     * @global type $DB
     * @param type $sid
     */
    function deletesubattribute($sid) {
        global $DB;

        $t = time();
        $DB->execute("UPDATE {local_gas_subattributes} SET exp_time = ? WHERE id = ?", array($t, $sid));
    }

    /**
     * return the list of attributes valid at the time of $time
     * 
     * @global type $DB
     * @param type $lang
     * @param type $time
     * @return array
     */
    function getattributes($lang, $time) {
        global $DB;

        $r = $DB->get_records_sql("SELECT id from {local_gas_attributes} WHERE exp_time IS NULL OR exp_time > ?"
                , array($time));
        $result = $DB->get_records("local_gas_attributes_names", array('lang' => $lang), 'attribute_id', 'attribute_id,name');
        $res = array();

        foreach ($result as $ins) {
            if (current($r) == false) {
                break;
            }
            if ($ins->attribute_id == current($r)->id) {
                array_push($res, $ins);
                if (next($r) == false) {
                    break;
                }
            }
        }

        return $res;
    }

    /**
     * returns the list of sub-attributes valid at time of $time and related to attribute with id of $id
     * 
     * @global type $DB
     * @param type $id
     * @param type $lang
     * @param type $time
     * @return array
     */
    function getsubattributes($id, $lang, $time) {
        global $DB;

        $r = $DB->get_records_sql("SELECT id from {local_gas_subattributes} WHERE attribute_id = ?"
                . " AND (exp_time IS NULL OR exp_time > ?)", array($id, $time));
        $result = $DB->get_records("local_gas_subattributes_name", array('lang' => $lang), 'subattribute_id'
                , 'subattribute_id,name,'
                . 'description,description1,description2,description3,description4,description5');
        $res = array();

        foreach ($result as $ins) {
            if (current($r) == false) {
                break;
            }
            if ($ins->subattribute_id == current($r)->id) {
                array_push($res, $ins);
                if (next($r) == false) {
                    break;
                }
            }
        }

        return $res;
    }

    /**
     * adds a new attribute to the DB
     * 
     * @global type $DB
     * @param type $namee
     * @param type $namef
     * @return type
     */
    function insertattribute($name, $namef) {
        global $DB;
        $row['timestamp'] = time();
        $attid = $DB->insert_record("local_gas_attributes", $row);
        $r = array();
        $r['attribute_id'] = $attid;
        $r['lang'] = "en";
        $r['name'] = $name;
        $DB->insert_record("local_gas_attributes_names", $r);
        $r2 = array();
        $r2['attribute_id'] = $attid;
        $r2['lang'] = "fr";
        $r2['name'] = $namef;
        $DB->insert_record("local_gas_attributes_names", $r2);

        return $attid;
    }

    /**
     * adds a new sub-attribute to the DB
     * 
     * @global type $DB
     * @param type $id
     * @param type $namee
     * @param type $namef
     * @param type $endes
     * @param type $frdes
     * @param type $endes1
     * @param type $frdes1
     * @param type $endes2
     * @param type $frdes2
     * @param type $endes3
     * @param type $frdes3
     * @param type $endes4
     * @param type $frdes4
     * @param type $endes5
     * @param type $frdes5
     * @return type
     */
    function insertsubattribute($id, $namee, $namef, $endes, $frdes, $endes1, $frdes1, $endes2, $frdes2
    , $endes3, $frdes3, $endes4, $frdes4, $endes5, $frdes5) {
        global $DB;
        $row['timestamp'] = time();
        $row['attribute_id'] = $id;
        $sattid = $DB->insert_record("local_gas_subattributes", $row);
        $r = array();
        $r['subattribute_id'] = $sattid;
        $r['lang'] = "en";
        $r['name'] = $namee;
        $r['description'] = $endes;
        $r['description1'] = $endes1;
        $r['description2'] = $endes2;
        $r['description3'] = $endes3;
        $r['description4'] = $endes4;
        $r['description5'] = $endes5;
        $DB->execute("insert into {local_gas_subattributes_name} (subattribute_id,lang,name,description,description1,description2,"
                . "description3,description4,description5) values ( ?, ?, ?, ?, ?, ?, ?, ?, ?)", $r);
        $r2 = array();
        $r2['subattribute_id'] = $sattid;
        $r2['lang'] = "fr";
        $r2['name'] = $namef;
        $r2['description'] = $frdes;
        $r2['description1'] = $frdes1;
        $r2['description2'] = $frdes2;
        $r2['description3'] = $frdes3;
        $r2['description4'] = $frdes4;
        $r2['description5'] = $frdes5;
        $DB->execute("insert into {local_gas_subattributes_name} (subattribute_id,lang,name,description,description1,description2,"
                . "description3,description4,description5) values ( ?, ?, ?, ?, ?, ?, ?, ?, ?)", $r2);

        return;
    }

    /**
     * returns the semester of day and month in input
     * 
     * @global type $DB
     * @param type $cday
     * @param type $cmonth
     * @return type
     */
    function semofdate($cday, $cmonth) {
        global $DB;

        $sems = $DB->get_fieldset_select("local_gas_semesters", "semester", "");
        $csem = "";
        foreach ($sems as $sem) {
            $results = $DB->get_record("local_gas_semesters", array("semester" => $sem));
            if ($cmonth >= $results->startmonth && $cmonth <= $results->endmonth && $cday >= $results->startday &&
                    $cday <= $results->endday) {
                $csem = $sem;
                break;
            }
        }

        return $csem;
    }

    /**
     * returns the semester and year of course with id of $id
     * this is based in the start date of the course
     * 
     * @param type $id
     * @return type
     */
    function semandyearofcourse($id) {

        $course = get_course($id);
        $r['sem'] = semofdate(date('d', $course->startdate), date('m', $course->startdate));
        $r['year'] = date('Y', $course->startdate);

        return $r;
    }

    /**
     * returns all active term ids
     */
    function getcohortids() {
        global $DB;
        $query = "select term_id from {local_gas_activeterm}";
        $r = $DB->get_records_sql($query);

        return $r;
    }

    /**
     * add a active term to database
     * @param type $id
     */
    function addtermid($id) {
        global $DB;

        $row['term_id'] = $id;
        $DB->insert_record("local_gas_activeterm", $row);
    }

    /**
     * returns the list of courses related to a user with id of $id
     * 
     * @global type $DB
     * @param type $id
     * @param type $role = {"student" , "teacher"}
     * @param type $cyear
     * @param type $csem
     * @return type
     */
    function coursesas($id, $role) {

        global $DB;

        $allcourses = enrol_get_all_users_courses($id);
        $courses = array();
        $i = 0;
        $cohortids = getcohortids();
        $cohortcourses = array();
        if (count($cohortids) > 0) {
            $query = "select distinct e.courseid from {enrol} e, {cohort} co where "
                    . "e.customint1 = co.id and (co.idnumber like '" . current($cohortids)->term_id . "%'";
            for ($i = 1; $i < count($cohortids); $i++) {
                $query .= " or co.idnumber like '" . next($cohortids)->term_id . "%'";
            }
            $query .= ")";
            $cohortcourses = $DB->get_records_sql($query);
        }
        foreach ($allcourses as $course) {
            $isin = false;
            foreach ($cohortcourses as $cohortcourse) {
                if ($cohortcourse->courseid == $course->id) {
                    $isin = true;
                }
            }
            if (!$isin) {
                continue;
            }
            if ($role == 'student') {
                if (isstudentincourse($id, $course->id)) {
                    array_push($courses, $course);
                }
            }
            if ($role == 'teacher') {
                if (isteacherincourse($id, $course->id)) {
                    array_push($courses, $course);
                }
            }
        }
        return $courses;
    }

    /**
     * returns the list of students enroled in course with id of $id
     * 
     * @param type $id
     * @return array
     */
    function studentsofcourse($id) {
        $cntx = \context_course::instance($id);
        $allusers = get_enrolled_users($cntx);
        $students = array();
        foreach ($allusers as $user) {
            if (isstudentincourse($user->id, $id)) {
                array_push($students, $user->id);
            }
        }

        return $students;
    }

    /**
     * returns true if the user have done an assessment
     * 
     * @param type $userid
     * @param type $sem
     * @param type $year
     */
    function doneassessment($userid, $sem, $year) {
        global $DB;

        $data = $DB->get_records_sql("SELECT lastAssessment.id from (
                SELECT a.id, a.student_id, a.timestamp, a.semester, a.timetaken FROM
                {local_gas_assessment} a left join {local_gas_assessment} b on a.student_id = b.student_id and
                EXTRACT(YEAR from to_timestamp(a.timestamp)) = EXTRACT(YEAR from to_timestamp(b.timestamp))
                and a.semester = b.semester and
                a.timestamp < b.timestamp where b.timestamp is null and a.student_id = ? and a.semester = ? and
               EXTRACT(YEAR from to_timestamp(a.timestamp)) = ?
            ) as lastAssessment", array($userid, $sem, $year));
        if (count($data) == 0) {
            return false;
        }
        return true;
    }

    /**
     * This function brings back the value of last assessment a student have done,
     * in semester $sem, year $year, on subattribute of $subattribute_id
     * 
     * @global type $DB
     * @param type $studentid
     * @param type $sem
     * @param type $year
     * @param type $subattributeid
     * @return int
     */
    function retrievesubattributevalue($studentid, $sem, $year, $subattributeid) {

        global $DB;

        $data = $DB->get_records_sql("SELECT subattAssessment.value from (
                SELECT a.id, a.student_id, a.timestamp, a.semester, a.timetaken FROM
                {local_gas_assessment} a left join {local_gas_assessment} b on a.student_id = b.student_id and
                EXTRACT(YEAR from to_timestamp(a.timestamp)) = EXTRACT(YEAR from to_timestamp(b.timestamp))
                and a.semester = b.semester and
                a.timestamp < b.timestamp where b.timestamp is null and a.student_id = ? and a.semester = ? and
               EXTRACT(YEAR from to_timestamp(a.timestamp)) = ?
            ) lastAssessment left join {local_gas_subatt_assessment} subattAssessment on
                lastAssessment.id = subattAssessment.assessment_id
		where subattAssessment.subattribute_id = ?
        ", array($studentid, $sem, $year, $subattributeid));
        if (count($data) == 0) {
            return 1;
        }
        return intval(current($data)->value);
    }

    /**
     * This function creates the division for assessment of one sub-attribute (student)
     * 
     * @param type $studentid
     * @param type $num is the id of attribute
     * @param type $num2 is the id of sub-attribute
     * @param type $courses
     */
    function slider($studentid, $num, $num2, $courses) {

        $labels = getsubattributes($num, current_language(), time());
        $label = $labels[$num2];
        $values = get_string('values', 'local_gas');
        $size = count($labels);
        $sizeofcourses = count($courses) + 1;
        $cyear = date("Y");
        $csem = semofdate(date("d"), date("m"));
        $previousvalue = retrieveSubattributeValue($studentid, $csem, $cyear, $label->subattribute_id);
        echo ("

	<tr class='active' onclick=' ShowDes($num,$num2,$size);' style='cursor:default;'>
		<td class='ass-cell1 table-bordered'>
			<tt>$label->name </tt><i class='fa fa-info-circle' data-toggle='tooltip'  data-placement='bottom' title='" .
        $label->description .
        "'></i>
		</td>
                <td class='ass-cell4' style='text-align: center;'>
                    $values[0]<br/>(1)
                </td>
		<td class='ass-cell2' style='text-align: center;'>
                    <input type='range' value='$previousvalue' class='rangeInput' id='rangeInput$num$num2'"
        . " name='rangeInput[]' min='1' max='5' step='1' oninput='out$num$num2.value = rangeInput$num$num2.value; "
        . "ShowDes($num,$num2);' onchange='setChanges()' onfocus='' onfocus='' /><br/>
                    <input type='hidden' value='$label->subattribute_id' name='subattID[]'>
                    <div class='rangeOut'>
                        <output id='out$num$num2' style='color:white'>1</output>
                        <script>
                            out$num$num2.value = rangeInput$num$num2.value;
                        </script>
                    </div>
		</td>
                <td class='ass-cell4' style='text-align: center;'>
                    $values[4]<br/>(5)
                </td>
                <td class='table-bordered' style='text-align: center;'>
                <div><button type='button' onclick=\"nextCourse
                ('courseBlock$num$num2',-1,$sizeofcourses);\" class='btn btn-success'  "
        . "data-toggle='tooltip' data-placement='bottom' title='" .
        get_string('previousCourse', 'local_gas') . "'><b><</b></button></div>"
        . "</td>
                <td class='table-bordered' style='width:150px; text-align: center;'><div class='row-fluid'>");
        $z = 0;
        echo("  <div id='courseBlock$num$num2$z' >
                <div class='courseBlock$num$num2' id='$z'>
                    <label> < Courses > </label>
                </div>
            </div>
		");
        for ($i = 1; $i < $sizeofcourses; $i++) {
            $course = $courses[$i - 1];
            $coursename = $course->shortname;
            echo("      <div style = 'display:none' id='courseBlock$num$num2$i' >
                        <div class='courseBlock$num$num2' id='$i'>
                            <label>$coursename</label>
                            <input type='hidden' name='CourseIDFor$label->subattribute_id[]' value='$course->id'>
                            <label class='radio' style='font-size:x-small;'>
                                <input type='radio' onchange='setChanges()' id='1mm$num$num2$i' name='"
            . "CourseRadio$label->subattribute_id$course->id' value='3'/>" . get_string('Major', 'local_gas') . "
                            </label>
                            <label class='radio' style='font-size:x-small;'>
                                <input type='radio' onchange='setChanges()' id='2mm$num$num2$i' name='"
            . "CourseRadio$label->subattribute_id$course->id' value='2'/>" . get_string('Minor', 'local_gas') . "
                            </label>
                            <label class='radio' style='font-size:x-small;'>
                                <input type='radio' onchange='setChanges()'
                                name='CourseRadio$label->subattribute_id$course->id' value='0' />"
                    . get_string('none', 'local_gas') . "
                            </label>
                        </div>
                    </div>
		");
        }
        echo("</td><td class='table-bordered' style='text-align: center;'>
                <div><button type='button'  onclick=\"nextCourse('courseBlock$num$num2',"
        . "1,$sizeofcourses);\" class='btn btn-success'  data-toggle='tooltip' data-placement='bottom' title='" .
        get_string('nextCourse', 'local_gas') . "'><b>></b></button></div>"
        . "</div></td></tr>");
        echo("<tr><td colspan='7'>
                 <table class='table' style='display:none;' id='attDes$num$num2'>
                     <tr>
                        <td><tt style='font-weight: bold;'>"
        . get_string('value', 'local_gas') .
        "</tt></td>
        <td><tt style='font-weight: bold;'>"
        . get_string('description', 'local_gas') .
        "</tt></td>
    </tr>");
        echo("<tr class='descriptionRow$num$num2" . "1'><td>");
        echo("(1) " . $values[0]);
        echo("</td><td>");
        echo($label->description1);
        echo("</td></tr>");

        echo("<tr class='descriptionRow$num$num2" . "2'><td>");
        echo("(2) " . $values[1]);
        echo("</td><td>");
        echo($label->description2);
        echo("</td></tr>");

        echo("<tr class='descriptionRow$num$num2" . "3'><td>");
        echo("(3) " . $values[2]);
        echo("</td><td>");
        echo($label->description3);
        echo("</td></tr>");

        echo("<tr class='descriptionRow$num$num2" . "4'><td>");
        echo("(4) " . $values[3]);
        echo("</td><td>");
        echo($label->description4);
        echo("</td></tr>");

        echo("<tr class='descriptionRow$num$num2" . "5'><td>");
        echo("(5) " . $values[4]);
        echo("</td><td>");
        echo($label->description5);
        echo("</td></tr>");

        echo("</table>
        </td>
    </tr>");
    }

    /**
     * This function creates the divisons needed for assessment of an attribute (student)
     * 
     * @param type $studentid
     * @param type $num is the id of attribute
     * @param type $courses is the list of courses the student is in
     */
    function sliderset($studentid, $num, $courses) {
        $labels = getsubattributes($num, current_language(), time());
        echo ("
	<table class='table'>
        <tr class='table-bordered'>
			<td class='ass-cell1' rowspan='2' style='text-align: center; vertical-align: middle;'><label>" .
        get_string('sub-attributes-label', 'local_gas') . "</label>
			</td>
   <td style='text-align: center; vertical-align: middle;' colspan='3' rowspan='2'><label>" .
        get_string('value', 'local_gas') . "</label>
			</td>
   <td class='ass-cell3'  style='text-align: center;' colspan='3'><label>" . get_string('ContributedCourses', 'local_gas') .
        " <i class='fa fa-info-circle' data-toggle='tooltip'  data-placement='bottom' title='" .
        get_string('ContributedCoursesTitle', 'local_gas') .
        "'></i></label>
			</td>
		</tr>
		<tr></tr>
	");
        for ($i = 1; $i <= count($labels); $i ++) {
            slider($studentid, "" + $num, "" + $i - 1, $courses);
        }
        echo ("
	</table>
	");
    }

    /**
     * This function brings back the value of last assessment a teacher have done,
     * 
     * @global type $DB
     * @param type $userid
     * @param type $sem
     * @param type $year
     * @param type $courseid
     * @param type $subattributeid
     * @return int
     */
    function teacherretrievesubattributevalue($userid, $sem, $year, $courseid, $subattributeid) {

        global $DB;

        $data = $DB->get_records_sql("SELECT subattAssessment.value from (
                SELECT a.id, a.user_id, a.timestamp, a.semester, a.timetaken FROM
                {local_gas_course_assessment} a left join {local_gas_course_assessment} b on a.user_id = b.user_id and
                EXTRACT(YEAR from to_timestamp(a.timestamp)) = EXTRACT(YEAR from to_timestamp(b.timestamp))
                and a.semester = b.semester and
                a.timestamp < b.timestamp where b.timestamp is null and a.user_id = ? and a.semester = ?
                and EXTRACT(YEAR from to_timestamp(a.timestamp))
                = ? and a.course_id = ?
            ) lastAssessment left join {local_gas_subatt_cassessment} subattAssessment on
                lastAssessment.id = subattAssessment.cassessment_id
		where subattAssessment.subattribute_id = ?
        ", array($userid, $sem, $year, $courseid, $subattributeid));
        if (count($data) == 0) {
            return 1;
        }
        return intval(current($data)->value);
    }

    /**
     * This function creates the divisions needed for assessment of one sub-attribute (instructor)
     * 
     * @param type $userid
     * @param type $courseid
     * @param type $num is the id of attribute
     * @param type $num2 is the id of sub-attribute
     * @return type
     */
    function teacherslider($userid, $courseid, $num, $num2) {

        $labels = getsubattributes($num, current_language(), time());
        $label = $labels[$num2];
        $values = get_string('values', 'local_gas');
        $size = count($labels);
        $cyear = date("Y");
        $csem = semofdate(date("d"), date("m"));
        $previousvalue = teacherretrievesubattributevalue($userid, $csem, $cyear, $courseid, $label->subattribute_id);

        echo ("

	<tr class='active' onclick=' ShowDes($num,$num2,$size);' style='cursor:default;'>
		<td class='ass-cell1 table-bordered'>
			<tt>$label->name </tt><i class='fa fa-info-circle' data-toggle='tooltip'  data-placement='bottom' title='" .
        $label->description .
        "'></i>
		</td>
                <td class='ass-cell4' style='text-align: center;'>
                    $values[0]<br/>(1)
                </td>
		<td class='ass-cell2' style='text-align: center;'>
                    <input type='range' value='$previousvalue' class='rangeInput' id='rangeInput$num$num2' name='rangeInput[]'"
        . " min='1' max='5' step='1' oninput='out$num$num2.value = rangeInput$num$num2.value; ShowDes($num,$num2);"
        . " SetAverage$num();' onchange='setChanges()' onfocus='' onfocus='' /><br/>
                    <input type='hidden' value='$label->subattribute_id' name='subattID[]'>
                    <div class='rangeOut'>
                        <output id='out$num$num2' style='color:white'>$previousvalue</output>
                    </div>
		</td>
                <td class='ass-cell4' style='text-align: center;'>
                    $values[4]<br/>(5)
                </td>
                ");
        echo("</tr>");
        echo("<tr><td colspan='4'>
                 <table class='table' style='display:none;' id='attDes$num$num2'>
                     <tr>
                        <td><tt style='font-weight: bold;'>"
        . get_string('value', 'local_gas') .
        "</tt></td>
        <td><tt style='font-weight: bold;'>"
        . get_string('description', 'local_gas') .
        "</tt></td>
    </tr>");
        echo("<tr class='descriptionRow$num$num2" . "1'><td>");
        echo("(1) " . $values[0]);
        echo("</td><td>");
        echo($label->description1);
        echo("</td></tr>");

        echo("<tr class='descriptionRow$num$num2" . "2'><td>");
        echo("(2) " . $values[1]);
        echo("</td><td>");
        echo($label->description2);
        echo("</td></tr>");

        echo("<tr class='descriptionRow$num$num2" . "3'><td>");
        echo("(3) " . $values[2]);
        echo("</td><td>");
        echo($label->description3);
        echo("</td></tr>");

        echo("<tr class='descriptionRow$num$num2" . "4'><td>");
        echo("(4) " . $values[3]);
        echo("</td><td>");
        echo($label->description4);
        echo("</td></tr>");

        echo("<tr class='descriptionRow$num$num2" . "5'><td>");
        echo("(5) " . $values[4]);
        echo("</td><td>");
        echo($label->description5);
        echo("</td></tr>");

        echo("</table>
        </td>
    </tr>");

        return $previousvalue;
    }

    /**
     * This function creates the divisions needed for assessment of an attribute (instructor)
     * 
     * @param type $userid
     * @param type $courseid
     * @param type $num is the id of attribute
     */
    function teachersliderset($userid, $courseid, $num) {
        $labels = getsubattributes($num, current_language(), time());
        echo("<script>function SetAverage$num(){ var r=parseInt(0);");
        for ($i = 0; $i < count($labels); $i ++) {
            echo("r+= parseInt($('#rangeInput$num$i').val());");
        }
        $cc = count($labels);
        echo("r/=parseInt($cc);");
        echo("aveOf$num.value = r;");
        echo(" }</script>");
        echo ("
            <table class='table'>
            <tr class='table-bordered'>
                            <td class='ass-cell1' style='text-align: center; vertical-align: middle;'><label>"
        . get_string('sub-attributes-label', 'local_gas') . "</label>
                            </td>
                            <td style='text-align: center; vertical-align: middle;' colspan='3'><label>"
        . get_string('value', 'local_gas') . "</label>
                            </td>
                    </tr>");
        $previousvalues = 0;
        for ($i = 1; $i <= count($labels); $i ++) {
            $previousvalues += teacherslider($userid, $courseid, "" + $num, "" + $i - 1);
        }
        $previousvalues /= $cc;
        echo("<tr><td colspan='4'><input type='hidden' value='$previousvalues' id='aveOf$num'></td></tr>");
        echo ("</table>");
    }

    /**
     * Returns the number of courses having the value of $value assessed by $students in assessment of sub-attribute having id
     * of $subAttId
     * 
     * @param type $data is the list of sub-attribute assessments and the value assigned to courses
     * @param type $students
     * @param type $subattid
     * @param type $value
     * @return int
     */
    function getcontributedcourse($data, $students, $subattid, $value) {
        $r = 0;

        foreach ($data as $row) {
            if (in_array($row->student_id, $students) && $row->subattribute_id == $subattid && $row->cvalue == $value) {
                $r++;
            }
        }

        return $r;
    }

    /**
     * returns the assessment of instrcutor with id of $userID for the course having the id of $courseID
     * 
     * @global type $DB
     * @param type $userid
     * @param type $courseid
     * @return type
     */
    function loadteacherassessment($userid, $courseid) {

        global $DB;

        $data = $DB->get_records_sql("
        select suba.id as subatt_cassessment_id,suba.subattribute_id,suba.value, ass.id, ass.timestamp,
        ass.semester from (SELECT a.id, a.user_id, a.timestamp, a.semester, a.timetaken
        FROM {local_gas_course_assessment} a left join {local_gas_course_assessment} b on a.user_id = b.user_id and
        EXTRACT(YEAR from to_timestamp(a.timestamp)) = EXTRACT(YEAR from to_timestamp(b.timestamp)) and
        a.semester = b.semester and a.timestamp < b.timestamp
        where b.timestamp is null and a.user_id = ? and a.course_id = ?) as ass join
        {local_gas_subatt_cassessment} as suba
        on suba.cassessment_id = ass.id", array($userid, $courseid));

        return $data;
    }

    /**
     * Returns the joined assessment of all students for course having id of $courseId
     * 
     * @global type $DB
     * @param type $courseid
     * @return type
     */
    function getreportdata($courseid) {
        global $DB;

        $data = $DB->get_records_sql("select cc.id as course_contribution_id ,al.subattribute_id,al.assessment_id,al.timestamp
            ,al.semester, al.student_id,cc.course_id, al.value, cc.value as cvalue
        from (
			select suba.id as subatt_assessment_id,suba.subattribute_id, suba.value
   , ass.student_id,suba.assessment_id, ass.timestamp,ass.semester from
				(SELECT a.id, a.student_id, a.timestamp, a.semester, a.timetaken
					FROM {local_gas_assessment} a left join {local_gas_assessment} b on a.student_id = b.student_id and
						EXTRACT(YEAR from to_timestamp(a.timestamp)) = EXTRACT(YEAR from to_timestamp(b.timestamp)) and
						a.semester = b.semester and a.timestamp < b.timestamp where b.timestamp is null
				) as ass join
				{local_gas_subatt_assessment} as suba
				on suba.assessment_id = ass.id
			) al join {local_gas_contributed_course} cc on al.subatt_assessment_id = cc.subatt_assessment_id
        where cc.course_id = ?", array($courseid));
        return $data;
    }

    /**
     * Returns the average of all (student's)assessments for sub-attribute having id of $subattId
     * 
     * @param type $data is the joined assessments of all students
     * @param type $subattid
     * @return int
     */
    function assesssubattvalue($data, $subattid) {

        $r = 0;
        $c = 0;

        foreach ($data as $row) {
            if ($row->subattribute_id == $subattid) {
                $r += $row->value;
                $c++;
            }
        }
        if ($c != 0) {
            $r /= $c;
        }

        return $r;
    }

    /**
     * returns the value assigned to the assessment of the whole class based on the average of the average
     * for all sub-attributes in this attribute
     * 
     * @param type $data is the joined assessments of all students
     * @param type $attid is the id of attribute
     * @return type
     */
    function assessattvalue($data, $attid) {

        $subatts = getsubattributes($attid, current_language(), time());
        $r = 0;
        foreach ($subatts as $subatt) {
            $r += assessSubattValue($data, $subatt->subattribute_id);
        }

        if (count($subatts) == 0) {
            $r = 0;
        } else {
            $r /= count($subatts);
        }

        return $r;
    }

    /**
     * This function creates the divisions needed for the courseReport page
     * 
     * @param type $courseid
     * @return type
     */
    function coursereportpage($courseid) {

        $subattributeslabel = get_string('sub-attributes-label', 'local_gas');
        $majornum = get_string('numOfMajor', 'local_gas');
        $minornum = get_string('numOfMinor', 'local_gas');
        $labels = getAttributes(current_language(), time());

        $students = studentsofcourse($courseid);
        $numofstudents = count($students);
        $numofstudentsdoneassessment = 0;
        $year = date("Y");
        $sem = semofdate(date("d"), date("m"));
        if (count($students) > 0) {
            foreach ($students as $studentid) {
                if (doneassessment($studentid, $sem, $year)) {
                    $numofstudentsdoneassessment++;
                }
            }
        }

        $data = getreportdata($courseid);

        echo ("
        <div><br/>
            <p>$numofstudentsdoneassessment out of $numofstudents students having this course have completed
                their assessment of this semester.</p>
            <table class='table table-bordered'>
                <tr class='warning'>
                    <td>$subattributeslabel
                    </td>
                    <td>$majornum
                    </td>
                    <td>$minornum
                    </td>
                </tr>
                ");
        $class = ["success", "info"];
        $i = 0;
        foreach ($labels as $label) {
            $sublabels = getsubattributes($label->attribute_id, current_language(), time());
            foreach ($sublabels as $sublabel) {
                $r1 = getcontributedcourse($data, $students, $sublabel->subattribute_id, 3);
                $r2 = getcontributedcourse($data, $students, $sublabel->subattribute_id, 2);
                echo("<tr class='" . $class[$i % 2] . "'>
                            <td>$sublabel->name
                            </td>
                            <td>$r2
                            </td>
                            <td>$r1
                            </td>
                        </tr>");
            }
            $i++;
        }
        echo("
            </table>
        </div>");

        return $data;
    }

}
