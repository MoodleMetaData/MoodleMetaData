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
 * This file keeps track of upgrades to the spedcompletion status block
 *
 * Sometimes, changes between versions involve alterations to database structures
 * and other major things that may break installations.
 *
 * The upgrade function in this file will attempt to perform all the necessary
 * actions to upgrade your older installation to the current version.
 *
 * If there's something it cannot do itself, it will tell you what you need to do.
 *
 * The commands in here will all be database-neutral, using the methods of
 * database_manager class
 *
 * Please do not forget to use upgrade_set_timeout()
 * before any action that may take longer time to finish.
 *
 * @since 2.6
 * @package blocks
 * @author Anthony Radziszewski radzisze@ualberta.ca
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Handles upgrading instances of this block.
 *
 * @param int $oldversion
 * @param object $block
 */
function xmldb_block_spedcompletion_upgrade($oldversion, $block) {
    global $DB;

    // Moodle v2.4.0 release upgrade line
    // Put any upgrade step following this.

    // Moodle v2.5.0 release upgrade line.
    // Put any upgrade step following this.

    // Moodle v2.6.0 release upgrade line.
    // Put any upgrade step following this.

    if ($oldversion < 2014090900) {
        // Savepoint reached.
        upgrade_block_savepoint(true, 2014090900, 'spedcompletion');
    }

    if ($oldversion < 2014100700) {
        $dbman = $DB->get_manager();
        $index = new xmldb_index('completionsent', XMLDB_INDEX_NOTUNIQUE, array('completionsent'));
        $table = new xmldb_table('spedcompletion');

        // Conditionally launch drop index completionsent.
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }

        // Define field completionsent to be dropped from spedcompletion.

        $field = new xmldb_field('completionsent');

        // Conditionally launch drop field completionsent.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        $field = new xmldb_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'userid');

        // Launch rename field courseid.
        $dbman->rename_field($table, $field, 'course');

        $index = new xmldb_index('course', XMLDB_INDEX_NOTUNIQUE, array('course'));

        // Conditionally launch add index completionsent.
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        $index = new xmldb_index('userid', XMLDB_INDEX_NOTUNIQUE, array('userid'));
        // Conditionally launch add index completionsent.
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Savepoint reached.
        upgrade_block_savepoint(true, 2014100700, 'spedcompletion');
    }

    return true;
}