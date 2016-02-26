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
 * @package eclass-local-contextadmin
 * @author joshstagg
 * @copyright Josh Stagg
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
function xmldb_local_contextadmin_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2013100813) {

        // Adding fields to table.
        $table = new xmldb_table('cat_role_names');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);
        $table->add_field('roleid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'id');
        $table->add_field('catid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'roleid');
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'catid');

        // Add keys to table.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('roleid', XMLDB_KEY_FOREIGN, array('roleid'), 'role', array('id'));
        $table->add_key('catid', XMLDB_KEY_FOREIGN, array('catid'), 'course_categories', array('id'));

        // Add indices.
        $table->add_index('roleid-catid', XMLDB_INDEX_UNIQUE, array('roleid', 'catid'));

        // Conditionally launch add table.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
            // Main savepoint reached.
            upgrade_plugin_savepoint(true, 2013100813, 'local', 'contextadmin');
        }
    }
}
