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
 * @package    block_course_message
 * @category   block
 * @copyright  2014 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_block_course_message_upgrade($oldversion=0) {

    global $DB;
    $dbman = $DB->get_manager();
    $result = true;

    // July 7, 2014 version added the carbon copy field.
    if ($oldversion < 2014070700) {
        $table = new xmldb_table('course_message_mails');
        $field = new xmldb_field('carboncopy', XMLDB_TYPE_TEXT, 'big', null, null, null, 'attachment');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Force cache purge.
        purge_all_caches();

        // Update savepoint.
        upgrade_block_savepoint(true, 2014062600, 'course_message');
    }

    return $result;
}