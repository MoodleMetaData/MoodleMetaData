<?php
	global $CFG, $USER, $DB, $OUTPUT;

    	require_once($CFG->dirroot.'/lib/db/upgradelib.php');

    	$dbman = $DB->get_manager();

   	/**
    	* Version 2015111701 adds new columns to the feedback_form to indicate who owns the form and the visibility of the form.
    	* The owner is identified by the user ID and is a foreign key reference.
    	*/
    	if ($oldversion < 2015111701) {
        	  // Define field userid to be added to feedback_form.
        	$table = new xmldb_table('feedback_form');
        	$field = new xmldb_field('userid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'description');

        	// Conditionally launch add field userid.
        	if (!$dbman->field_exists($table, $field)) {
        	    	$dbman->add_field($table, $field);
        	}

    ...
        // Conditionally launch add field visibility.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Feedback_ec10 savepoint reached.
        upgrade_plugin_savepoint(true, 2015111701, 'local', 'feedback_ec10');

    }
?>
