<?php

// Moodle settings and security based on capability
define("MOODLE_INTERNAL", TRUE);
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_login();
require_capability('moodle/site:config', get_context_instance(CONTEXT_SYSTEM));

global $DB, $CFG;

echo '<html>';
echo '<head>';
$javascript = <<<JSCRIPT

<script type="text/javascript">
<!--
function confirmation() {
	var answer = confirm("Are you want to delete all unselected records?")
	if (answer){
		document.fix.submit();
	}
	else{

	}
}
//-->
</script>

JSCRIPT;
echo $javascript;
echo '</head><body>';

// Query to show the bugged courses
$sql = 'select gi.id,list.courseid,list.fullname,list.idnumber from (select c.fullname,c.idnumber,courseid,itemtype,count(courseid) as amount from mdl_grade_items g LEFT JOIN mdl_course c ON (g.courseid = c.id) where itemtype=\'course\' group by c.fullname,c.idnumber,courseid,itemtype having count(courseid) > 1) as list JOIN mdl_grade_items gi ON (list.courseid = gi.courseid) where gi.itemtype=\'course\' order by list.courseid,gi.id';
$records = $DB->get_recordset_sql($sql, null);

$processed_rec = array();
process_records($processed_rec, $records);

if (isset($_POST['ids'])) {
    //    print_r($_POST['ids']);
    $id_strs = $_POST['ids'];
    //first we process the passed ids

    if (is_array($id_strs)) {
        foreach ($id_strs as $id_str) {
            if (preg_match('/(\d+)_(\d+)/', $id_str, $a_matches)) {
                check_and_unset($processed_rec, $a_matches[1], $a_matches[2]);
            }
        }
    }
    else {
        echo 'Not valid post';
    }


    $where = "";
    //process the resulting array
    foreach ($processed_rec as $record) {
        if (!empty($record->deletable)) {
            foreach ($record->id as $key => $cur_id) {
                if (!empty($where)) {
                    $where .= " OR id=$cur_id";
                }
                else {
                    $where .= " id=$cur_id";
                }
            }
        }
    }
    if (empty($where)) {
        echo "<div>No records to delete.</div>";
    }
    elseif (!$DB->delete_records_select("grade_items", $where)) {
        echo "<div>Error - Failed to delete records from the database.</div>";
        exit;
    }
    else {
        echo "<div>Delete Successful!</div>";
    }
}

//foreach($records as $record){
//    print_r($record);
//    exit;
//}
echo 'Courses With Duplicate Grade Items:<br>';
//echo 'Go <a href="'.$CFG->wwwroot.'/eclass/landing/duplicate_grade_item_view.php">here</a> to fix the course by id number<br><br>';

echo '<form name="fix" action="" method="post">';
echo '<table border=1>';
echo '<th>Course id</th><th>Full Course Name</th><th>Choose id to keep</th>';
foreach ($processed_rec as $record) {
    echo '<tr><td><a href="' . $CFG->wwwroot . "/course/view.php?id=" . $record->courseid . "\" target='_blank'>$record->courseid";
    if (!empty($record->deletable)) {
        echo " (fixed)";
    }
    echo '</a></td><td>' . $record->fullname . '</td><td>';
    echo '<label for="' . $record->courseid . '_blank">All</label><input type="radio" id="' . $record->courseid . '_blank" name="ids[' . $record->courseid . ']" value="' . $record->courseid . '_' . 'blank"/><br/>';
    if (empty($record->deletable)) {
        foreach ($record->id as $id) {
            echo '<label for="' . $record->courseid . '_' . $id . '">' . $id . '</label><input type="radio" id="' . $record->courseid . '_' . $id . '" name="ids[' . $record->courseid . ']" value="' . $record->courseid . '_' . $id . '"/><br/>';
        }
    }

    echo '</td></tr>';
}
$records->close();
echo '</table>';
echo '<br><input type="button" value="Fix" onclick="confirmation()"/>';
echo '</form>';
echo '</body></html>';

function process_records(&$processed_rec, $records) {
    foreach ($records as $record) {
        if (empty($processed_rec[$record->courseid])) {
            $curr = $processed_rec[$record->courseid] = new stdClass();
            $curr->courseid = $record->courseid;
            $curr->fullname = $record->fullname;
            $curr->idnumber = $record->idnumber;
            $curr->id = array($record->id);
        }
        else {
            array_push($processed_rec[$record->courseid]->id, $record->id);
        }
    }
}

/**
 * Checks if the id for this course id has more than one record and that the id specified is in the set of records.
 * @param array $processed_array
 * @param $courseid
 * @param $id
 * @return bool true if it found and unset a record, false if nothing removed because it failed the criteria
 */
function check_and_unset(array &$processed_array, $courseid, $id) {
    //don't process if we're already deleting something out of this record set.
    if (!empty($processed_array[$courseid]) && count($processed_array[$courseid]->id) > 1 && empty($processed_array[$courseid]->deletable)) {
        $key = array_search($id, $processed_array[$courseid]->id);
        $do_delete = false;
        if (!$key && $processed_array[$courseid]->id[$key] == $id) {
            //key returned 0 and not false
            unset($processed_array[$courseid]->id[$key]);
            $do_delete = true;
        }
        elseif ($key) {
            //key was found
            unset($processed_array[$courseid]->id[$key]);
            $do_delete = true;
        }
        $processed_array[$courseid]->deletable = $do_delete;
        return true;
    }
    return false;
}