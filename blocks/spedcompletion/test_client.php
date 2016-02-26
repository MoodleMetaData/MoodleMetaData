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

define('MOODLE_INTERNAL', true);

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
global $CFG;
require_once("{$CFG->dirroot}/blocks/spedcompletion/lib/sped_service.php");
require_login();
require_capability('moodle/site:config', context_system::instance());

$ccid = required_param("ccid", PARAM_ALPHANUM);
$version = required_param("version", PARAM_ALPHANUM);

$sped = new Sped($version, get_config('', 'sped_completion_presharedkey'),
    get_config('', 'sped_completion_webservice'));
if ($sped->post_update($ccid)) {
    echo "Successfully updated record for user $ccid";
} else {
    echo "Failed to update record for user $ccid: {$sped->get_message()}";
}
