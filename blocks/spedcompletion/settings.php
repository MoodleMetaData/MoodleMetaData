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
 * @package spedcompletion
 * @author Anthony Radziszewski radzisze@ualberta.ca
 **/

$settings->add(new admin_setting_configtextarea('sped_completion_webservice',
    get_string('webservice_url', 'block_spedcompletion'), get_string('webservice_description', 'block_spedcompletion'),
    get_string('webservice_default', 'block_spedcompletion'), PARAM_TEXT));
$settings->add(new admin_setting_configtextarea('sped_completion_presharedkey',
    get_string('presharedkey', 'block_spedcompletion'), get_string('presharedkey_description', 'block_spedcompletion'),
    get_string('presharedkey_default', 'block_spedcompletion'), PARAM_TEXT));