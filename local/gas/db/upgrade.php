<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
//
// Author: Behdad Bakhshinategh!

defined('MOODLE_INTERNAL') || die();

function xmldb_local_gas_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2015061002) {

        // Define table local_gas_semesters to be created.
        $table = new xmldb_table('local_gas_semesters');

        // Adding fields to table local_gas_semesters.
        $table->add_field('semester', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('startmonth', XMLDB_TYPE_INTEGER, '20', null, null, null, null);
        $table->add_field('startday', XMLDB_TYPE_INTEGER, '20', null, null, null, null);
        $table->add_field('endmonth', XMLDB_TYPE_INTEGER, '20', null, null, null, null);
        $table->add_field('endday', XMLDB_TYPE_INTEGER, '20', null, null, null, null);

        // Adding keys to table local_gas_semesters.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('semester'));

        // Conditionally launch create table for local_gas_semesters.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Gas savepoint reached.
        upgrade_plugin_savepoint(true, 2015061002, 'local', 'gas');
    }
    if ($oldversion < 2015072201) {

        // Define table local_gas_attributes to be created.
        $table = new xmldb_table('local_gas_attributes');

        // Adding fields to table local_gas_attributes.
        $table->add_field('attribute_id', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('timestamp', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('exp_time', XMLDB_TYPE_INTEGER, '20', null, null, null, null);

        // Adding keys to table local_gas_attributes.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('attribute_id'));

        // Conditionally launch create table for local_gas_attributes.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Gas savepoint reached.
        upgrade_plugin_savepoint(true, 2015072201, 'local', 'gas');
    }

    if ($oldversion < 2015072203) {

        // Define table local_gas_subattributes to be created.
        $table = new xmldb_table('local_gas_subattributes');

        // Adding fields to table local_gas_subattributes.
        $table->add_field('subattribute_id', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('attribute_id', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timestamp', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('exp_time', XMLDB_TYPE_INTEGER, '20', null, null, null, null);

        // Adding keys to table local_gas_subattributes.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('subattribute_id'));
        $table->add_key('foreign', XMLDB_KEY_FOREIGN, array('attribute_id'), 'local_gas_attributes', array('attribute_id'));

        // Conditionally launch create table for local_gas_subattributes.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Gas savepoint reached.
        upgrade_plugin_savepoint(true, 2015072203, 'local', 'gas');
    }
    if ($oldversion < 2015072401) {

        // Define table local_gas_attributes_names to be created.
        $table = new xmldb_table('local_gas_attributes_names');

        // Adding fields to table local_gas_attributes_names.
        $table->add_field('attribute_id', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('lang', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
        $table->add_field('id', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);

        // Adding keys to table local_gas_attributes_names.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for local_gas_attributes_names.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Gas savepoint reached.
        upgrade_plugin_savepoint(true, 2015072401, 'local', 'gas');
    }
    if ($oldversion < 2015072700) {

        // Define table local_gas_subattributes_name to be created.
        $table = new xmldb_table('local_gas_subattributes_name');

        // Adding fields to table local_gas_subattributes_name.
        $table->add_field('subattribute_id', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('lang', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
        $table->add_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('description1', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('description2', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('description3', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('description4', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('description5', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('id', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);

        // Adding keys to table local_gas_subattributes_name.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('foreign', XMLDB_KEY_FOREIGN, array('subattribute_id'), 'local_gas_subattributes',
                array('subattribute_id'));

        // Conditionally launch create table for local_gas_subattributes_name.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Gas savepoint reached.
        upgrade_plugin_savepoint(true, 2015072700, 'local', 'gas');
    }
    if ($oldversion < 2015080600) {

        // Define table local_gas_assessment to be created.
        $table = new xmldb_table('local_gas_assessment');

        // Adding fields to table local_gas_assessment.
        $table->add_field('assessment_id', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('student_id', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timestamp', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('semester', XMLDB_TYPE_CHAR, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timetaken', XMLDB_TYPE_INTEGER, '20', null, null, null, null);

        // Adding keys to table local_gas_assessment.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('assessment_id'));

        // Conditionally launch create table for local_gas_assessment.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Gas savepoint reached.
        upgrade_plugin_savepoint(true, 2015080600, 'local', 'gas');
    }
    if ($oldversion < 2015080601) {

        // Define table local_gas_subatt_assessment to be created.
        $table = new xmldb_table('local_gas_subatt_assessment');

        // Adding fields to table local_gas_subatt_assessment.
        $table->add_field('subatt_assessment_id', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('subattribute_id', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('assessment_id', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('value', XMLDB_TYPE_NUMBER, '10, 9', null, null, null, null);

        // Adding keys to table local_gas_subatt_assessment.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('subatt_assessment_id'));
        $table->add_key('foreign', XMLDB_KEY_FOREIGN, array('assessment_id'), 'local_gas_assessment', array('assessment_id'));
        $table->add_key('foreign2', XMLDB_KEY_FOREIGN, array('subattribute_id'), 'local_gas_subattributes',
                array('subattribute_id'));

        // Conditionally launch create table for local_gas_subatt_assessment.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Gas savepoint reached.
        upgrade_plugin_savepoint(true, 2015080601, 'local', 'gas');
    }
    if ($oldversion < 2015080602) {

        // Define table local_gas_contributed_course to be created.
        $table = new xmldb_table('local_gas_contributed_course');

        // Adding fields to table local_gas_contributed_course.
        $table->add_field('course_contribution_id', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('subatt_assessment_id', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('course_id', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('value', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table local_gas_contributed_course.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('course_contribution_id'));
        $table->add_key('foreign', XMLDB_KEY_FOREIGN, array('subatt_assessment_id'), 'local_gas_subatt_assessment',
                array('subatt_assessment_id'));

        // Conditionally launch create table for local_gas_contributed_course.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Gas savepoint reached.
        upgrade_plugin_savepoint(true, 2015080602, 'local', 'gas');
    }
    if ($oldversion < 2015082600) {

        // Define table local_gas_course_assessment to be created.
        $table = new xmldb_table('local_gas_course_assessment');

        // Adding fields to table local_gas_course_assessment.
        $table->add_field('cassessment_id', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('user_id', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timestamp', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timetaken', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('semester', XMLDB_TYPE_CHAR, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table local_gas_course_assessment.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('cassessment_id'));

        // Conditionally launch create table for local_gas_course_assessment.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Gas savepoint reached.
        upgrade_plugin_savepoint(true, 2015082600, 'local', 'gas');
    }
    if ($oldversion < 2015082601) {

        // Define table local_gas_subatt_cassessment to be created.
        $table = new xmldb_table('local_gas_subatt_cassessment');

        // Adding fields to table local_gas_subatt_cassessment.
        $table->add_field('subatt_cassessment_id', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('subattribute_id', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('cassessment_id', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('value', XMLDB_TYPE_NUMBER, '10, 9', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table local_gas_subatt_cassessment.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('subatt_cassessment_id'));
        $table->add_key('foreign', XMLDB_KEY_FOREIGN, array('cassessment_id'), 'local_gas_course_assessment',
                array('cassessment_id'));
        $table->add_key('foreign2', XMLDB_KEY_FOREIGN, array('subattribute_id'), 'local_gas_subattributes',
                array('subattribute_id'));

        // Conditionally launch create table for local_gas_subatt_cassessment.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Gas savepoint reached.
        upgrade_plugin_savepoint(true, 2015082601, 'local', 'gas');
    }
    if ($oldversion < 2015082602) {

        // Define field course_id to be added to local_gas_course_assessment.
        $table = new xmldb_table('local_gas_course_assessment');
        $field = new xmldb_field('course_id', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null, 'semester');

        // Conditionally launch add field course_id.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Gas savepoint reached.
        upgrade_plugin_savepoint(true, 2015082602, 'local', 'gas');
    }
    if ($oldversion < 2015111201) {

        // Define table local_gas_student_survey to be created.
        $table = new xmldb_table('local_gas_student_survey');

        // Adding fields to table local_gas_student_survey.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('gender', XMLDB_TYPE_CHAR, '20', null, null, null, null);
        $table->add_field('age', XMLDB_TYPE_INTEGER, '3', null, null, null, null);
        $table->add_field('year_of_study', XMLDB_TYPE_INTEGER, '2', null, null, null, null);
        $table->add_field('has_post_secondary_education', XMLDB_TYPE_INTEGER, '1', null, null, null, null);
        $table->add_field('institution', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('area_of_study', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('num_of_years', XMLDB_TYPE_INTEGER, '2', null, null, null, null);
        $table->add_field('has_cer_dip_deg', XMLDB_TYPE_INTEGER, '1', null, null, null, null);
        $table->add_field('has_certificate', XMLDB_TYPE_INTEGER, '1', null, null, null, null);
        $table->add_field('has_diploma', XMLDB_TYPE_INTEGER, '1', null, null, null, null);
        $table->add_field('has_degree', XMLDB_TYPE_INTEGER, '1', null, null, null, null);
        $table->add_field('other_cer_dip_deg', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('lives_on_campus', XMLDB_TYPE_INTEGER, '1', null, null, null, null);
        $table->add_field('is_international_student', XMLDB_TYPE_INTEGER, '1', null, null, null, null);
        $table->add_field('country', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('faculty', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('department', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('other_department', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('major', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('minor', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('pursuing_certificate', XMLDB_TYPE_CHAR, '10', null, null, null, null);
        $table->add_field('certificate', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('activity1', XMLDB_TYPE_INTEGER, '1', null, null, null, null);
        $table->add_field('activity2', XMLDB_TYPE_INTEGER, '1', null, null, null, null);
        $table->add_field('activity3', XMLDB_TYPE_INTEGER, '1', null, null, null, null);
        $table->add_field('activity4', XMLDB_TYPE_INTEGER, '1', null, null, null, null);
        $table->add_field('activity5', XMLDB_TYPE_INTEGER, '1', null, null, null, null);
        $table->add_field('activity6', XMLDB_TYPE_INTEGER, '1', null, null, null, null);
        $table->add_field('activity7', XMLDB_TYPE_INTEGER, '1', null, null, null, null);
        $table->add_field('no_activity', XMLDB_TYPE_INTEGER, '1', null, null, null, null);
        $table->add_field('why_no_activity', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('has_other_activity', XMLDB_TYPE_INTEGER, '1', null, null, null, null);
        $table->add_field('other_activity', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('hours_of_activity', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('hours_of_activity_text', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('hours_of_study', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('hours_of_study_text', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('reason_of_participation', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('gains_of_participation', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('student_id', XMLDB_TYPE_INTEGER, '20', null, null, null, null);
        $table->add_field('timestamp', XMLDB_TYPE_INTEGER, '20', null, null, null, null);

        // Adding keys to table local_gas_student_survey.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for local_gas_student_survey.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Gas savepoint reached.
        upgrade_plugin_savepoint(true, 2015111201, 'local', 'gas');
    }
    if ($oldversion < 2015111601) {

        // Define table local_gas_instructor_survey to be created.
        $table = new xmldb_table('local_gas_instructor_survey');

        // Adding fields to table local_gas_instructor_survey.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('gender', XMLDB_TYPE_CHAR, '20', null, null, null, null);
        $table->add_field('position', XMLDB_TYPE_CHAR, '30', null, null, null, null);
        $table->add_field('otherporition', XMLDB_TYPE_CHAR, '40', null, null, null, null);
        $table->add_field('faculty', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('subject', XMLDB_TYPE_CHAR, '100', null, null, null, null);
        $table->add_field('years_of_teaching', XMLDB_TYPE_INTEGER, '3', null, null, null, null);
        $table->add_field('discipline', XMLDB_TYPE_CHAR, '30', null, null, null, null);
        $table->add_field('expand_on_answers', XMLDB_TYPE_TEXT, null, null, null, null, null);

        // Adding keys to table local_gas_instructor_survey.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for local_gas_instructor_survey.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Gas savepoint reached.
        upgrade_plugin_savepoint(true, 2015111601, 'local', 'gas');
    }
    if ($oldversion < 2015111602) {

        // Define field student_id to be added to local_gas_instructor_survey.
        $table = new xmldb_table('local_gas_instructor_survey');
        $field = new xmldb_field('student_id', XMLDB_TYPE_INTEGER, '20', null, null, null, null, 'expand_on_answers');

        // Conditionally launch add field student_id.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field2 = new xmldb_field('timestamp', XMLDB_TYPE_INTEGER, '20', null, null, null, null, 'student_id');

        // Conditionally launch add field timestamp.
        if (!$dbman->field_exists($table, $field2)) {
            $dbman->add_field($table, $field2);
        }

        // Gas savepoint reached.
        upgrade_plugin_savepoint(true, 2015111602, 'local', 'gas');
    }
    if ($oldversion < 2015111603) {

        // Rename field user_id on table local_gas_instructor_survey to NEWNAMEGOESHERE.
        $table = new xmldb_table('local_gas_instructor_survey');
        $field = new xmldb_field('student_id', XMLDB_TYPE_INTEGER, '20', null, null, null, null, 'expand_on_answers');

        // Launch rename field user_id.
        $dbman->rename_field($table, $field, 'user_id');

        // Gas savepoint reached.
        upgrade_plugin_savepoint(true, 2015111603, 'local', 'gas');
    }
    if ($oldversion < 2015120801) {

        $table1 = new xmldb_table('local_gas_attributes');
        $field1 = new xmldb_field('attribute_id', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);
        $dbman->rename_field($table1, $field1, 'id');

        $table2 = new xmldb_table('local_gas_subattributes');
        $field2 = new xmldb_field('subattribute_id', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);
        $dbman->rename_field($table2, $field2, 'id');

        $table3 = new xmldb_table('local_gas_assessment');
        $field3 = new xmldb_field('assessment_id', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);
        $dbman->rename_field($table3, $field3, 'id');

        $table4 = new xmldb_table('local_gas_subatt_assessment');
        $field4 = new xmldb_field('subatt_assessment_id', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, XMLDB_SEQUENCE,
                null, null);
        $dbman->rename_field($table4, $field4, 'id');

        $table5 = new xmldb_table('local_gas_contributed_course');
        $field5 = new xmldb_field('course_contribution_id', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, XMLDB_SEQUENCE,
                null, null);
        $dbman->rename_field($table5, $field5, 'id');

        $table6 = new xmldb_table('local_gas_course_assessment');
        $field6 = new xmldb_field('cassessment_id', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);
        $dbman->rename_field($table6, $field6, 'id');

        $table7 = new xmldb_table('local_gas_subatt_cassessment');
        $field7 = new xmldb_field('subatt_cassessment_id', XMLDB_TYPE_INTEGER, '20', null,
                XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);
        $dbman->rename_field($table7, $field7, 'id');

        upgrade_plugin_savepoint(true, 2015120801, 'local', 'gas');
    }
    if ($oldversion < 2015120901) {

        // Define table local_gas_users to be created.
        $table = new xmldb_table('local_gas_users');

        // Adding fields to table local_gas_users.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('user_id', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('email', XMLDB_TYPE_CHAR, '40', null, null, null, null);
        $table->add_field('timestamp', XMLDB_TYPE_INTEGER, '20', null, null, null, null);

        // Adding keys to table local_gas_users.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for local_gas_users.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Gas savepoint reached.
        upgrade_plugin_savepoint(true, 2015120901, 'local', 'gas');
    }
    if ($oldversion < 2015121501) {

        // Define table local_gas_activeterm to be created.
        $table = new xmldb_table('local_gas_activeterm');

        // Adding fields to table local_gas_activeterm.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('term_id', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

        // Adding keys to table local_gas_activeterm.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for local_gas_activeterm.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Gas savepoint reached.
        upgrade_plugin_savepoint(true, 2015121501, 'local', 'gas');
    }
    return true;
}
