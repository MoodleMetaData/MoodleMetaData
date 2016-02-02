<?php
	global $CFG, $USER, $DB, $OUTPUT;

    	require_once($CFG->dirroot.'/lib/db/upgradelib.php');

    	$dbman = $DB->get_manager();

   	/**
    	* Version 2015111701 adds new columns to the feedback_form to indicate who owns the form and the visibility of the form.
    	* The owner is identified by the user ID and is a foreign key reference.
    	*/
    	if ($oldversion < 20160201) {

		// Define table courseinfo to be created.
		$table = new xmldb_table('courseinfo');

		// Adding fields to table courseinfo.
		$table->add_field('coursename', XMLDB_TYPE_TEXT, null, null, null, null, null);
		$table->add_field('coursetopic', XMLDB_TYPE_TEXT, null, null, null, null, null);
		$table->add_field('coursedescription', XMLDB_TYPE_TEXT, null, null, null, null, null);
		$table->add_field('courseinstructor', XMLDB_TYPE_TEXT, null, null, null, null, null);
		$table->add_field('coursefaculty', XMLDB_TYPE_TEXT, null, null, null, null, null);
		$table->add_field('courseid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);

		// Adding keys to table courseinfo.
		$table->add_key('courseid', XMLDB_KEY_PRIMARY, array('courseid'));

		// Conditionally launch create table for courseinfo.
		if (!$dbman->table_exists($table)) {
		    $dbman->create_table($table);
		}

		// Xiaoranmoodlemetadata savepoint reached.
		upgrade_plugin_savepoint(true, 20160201, 'local', 'xiaoranmoodlemetadata');
	}
    	if ($oldversion < 20160201) {

		// Define table courseassessment to be created.
		$table = new xmldb_table('courseassessment');

		// Adding fields to table courseassessment.
		$table->add_field('assesmentname', XMLDB_TYPE_TEXT, null, null, null, null, null);
		$table->add_field('assesmentweight', XMLDB_TYPE_INTEGER, '2', null, null, null, '0');
		$table->add_field('assessmentduedate', XMLDB_TYPE_TEXT, null, null, null, null, null);
		$table->add_field('courseid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
		$table->add_field('assesmentid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);

		// Adding keys to table courseassessment.
		$table->add_key('courseid', XMLDB_KEY_FOREIGN, array('courseid'), 'courseinfo', array('courseid'));
		$table->add_key('assesmentid', XMLDB_KEY_PRIMARY, array('assesmentid'));

		// Conditionally launch create table for courseassessment.
		if (!$dbman->table_exists($table)) {
		    $dbman->create_table($table);
		}

		// Xiaoranmoodlemetadata savepoint reached.
		upgrade_plugin_savepoint(true, 20160201, 'local', 'xiaoranmoodlemetadata');
    	}
    	if ($oldversion < 20160201) {

		// Define table coursereadings to be created.
		$table = new xmldb_table('coursereadings');

		// Adding fields to table coursereadings.
		$table->add_field('readingurl', XMLDB_TYPE_TEXT, null, null, null, null, null);
		$table->add_field('readingname', XMLDB_TYPE_TEXT, null, null, null, null, null);
		$table->add_field('readingid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
		$table->add_field('courseid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);

		// Adding keys to table coursereadings.
		$table->add_key('readingid', XMLDB_KEY_PRIMARY, array('readingid'));
		$table->add_key('courseid', XMLDB_KEY_FOREIGN, array('courseid'), 'courseinfo', array('courseid'));

		// Conditionally launch create table for coursereadings.
		if (!$dbman->table_exists($table)) {
		    $dbman->create_table($table);
		}

		// Xiaoranmoodlemetadata savepoint reached.
		upgrade_plugin_savepoint(true, 20160201, 'local', 'xiaoranmoodlemetadata');
    	}


?>
