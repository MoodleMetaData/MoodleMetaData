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
 *
 *
 * @version $Id$
 * @copyright 2011
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');

$site = get_site();

global $CFG, $DB;
require_once('lib.php');

$enrol = new enrol_uaims_plugin();

if (md5($_POST['imsdoc'].$_POST['timestamp'].$CFG->uaimssecret ) == $_POST['mac']) {
    $enrol->process_imsdoc($_POST['imsdoc']);
} else {
    echo 'Invalid Mac.';
}
