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
 * Certificate module data generator.
 *
 * @package    mod_iclickerregistration
 * @category   test
 * @author     Joey Andres <jandres@ualberta.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

defined('MOODLE_INTERNAL') || die();

class mod_iclickerregistration_generator extends testing_module_generator {
    /**
     * Create new certificate module instance
     * @param array|stdClass $record data for module being generated. Requires 'course' key
     *     (an id or the full object). Also can have any fields from add module form.
     * @param null|array $options general options for course module. Since 2.6 it is
     *     possible to omit this argument by merging options into $record
     * @return stdClass record from module-defined table with additional field
     *     cmid (corresponding id in course_modules table)
     */
    public function create_instance($record = null, array $options = null) {
        global $CFG;
        require_once("$CFG->dirroot/mod/iclickerregistration/lib.php");

        $this->instancecount;
        $i = $this->instancecount;

        $record = (object)(array)$record;
        $options = (array)$options;

        if (empty($record->course)) {
            throw new coding_exception('module generator requires $record->course');
        }

        $defaults = array();
        $defaults['name'] = get_string('pluginname', 'iclickerregistration');
        $defaults['intro'] = 'Test certificate '.$i;
        $defaults['introformat'] = FORMAT_MOODLE;

        foreach ($defaults as $field => $value) {
            if (!isset($record->$field)) {
                $record->$field = $value;
            }
        }

        if (isset($options['idnumber'])) {
            $record->cmidnumber = $options['idnumber'];
        } else {
            $record->cmidnumber = '';
        }

        // Do work to actually add the instance.
        return parent::create_instance($record, (array)$options);
    }
}