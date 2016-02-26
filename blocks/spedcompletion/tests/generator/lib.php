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
 * block_spedcompletion data generator
 *
 * @package    block_spedcompletion
 * @category   test
 * @copyright  2014 Trevor Jones
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class block_spedcompletion_generator extends testing_block_generator {

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
        $config = (object)(array)$config;

        $record = $this->prepare_record($record);

        if (!isset($config->sped_version)) {
            $config->sped_version = 0;
        }

        $record->configdata = base64_encode(serialize($config));
        $id = $DB->insert_record('block_instances', $record);
        $context = context_block::instance($id);

        $instance = $DB->get_record('block_instances', array('id' => $id), '*', MUST_EXIST);

        return $instance;
    }
}
