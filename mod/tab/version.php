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
 * Code fragment to define the version of tab
 * This fragment is called by moodle_needs_upgrading() and /admin/index.php
 *
 * @author : Patrick Thibaudeau
 * @author Joey Andres <jandres@ualberta.ca>
 * @package tab
 **/

$module->version  = 2015102200;  // The current module version (Date: YYYYMMDDXX) previously:2013070500.
$module->cron     = 0;           // Period for cron to check this module (secs).
