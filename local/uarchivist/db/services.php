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
 * Web service local plugin template external functions and service definitions.
 *
 * @package    localuarchivist
 * @copyright  2011 Jerome Mouneyrac
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// We defined the web service functions to install.
$functions = array(
    'local_uarchivist_copy_content' => array(
        'classname' => 'local_uarchivist_external',
        'methodname' => 'copy_content',
        'classpath' => 'local/uarchivist/externallib.php',
        'description' => 'Copies content from one Course to another',
        'type' => 'read',
    ),
    'local_uarchivist_restore_course' => array(
        'classname' => 'local_uarchivist_external',
        'methodname' => 'restore_course',
        'classpath' => 'local/uarchivist/externallib.php',
        'description' => 'restores a course from backup file',
        'type' => 'read',
    )
);

// We define the services to install as pre-build services. A pre-build service is not editable by administrator.
$services = array(
    'UARCHIVIST' => array('functions' => array('local_uarchivist_restore_course', 'local_uarchivist_copy_content'),
        'restrictedusers' => 0, 'enabled' => 1)
);
