

<?php
	global $PAGE, $CFG, $DB;
//	require_once $CFG->dirroot.'/lib/formslib.php';
//	require_once $CFG->dirroot.'/lib/datalib.php';
   	require_once('../../config.php');
   // $PAGE ->set_title(get_string('pluginname','local_xiaoranmoodlemetadata'));
   	echo "this is just a test\n";
	echo " ";
	$sqlmy = "SELECT * FROM {courseinfo}";
 	if($result=$DB->get_records_sql($sqlmy)){
//	 	echo'<table border="1"><tr>
//		<th>coursename</th>
//		<th>courseobject</th>
//		<th>coursedescription</th>
//		<th>courseinstructor</th>
//		<th>coursefaculty</th>
//		<th>courseid</th>
//		</tr>';
		foreach($result as $item){
//			echo '<tr><td>'.implode("</td><td>",
//			(array)($item)).'</td?</tr?';
			echo $item->courseid;
		}
		echo '</table>';
	}
?>

