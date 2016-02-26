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
require_once(dirname(__FILE__).'/../../../../config.php');
global $CFG;
require_once($CFG->dirroot.'/blocks/nurs_navigation/backup/moodle2/restore_nurs_navigation_stepslib.php');
require_once($CFG->dirroot.'/blocks/nurs_navigation/locallib.php');

/**
 * Nurs Navigation block restore class - executes the restore steps.
 *
 * @package    block_nurs_navigation
 * @copyright  2013 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_nurs_navigation_block_task extends restore_block_task {

    /**
     * The block has no "setings".
     *
     */
    protected function define_my_settings() {
        // No particular settings for this activity.
    }

    /**
     * Setup the two restore steps: the names of the xml files must
     * match those that are used in the backup step.
     *
     */
    protected function define_my_steps() {
        $this->add_step(new restore_nurs_navigation_block_structure_step('nurs_navigation_structure', 'nurs_navigation.xml'));
        $this->add_step(new restore_nurs_navigation_settings_block_structure_step('nurs_navigation_settings_structure',
                        'nurs_navigation_settings.xml'));
    }

    /**
     * If any content was run through the encoder, it needs to be
     * decoded here.  Unlike backup, this seems to be a two step
     * process where the rules are setup in the method below.
     *
     * @return array Empty array
     *
     */
    static public function define_decode_contents() {
        // Empty -> no decoding needed.
        return array();
    }

    /**
     * Define the decoding rules for links belonging
     * to the activity to be executed by the link decoder
     *
     * @return array Empty array
     *
     */
    static public function define_decode_rules() {
        // Empty -> no decoding rules.
        return array();
    }

    /**
     * This method instructs the restore process which file areas to
     * look in for files.
     *
     * @return array Array containing file areas belonging to the block
     *
     */
    public function get_fileareas() {
        return array(BNN_BLOCK_SAVE_AREA);
    }

    /**
     * This method is required by the base class definition (abstract).
     * I'm not sure why it is also a 'getter'.  Returns empy array since
     * there are no instanced block settings to restore.
     *
     * @return array Empty array
     *
     */
    public function get_configdata_encoded_attributes() {
        return array();
    }
}