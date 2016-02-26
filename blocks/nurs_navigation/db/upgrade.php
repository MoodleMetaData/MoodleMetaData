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

/**
 * This is the upgrade script for the project.
 *
 * @package    block_nurs_navigation
 * @category   block
 * @copyright  2012 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
function xmldb_block_nurs_navigation_upgrade($oldversion = 0) {
    global $DB;
    $dbman = $DB->get_manager();
    $result = true;

    // June 17, 2013 version changed the way that contexts are stored in the files table.
    if ($oldversion < 2013061706) {
        $query = "SELECT * FROM {nurs_navigation} WHERE courseid <> 1";
        $records = $DB->get_records_sql($query);
        $fs = get_file_storage();

        foreach ($records as $record) {
            $coursecontext = context_course::instance($record->courseid);
            // Explicit check here since sometimes there are old nurs_navigation records that point to deleted courses.
            if (isset($coursecontext->id)) {
                $params = array($coursecontext->id, 'nurs_navigation');
                $query = "SELECT * FROM {block_instances} WHERE parentcontextid = ? AND blockname = ?";
                $block = $DB->get_record_sql($query, $params, IGNORE_MULTIPLE);
                $blockcontext = context_block::instance($block->id);

                // This can return multiple records because of how the multiple file terminator works.
                $filerecords = $DB->get_records('files', array('contextid' => $coursecontext->id, 'itemid' => $record->fileid));
                foreach ($filerecords as $filerecord) {
                    $filerecord->contextid = $blockcontext->id;
                    // Path hash must be updated as well with a context change.
                    $filerecord->pathnamehash = $fs->get_pathname_hash($filerecord->contextid, BNN_BLOCK_SAVE_COMPONENT,
                            BNN_BLOCK_SAVE_AREA, $filerecord->itemid, $filerecord->filepath, $filerecord->filename);
                    $DB->update_record('files', $filerecord);
                }
            }
        }
    }

    // Create the second table.
    if ($oldversion < 2012110200) {

        // Define table nurs_navigation_settings to be created.
        $table = new xmldb_table('nurs_navigation_settings');

        // Adding fields to table nurs_navigation_settings.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('sectionname', XMLDB_TYPE_TEXT, 'big', null, null, null, null, null);
        $table->add_field('disableicon', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('customlabel', XMLDB_TYPE_TEXT, 'big', null, null, null, null);

        // Adding keys to table nurs_navigation_settings.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for nurs_navigation_settings.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define field disableicon to be dropped from nurs_navigation.
        $table = new xmldb_table('nurs_navigation');
        $field = new xmldb_field('disableicon');

        // Conditionally launch drop field courseid.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Update savepoint.
        upgrade_block_savepoint(true, 2012110200, 'nurs_navigation');
    }

    return $result;
}