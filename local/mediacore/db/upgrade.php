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
 *       __  _____________   _______   __________  ____  ______
 *      /  |/  / ____/ __ \ /  _/   | / ____/ __ \/ __ \/ ____/
 *     / /|_/ / __/ / / / / / // /| |/ /   / / / / /_/ / __/
 *    / /  / / /___/ /_/ /_/ // ___ / /___/ /_/ / _, _/ /___
 *   /_/  /_/_____/_____//___/_/  |_\____/\____/_/ |_/_____/
 *
 * MediaCore's local plugin
 *
 * @package    local
 * @subpackage mediacore
 * @copyright  2012 MediaCore Technologies
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

function xmldb_local_mediacore_upgrade($oldversion) {
    global $DB;

    // Get the old 'url' setting, if there is one...
    $old_record = $DB->get_record('config_plugins',
            array('plugin'=>'local_mediacore', 'name'=>'url'));

    // Replace the old 'url' setting with a new 'host' setting
    // that includes only the hostname and port.
    if ($old_record) {
        $hostname = parse_url($old_record->value, PHP_URL_HOST);
        $port = parse_url($old_record->value, PHP_URL_PORT);
        $host = $hostname;
        if ($port) {
            $host .= ':' . $port;
        }
        $new_record = new stdClass();
        $new_record->plugin = 'local_mediacore';
        $new_record->name = 'host';
        $new_record->value = $host;

        $DB->insert_record('config_plugins', $new_record, false);
        $DB->delete_records('config_plugins',
                array('plugin' => 'local_mediacore', 'name' => 'url'));
    }
    return true;
}
