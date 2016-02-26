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
 * @package    localwstemplate
 * @copyright  2011 Jerome Mouneyrac
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// We defined the web service functions to install.
$functions = array(
        'local_ws_update_course_category' => array(
                'classname'   => 'local_ws_update_course_category_external',
                'methodname'  => 'update_course_category',
                'classpath'   => 'local/ws_update_course_category/externallib.php',
                'description' => 'Move given courseid to given categoryid.',
                'type'        => 'write',
        ),
);

// We define the services to install as pre-build services.
// A pre-build service is not editable by administrator.
$services = array(
        'Move Course to Category Service' => array(
                'functions' => array ('local_ws_update_course_category'),
                'requiredcapability' => 'local/webservice:local_ws_update_course_category',
                'restrictedusers' => 0,
                'enabled' => 1,
        )
);
