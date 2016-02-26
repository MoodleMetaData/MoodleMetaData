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
require_once($CFG->dirroot.'/blocks/nurs_navigation/locallib.php');

/**
 * Nurs Navigation block backup class for defining steps.  There are
 * two classes contained in this file.  One for the main table and then
 * another for the settings table.
 *
 * @package    block_nurs_navigation
 * @copyright  2013 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_nurs_navigation_block_structure_step extends backup_block_structure_step {

    /**
     * This method creates the backup element and ensures that all
     * course related records existing in the database are stored to
     * be used later in the restore process.  There are a number of
     * other optional steps, but none were required.
     *
     */
    protected function define_structure() {
        $nursnavigation = new backup_nested_element('nurs_navigation', array('id'), array('courseid', 'fileid', 'sectionname'));
        $nursnavigation->set_source_table('nurs_navigation', array('courseid' => backup::VAR_COURSEID));
        // No context needed (optional 4th parameter), default block context used.
        $nursnavigation->annotate_files(BNN_BLOCK_SAVE_COMPONENT, BNN_BLOCK_SAVE_AREA, null);
        return $this->prepare_block_structure($nursnavigation);
    }
}

/**
 * Backup class defining steps for the settings table.  It's quite
 * similar to the previous class with one main exception (no files).
 *
 * @package    block_nurs_navigation
 * @copyright  2013 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_nurs_navigation_block_structure_step_settings extends backup_block_structure_step {

    /**
     * The settings table is a bit simpler, since no files are used.
     * Simply create the backup element and then grab the information
     * from the database.
     *
     */
    protected function define_structure() {
        // Notice the use of 'setting' here rather than 'settings', this is how it gets written to the XML file.
        $nursnavigationsettings = new backup_nested_element('nurs_navigation_setting', array('id'),
                                  array('courseid', 'sectionname', 'disableicon', 'customlabel'));
        $nursnavigationsettings->set_source_table('nurs_navigation_settings', array('courseid' => backup::VAR_COURSEID));
        return $this->prepare_block_structure($nursnavigationsettings);
    }
}