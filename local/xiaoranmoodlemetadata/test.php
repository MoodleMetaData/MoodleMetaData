<?php
	echo "this is just a test";
	global $DB;
	$user = $DB->get_record_sql('SELECT * FROM {courseinfo} WHERE courseid = ?', array(1111));
	echo "courseid (".$user.")has been found";
?>
