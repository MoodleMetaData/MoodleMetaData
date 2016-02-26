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
 * This file contains the version information and dependencies for the block.
 *
 * Pre 1.5 -> jQuery version of mail
 * Post 1.5 -> YUI version of mail
 *
 * @package    block_course_message
 * @category   block
 * @copyright  2012 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version            = 2015042900;
$plugin->component          = 'block_course_message';
$plugin->requires           = 2014110400;
$plugin->cron               = 0;
$plugin->maturity           = MATURITY_STABLE;
$plugin->release            = '1.7.0 (Build: 2015042900)';
$plugin->dependencies       = array('local_yuigallerylibs' => 2013080100);