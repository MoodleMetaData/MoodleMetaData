<?php

	require_once '../../config.php';
	require_once $CFG->dirroot.'/lib/formslib.php';
	require_once $CFG->dirroot.'/lib/datalib.php';
	$DBname = "moodledb";
	$DBusername = "moodleuser";
	$DBpassword = "moodlepassword";
	$conn = new mysqli($DBname, $DBusername, $DBpassword);

	// Check connection
	if ($conn->connect_error) {
  	  die("Connection failed: " . $conn->connect_error);
	}	
	echo "Connected successfully";
?> 
