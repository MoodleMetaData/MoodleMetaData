<?php
define("MOODLE_INTERNAL", TRUE);

require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
//Require Login to get course list

require_login();
require_capability('moodle/site:config', get_context_instance(CONTEXT_SYSTEM));

$javascript = <<<JSCRIPT

<script type="text/javascript">
<!--
function confirmation() {
	var answer = confirm("Are you want to delete all Guest and Self Enrolment Methods from the selected course(s)?")
	if (answer){
		document.enrol_fix.submit();
	}
	else{

	}
}
//-->
</script>

JSCRIPT;
echo $javascript;

$courseid = null;
$content = 'default';
$valid = false;
$where = '';

if(isset($_POST['courseid'])) {

    $content = 'fix';
    $courseid = $_POST['courseid'];
}


if($content == 'default') {
    echo '<form name="enrol_fix" action="enrol_fix.php" method="post">Course ID: <input type"text" name="courseid" /><br>';
    echo '<input type="button" value="Fix" onclick="confirmation()"/><br><br>';
    echo 'Click <a href="'.$CFG->wwwroot.'/local/eclass/landing/enrol_view.php">here</a> to see all courses with guest enrollment method bug.';
}
else if($content == 'fix') {
    if(is_numeric($courseid)) {
        $where = 'courseid = '.$courseid;
        $valid = true;
    }
    else if(is_array($courseid)) {
        if(count($courseid) == 1) {
            $where = 'courseid = '.$courseid[0];
        }
        else {
            $where .= '(courseid='.implode(" OR courseid=",$courseid).')';
        }

        $valid = true;
    }
    else {
        echo 'That is not a courseid...';
    }

    if($valid == true) {
        $table = 'enrol';
        $where .= ' AND (enrol = \'guest\' OR enrol = \'self\')';

        if(!$DB->delete_records_select($table,$where)) {
            echo "Error - Failed to delete records from the database.";
        }
        else {
            if(is_numeric($courseid)) {
                echo 'Course has been fixed! You can go to the course by clicking <a href="'.$CFG->wwwroot.'/course/view.php?id='.$courseid.'">here</a><br>';
            }
            else if(is_array($courseid) && count($courseid) == 1) {
                echo 'Course has been fixed! You can go to the course by clicking <a href="'.$CFG->wwwroot.'/course/view.php?id='.$courseid[0].'">here</a><br>';
            }
            else {
                echo 'Courses have been fixed!<br>';
            }
            echo 'View list of broken courses <a href="'.$CFG->wwwroot.'/local/eclass/landing/enrol_view.php">here</a>';
        }
    }
}
else {
    echo 'Bad Form Input';
}
