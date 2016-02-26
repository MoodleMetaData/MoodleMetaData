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


/**
 * This is the upgrade script for the project.
 *
 * @package    block_skills_group
 * @category   block
 * @copyright  2014 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_block_skills_group_upgrade($oldversion=0) {

    global $DB;
    $dbman = $DB->get_manager();
    $result = true;

    // Sept. 13, 2014 version added the skills_group_settings table.
    if ($oldversion < 2014091302) {
        $table = new xmldb_table('skills_group_settings');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('feedbackid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('groupingid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Update savepoint.
        upgrade_block_savepoint(true, 2014091302, 'skills_group');
    }
    // On Oct. 5th, 2014 I added the maxsize (for groups) setting.
    if ($oldversion < 2014100500) {
        $table = new xmldb_table('skills_group_settings');
        $field = new xmldb_field('maxsize', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, 0);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Update savepoint.
        upgrade_block_savepoint(true, 2014100500, 'skills_group');
    }
    // Later on Oct. 5th I added the other two tables: {skills_group, skills_group_student}.
    if ($oldversion < 2014100502) {
        $table = new xmldb_table('skills_group');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('groupid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('allowjoin', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        $table = new xmldb_table('skills_group_student');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('finalizegroup', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Update savepoint.
        upgrade_block_savepoint(true, 2014100502, 'skills_group');
    }

    // Nov. 11 2015 version1 added the fields for date.
    if ($oldversion < 2015111100) {
        $table = new xmldb_table('skills_group_settings');
        $field = new xmldb_field('date', XMLDB_TYPE_INTEGER, '20', null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Update savepoint.
        upgrade_block_savepoint(true, 2015111100, 'skills_group');
    }

    // Nov. 11 2015 version2 added the fields for threshold.
    if ($oldversion < 2015111101) {
        $table = new xmldb_table('skills_group_settings');
        $field = new xmldb_field('threshold', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, 1);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Update savepoint.
        upgrade_block_savepoint(true, 2015111101, 'skills_group');
    }

    // Nov. 19 2015 "01" added the fields for whether to allow naming.
    if ($oldversion < 2015111906) {
        $table = new xmldb_table('skills_group_settings');
        $field = new xmldb_field('allownaming', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 1);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Update savepoint.
        upgrade_block_savepoint(true, 2015111906, 'skills_group');
    }
    return $result;
}