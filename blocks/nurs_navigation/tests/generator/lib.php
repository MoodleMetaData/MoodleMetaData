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

defined('MOODLE_INTERNAL') || die();

/**
 * block_nurs_navigation_generator class
 *
 * This class is used by the unit tests to create a nurs_navigation block.
 * There doesn't seem to be much in the way of docs, so I've largely copied
 * the version that sits in the online_users block.
 *
 * @package    block_nurs_navigation
 * @copyright  2014 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_nurs_navigation_generator extends testing_block_generator{

    public function create_instance($record = null, array $options = null) {
        global $DB, $CFG;

        $this->instancecount++;

        $record = (object)(array)$record;
        $options = (array)$options;

        $record = $this->prepare_record($record);

        $id = $DB->insert_record('block_instances', $record);

        $instance = $DB->get_record('block_instances', array('id' => $id), '*', MUST_EXIST);

        return $instance;
    }
}