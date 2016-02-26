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
 * This is the lib.php file for the project that contains the plugin file handler.
 *
 * @package    block_nurs_navigation
 * @category   block
 * @copyright  2012 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * This is method serves up files to the user.  The files used in this course are just images,
 * but they are sent largely the same way.
 *
 */
function block_nurs_navigation_pluginfile($course, $birecord, $context, $filearea, $args, $forcedownload) {

    require_once('lib/filelib.php');

    $fs = get_file_storage();

    $entryid = clean_param(array_shift($args), PARAM_INT);
    $file = array_shift($args);

    if (!$file = $fs->get_file($context->id, 'block_nurs_navigation', $filearea, $entryid, '/', $file)) {
        send_file_not_found();
        return;
    }

    send_stored_file($file, 10 * 60, 0, true);
}

/**
 * This is method tells moodle that backup is supported.
 *
 */
function block_nurs_navigation_supports($feature) {
    switch ($feature) {
        case FEATURE_BACKUP_MOODLE2:
            return true;
        default:
            return null;
    }
}