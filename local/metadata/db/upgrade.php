<?php
	global $CFG, $USER, $DB, $OUTPUT;

    	require_once($CFG->dirroot.'/lib/db/upgradelib.php');

    	$dbman = $DB->get_manager();

   	/**
    	* Version 2016020801 adds all the basic tables
         */
    if ($oldversion < 2016020802) {

        // Define field assessmentnumber to be added to courseinfo.
        $table = new xmldb_table('courseinfo');
        $field = new xmldb_field('assessmentnumber', XMLDB_TYPE_INTEGER, '20', null, null, null, null, 'courseid');

        // Conditionally launch add field assessmentnumber.
        if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
                }
    
        // Metadata savepoint reached.
        upgrade_plugin_savepoint(true, 2016020802, 'local', 'metadata');
    }        

    if ($oldversion < 2016020802) {

    // Define field sessionnumber to be added to courseinfo.
        $table = new xmldb_table('courseinfo');
        $field = new xmldb_field('sessionnumber', XMLDB_TYPE_INTEGER, '20', null, null, null, null, 'assessmentnumber');

        // Conditionally launch add field sessionnumber.
        if (!$dbman->field_exists($table, $field)){
            $dbman->add_field($table, $field);
        }
        // Metadata savepoint reached.
        upgrade_plugin_savepoint(true, 2016020802, 'local', 'metadata');
    }

    if ($oldversion < 2016020801) {

        // Define table courseinstructors to be created.
        $table = new xmldb_table('courseinstructors');

        // Adding fields to table courseinstructors.
        $table->add_field('name', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('instructorid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('officelocation', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('officehours', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('type', XMLDB_TYPE_TEXT, null, null, null, null, null);

        // Adding keys to table courseinstructors.
        $table->add_key('instructorid', XMLDB_KEY_PRIMARY, array('instructorid'));

        // Conditionally launch create table for courseinstructors.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Metadata savepoint reached.
        upgrade_plugin_savepoint(true, 2016020801, 'local', 'metadata');
    }

    if ($oldversion < 2016020801) {

        // Define table courseinfo to be created.
        $table = new xmldb_table('courseinfo');

        // Adding fields to table courseinfo.
        $table->add_field('coursename', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('coursetopic', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('coursedescription', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('courseinstructor', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('coursefaculty', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table courseinfo.
        $table->add_key('courseid', XMLDB_KEY_PRIMARY, array('courseid'));
        $table->add_key('courseinstructor', XMLDB_KEY_FOREIGN, array('courseinstructor'), 'courseinstructors', array('instructorid'));

        // Conditionally launch create table for courseinfo.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Metadata savepoint reached.
        upgrade_plugin_savepoint(true, 2016020801, 'local', 'metadata');
    }

    if ($oldversion < 2016020801) {

        // Define table courseassessment to be created.
        $table = new xmldb_table('courseassessment');

        // Adding fields to table courseassessment.
        $table->add_field('assesmentname', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('assesmentweight', XMLDB_TYPE_INTEGER, '2', null, null, null, '0');
        $table->add_field('assessmentduedate', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('assesmentid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('type', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('objective', XMLDB_TYPE_TEXT, null, null, null, null, null);

        // Adding keys to table courseassessment.
        $table->add_key('courseid', XMLDB_KEY_FOREIGN, array('courseid'), 'courseinfo', array('courseid'));
        $table->add_key('assesmentid', XMLDB_KEY_PRIMARY, array('assesmentid'));

        // Conditionally launch create table for courseassessment.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Metadata savepoint reached.
        upgrade_plugin_savepoint(true, 2016020801, 'local', 'metadata');
    }

    if ($oldversion < 2016020801) {

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

        // Metadata savepoint reached.
        upgrade_plugin_savepoint(true, 2016020801, 'local', 'metadata');
    }
   	

    if ($oldversion < 2016020801) {

        // Define table coursesession to be created.
        $table = new xmldb_table('coursesession');

        // Adding fields to table coursesession.
        $table->add_field('sessionid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('sessiondate', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('sessiontopic', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('sessiontype', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('sessiondescription', XMLDB_TYPE_TEXT, null, null, null, null, null);

        // Adding keys to table coursesession.
        $table->add_key('sessionid', XMLDB_KEY_PRIMARY, array('sessionid'));
        $table->add_key('courseid', XMLDB_KEY_FOREIGN, array('courseid'), 'courseinfo', array('courseid'));

        // Conditionally launch create table for coursesession.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Metadata savepoint reached.
        upgrade_plugin_savepoint(true, 2016020801, 'local', 'metadata');
    }

    if ($oldversion < 2016020801) {

        // Define table coursetag to be created.
        $table = new xmldb_table('coursetag');

        // Adding fields to table coursetag.
        $table->add_field('tagid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table coursetag.
        $table->add_key('tagid', XMLDB_KEY_PRIMARY, array('tagid'));
        $table->add_key('courseid', XMLDB_KEY_FOREIGN, array('courseid'), 'courseinfo', array('courseid'));

        // Conditionally launch create table for coursetag.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Metadata savepoint reached.
        upgrade_plugin_savepoint(true, 2016020801, 'local', 'metadata');
    }
?>
