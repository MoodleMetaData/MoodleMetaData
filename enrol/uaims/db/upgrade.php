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

defined('MOODLE_INTERNAL') || die();

function xmldb_enrol_uaims_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2014110600) {

        // Define table eclass_course_management to be created.
        $table = new xmldb_table('eclass_course_management');

        // Adding fields to table eclass_course_management.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('startdate', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('enddate', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('lastopened', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('lastclosed', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table eclass_course_management.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for eclass_course_management.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Uaims savepoint reached.
        upgrade_plugin_savepoint(true, 2014110600, 'enrol', 'uaims');
    }

    if ($oldversion < 2015011200) {

        // Add new indicies to the eclass_course_management table.
        $table = new xmldb_table('eclass_course_management');
        $index = new xmldb_index('courseid', XMLDB_INDEX_UNIQUE, array('courseid'));
        $index2 = new xmldb_index('lastopenedstartdate', XMLDB_INDEX_NOTUNIQUE, array('lastopened', 'startdate'));
        $index3 = new xmldb_index('lastclosedenddate', XMLDB_INDEX_NOTUNIQUE, array('lastclosed', 'enddate'));

        // Conditionally launch add index courseid.
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        if (!$dbman->index_exists($table, $index2)) {
            $dbman->add_index($table, $index2);
        }
        if (!$dbman->index_exists($table, $index3)) {
            $dbman->add_index($table, $index3);
        }

        // Uaims savepoint reached.
        upgrade_plugin_savepoint(true, 2015011200, 'enrol', 'uaims');
    }

    return true;
}
