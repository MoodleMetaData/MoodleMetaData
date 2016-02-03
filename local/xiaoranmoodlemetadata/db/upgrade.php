<?php
	global $CFG, $USER, $DB, $OUTPUT;

    	require_once($CFG->dirroot.'/lib/db/upgradelib.php');

    	$dbman = $DB->get_manager();

   	/**
    	* Version 2015111701 adds new columns to the feedback_form to indicate who owns the form and the visibility of the form.
    	* The owner is identified by the user ID and is a foreign key reference.
    	*/
	echo $oldversion;
    if ($oldversion < 20160205) {

        // Define key courseid (foreign) to be added to coursesession.
        $table = new xmldb_table('coursesession');
        $key = new xmldb_key('courseid', XMLDB_KEY_FOREIGN, array('courseid'), 'courseinfo', array('courseid'));

        // Launch add key courseid.
        $dbman->add_key($table, $key);

        // Xiaoranmoodlemetadata savepoint reached.
        upgrade_plugin_savepoint(true, 20160205, 'local', 'xiaoranmoodlemetadata');
    }
    if ($oldversion < 20160204) {
        // Define field courseid to be added to coursesession.
        $table = new xmldb_table('coursesession');
        $field = new xmldb_field('courseid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null, 'sessiontopic');

        // Conditionally launch add field courseid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Xiaoranmoodlemetadata savepoint reached.
        upgrade_plugin_savepoint(true, 20160204, 'local', 'xiaoranmoodlemetadata');
    }

    if ($oldversion < 20160203) {

        // Define field sessionname to be added to coursesession.
        $table = new xmldb_table('coursesession');
        $field = new xmldb_field('sessionname', XMLDB_TYPE_TEXT, null, null, null, null, null, 'sessiontopic');

        // Conditionally launch add field sessionname.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Xiaoranmoodlemetadata savepoint reached.
        upgrade_plugin_savepoint(true, 20160203, 'local', 'xiaoranmoodlemetadata');
    }

    if ($oldversion < 20160203) {

        // Rename field coursetopic on table courseinfo to courseobject.
        $table = new xmldb_table('courseinfo');
        $field = new xmldb_field('coursetopic', XMLDB_TYPE_TEXT, null, null, null, null, null, 'coursename');

        // Launch rename field courseobject.
        $dbman->rename_field($table, $field, 'courseobject');

        // Xiaoranmoodlemetadata savepoint reached.
        upgrade_plugin_savepoint(true, 20160203, 'local', 'xiaoranmoodlemetadata');
    }


    	if ($oldversion < 20160202) {

      	  // Define table coursesession to be created.
      	  $table = new xmldb_table('coursesession');

      	  // Adding fields to table coursesession.
      	  $table->add_field('sessionid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
      	  $table->add_field('sessiondate', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
      	  $table->add_field('sessiontopic', XMLDB_TYPE_TEXT, null, null, null, null, null);

       	 // Adding keys to table coursesession.
       	 $table->add_key('sessionid', XMLDB_KEY_PRIMARY, array('sessionid'));

       	 // Conditionally launch create table for coursesession.
        	if (!$dbman->table_exists($table)) {
       	     		$dbman->create_table($table);
        	}

       	 // Xiaoranmoodlemetadata savepoint reached.
        upgrade_plugin_savepoint(true, 20160202, 'local', 'xiaoranmoodlemetadata');
    	}

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
