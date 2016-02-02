<?php
	echo "this is just a test";
	global $DB;
	$user = $DB->get_record_sql('Select Column_name from Information_schema.columns where Table_name like ?'table name'', array('mdl_courseinfo'));
	echo $user;
?>
