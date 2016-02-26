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
 * This is the lib.php file for the project.  The plugin_file() function is
 * important in order to let the user download files.  The other function
 * is probably unused.
 *
 * @package    block_course_message
 * @category   block
 * @copyright  Author unknown
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/** component name in {files} table */
define('BLOCK_CM_COMPONENT_NAME', 'block_course_message');
/** area name to save files under in {files} table */
define('BLOCK_CM_FILE_AREA_NAME', 'attachment');
/** time for file to remain in cache - default for Moodle 2.23 */
define('BLOCK_CM_LIFETIME', 86400);

function block_course_message_pluginfile($course, $birecord, $context, $filearea, $args, $forcedownload) {
    require_once('lib/filelib.php');

    $fs = get_file_storage();

    $context = context_course::instance($course->id);

    $entryid = clean_param(array_shift($args), PARAM_INT);
    $file = array_shift($args);

    if (!$file = $fs->get_file($context->id, 'block_course_message', $filearea, $entryid, '/', $file)) {
        send_file_not_found();
        return;
    }
    // Fourth parameter forces the user to download the file.
    send_stored_file($file, BLOCK_CM_LIFETIME, 0, $forcedownload);
}