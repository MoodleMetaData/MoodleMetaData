<?php

// Moodle settings and security based on capability
define("MOODLE_INTERNAL", TRUE);
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_login();
require_capability('moodle/site:config', get_context_instance(CONTEXT_SYSTEM));

global $DB, $CFG;

echo '<html>';
echo '<head>';

// Query to show the bugged courses
//$sql = 'select gh.id,oldid,source,courseid,parent,depth,path,c.fullname,aggregation from {grade_categories_history} gh JOIN {course} c ON (gh.courseid = c.id) where courseid=?';
$sql = 'select * from {cohort_members} where userid=0';
$records = $DB->get_recordset_sql($sql);

echo "List of broken cohorts due to peoplesoft mismatch on CCID and EmpID's <br><br>";

//Display Results

echo '<table border=1>';
//gh.id,oldid,source,courseid,parent,depth,path,c.fullname,aggregation
echo '<th>Cohort Members id</th><th>Cohort ID</th><th>User Id</th><th>Time Added</th>';
foreach ($records as $record) {
    echo '<tr>';
    echo "<td>$record->id</td>";
    echo "<td>$record->cohortid</td>";
    echo "<td>$record->userid</td>";
    echo "<td>$record->timeadded</td>";
    echo '</tr>';
}
$records->close();
echo '</table>';

echo '</body></html>';

?>
