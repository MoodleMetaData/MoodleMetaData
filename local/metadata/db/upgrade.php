<?php
	global $CFG, $USER, $DB, $OUTPUT;

    	require_once($CFG->dirroot.'/lib/db/upgradelib.php');

    	$dbman = $DB->get_manager();

   	/**
    	* Version 2016020801 adds all the basic tables
         */
     if ($oldversion < 2016021901) {

        // Define table courseinfo to be created.
        $table = new xmldb_table('courseinfo');

        // Adding fields to table courseinfo.
        $table->add_field('coursename', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('coursetopic', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('coursedescription', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('coursefaculty', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('assessmentnumber', XMLDB_TYPE_INTEGER, '20', null, null, null, null);
        $table->add_field('sessionnumber', XMLDB_TYPE_INTEGER, '20', null, null, null, null);

        // Adding keys to table courseinfo.
        $table->add_key('courseid', XMLDB_KEY_PRIMARY, array('courseid'));

        // Conditionally launch create table for courseinfo.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        
        

        // Define table courseassessment to be created.
        $table = new xmldb_table('courseassessment');

        // Adding fields to table courseassessment.
        $table->add_field('assessmentname', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('assessmentweight', XMLDB_TYPE_INTEGER, '3', null, null, null, '0');
        $table->add_field('assessmentduedate', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('assessmentid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('type', XMLDB_TYPE_TEXT, null, null, null, null, null);

        // Adding keys to table courseassessment.
        $table->add_key('courseid', XMLDB_KEY_FOREIGN, array('courseid'), 'courseinfo', array('courseid'));
        $table->add_key('assessmentid', XMLDB_KEY_PRIMARY, array('assessmentid'));

        // Conditionally launch create table for courseassessment.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }


        // Define table coursereadings to be created.
        $table = new xmldb_table('coursereadings');

        // Adding fields to table coursereadings.
        $table->add_field('readingurl', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('readingname', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('readingid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table coursereadings.
        $table->add_key('readingid', XMLDB_KEY_PRIMARY, array('readingid'));
        $table->add_key('courseid', XMLDB_KEY_FOREIGN, array('courseid'), 'courseinfo', array('courseid'));

        // Conditionally launch create table for coursereadings.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        
        
        // Define table coursesession to be created.
        $table = new xmldb_table('coursesession');

        // Adding fields to table coursesession.
        $table->add_field('sessionid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
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
    

        // Define table courseinstructors to be created.
        $table = new xmldb_table('courseinstructors');

        // Adding fields to table courseinstructors.
        $table->add_field('name', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('instructorid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('officelocation', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('officehours', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('type', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table courseinstructors.
        $table->add_key('instructorid', XMLDB_KEY_PRIMARY, array('instructorid'));
        $table->add_key('courseid', XMLDB_KEY_FOREIGN, array('courseid'), 'courseinfo', array('courseid'));

        // Conditionally launch create table for courseinstructors.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }



        // Define table courselbjectives to be created.
        $table = new xmldb_table('courselbjectives');

        // Adding fields to table courselbjectives.
        $table->add_field('objectiveid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('objectivename', XMLDB_TYPE_TEXT, null, null, null, null, null);

        // Adding keys to table courselbjectives.
        $table->add_key('objectiveid', XMLDB_KEY_PRIMARY, array('objectiveid'));

        // Conditionally launch create table for courselbjectives.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        
        
        
        // Define table sessionobjectives to be created.
        $table = new xmldb_table('sessionobjectives');

        // Adding fields to table sessionobjectives.
        $table->add_field('sessionid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('objectiveid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table sessionobjectives.
        $table->add_key('sessionid', XMLDB_KEY_FOREIGN, array('sessionid'), 'coursesession', array('sessionid'));
        $table->add_key('objectiveid', XMLDB_KEY_FOREIGN, array('objectiveid'), 'courselobjective', array('objectiveid'));

        // Conditionally launch create table for sessionobjectives.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        
        

        // Define table assessmentobjectives to be created.
        $table = new xmldb_table('assessmentobjectives');

        // Adding fields to table assessmentobjectives.
        $table->add_field('assessmentid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('objectiveid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table assessmentobjectives.
        $table->add_key('assessmentid', XMLDB_KEY_FOREIGN, array('assessmentid'), 'courseassessment', array('assessmentid'));
        $table->add_key('objectiveid', XMLDB_KEY_FOREIGN, array('objectiveid'), 'courselobjective', array('objectiveid'));

        // Conditionally launch create table for assessmentobjectives.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        
        
        // Define table coursetag to be created.
        $table = new xmldb_table('coursetag');

        // Adding fields to table coursetag.
        $table->add_field('tagid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table coursetag.
        $table->add_key('courseid', XMLDB_KEY_FOREIGN, array('courseid'), 'courseinfo', array('courseid'));
        $table->add_key('tagid', XMLDB_KEY_FOREIGN, array('tagid'), 'courselobjective', array('objectiveid'));

        // Conditionally launch create table for coursetag.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }               
    
                

        // Metadata savepoint reached.
        upgrade_plugin_savepoint(true, 2016021901, 'local', 'metadata');
    }

?>
