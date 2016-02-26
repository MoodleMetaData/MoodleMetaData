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
 * Created by IntelliJ IDEA.
 * User: tdjones
 * Date: 12-01-24
 * Time: 1:07 PM
 * To change this template use File | Settings | File Templates.
 */
header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1.
header('Pragma: no-cache'); // HTTP 1.0.
header('Expires: 0'); // Proxies.

define("MOODLE_INTERNAL", true);
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
$courseid = required_param("cid", PARAM_INT);

if (!isloggedin() || !empty($SESSION->has_timed_out)) {
    if (!empty($SESSION->has_timed_out)) {
        unset($SESSION->has_timed_out);
    }
    $USER = $guest = get_complete_user_data('username', 'guest');
    complete_user_login($guest);
    set_moodle_cookie($guest->username);
}

redirect(new moodle_url($CFG->httpswwwroot.'/course/view.php', array('id' => $courseid)));
