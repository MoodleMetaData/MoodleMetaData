<?php

// Moodle settings and security based on capability
define("MOODLE_INTERNAL", TRUE);
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_login();
require_capability('moodle/site:config', get_context_instance(CONTEXT_SYSTEM));

echo '<html>';
echo '<head>';
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
echo '</head><body>';

// Query to show the bugged courses
$sql = 'SELECT n.courseid, n.gcount, c.fullname, c.shortname FROM (SELECT courseid, count(courseid) AS gcount FROM {enrol} WHERE enrol=\'guest\' GROUP BY courseid) AS n, {course} AS c WHERE c.id = n.courseid AND n.gcount > 2';
$records = $DB->get_recordset_sql($sql, null);

echo 'Courses With Guest Access Bug:<br>';
echo 'Go <a href="'.$CFG->wwwroot.'/local/eclass/landing/enrol_fix.php">here</a> to fix the course by id number<br><br>';

echo '<form name="enrol_fix" action="enrol_fix.php" method="post">';
echo '<table border=1>';
echo '<th>Course Id</th><th>Full Course Name</th><th>Short Course Name</th><th>Number of Guest Access Entries</th>';
foreach($records as $record) {
    echo '<tr><td>'.$record->courseid.'</td><td>'.$record->fullname.'</td><td>'.$record->shortname.'</td><td>'.$record->gcount.'</td><td><input type="checkbox" name="courseid[]" value="'.$record->courseid.'"</td></tr>';
}
$records->close();
echo '</table>';
echo '<br><input type="button" value="Fix" onclick="confirmation()"/>';
echo '</form>';
echo '</body></html>';