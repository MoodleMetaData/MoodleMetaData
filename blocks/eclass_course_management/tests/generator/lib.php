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
 * block_course_management data generator
 *
 * @package    block_course_management
 * @category   test
 * @copyright  2014 Trevor Jones
 * @copyright  Joey Andres <jandres@ualberta.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class block_eclass_course_management_generator extends testing_block_generator {

    /**
     * Create new block instance
     * @param array|stdClass $record
     * @param array $config Instance configurations
     * @return stdClass instance record
     */
    public function create_instance($record = null, array $config = null) {
        global $DB, $CFG;
        require_once("$CFG->dirroot/mod/page/locallib.php");

        $this->instancecount++;

        $record = (object)(array)$record;
        $record = $this->prepare_record($record);

        $id = $DB->insert_record('block_instances', $record);

        $instance = $DB->get_record('block_instances', array('id' => $id), '*', MUST_EXIST);

        return $instance;
    }
}
