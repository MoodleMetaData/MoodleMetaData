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
 * Configuration page for setting course start/end dates
 *
 * @package    block
 * @subpackage eclass course management
 * @author     Trevor Jones tdjones@ualberta.ca
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once("$CFG->libdir/formslib.php");

class course_management_form extends moodleform
{

    protected $_start;
    protected $_end;

    public function __construct($start = null, $end = null, $action = null, $customdata = null, $method = 'post',
                                           $target = '', $attributes = null, $editable = true) {
        $this->_start = $start;
        $this->_end = $end;

        parent::__construct($action, $customdata, $method, $target, $attributes, $editable);
    }

    public function definition() {
        global $CFG;

        $mform = $this->_form;

        $mform->addElement('header', 'title', get_string('configurationtitle', 'block_eclass_course_management'));

        if (isset($this->_customdata['visibility'])) {
            if ($this->_customdata['visibility']) {
                $status = array('Open');
                $mform->addElement('select', 'visibility', get_string('status_label', 'block_eclass_course_management'), $status,
                    array('disabled' => 'disabled'));
            } else {
                $status = array('Closed');
                $mform->addElement('select', 'visibility', get_string('status_label', 'block_eclass_course_management'), $status,
                    array('disabled' => 'disabled'));
            }

        }

        $mform->addElement('static', 'info', get_string('forminstructions', 'block_eclass_course_management'));
        $mform->addElement('date_selector', 'start', get_string('open_label', 'block_eclass_course_management'),
            array());
        $mform->setType('start', PARAM_INT);
        if ($this->_start) {
            $mform->setDefault('start', $this->_start);
        }

        $mform->addElement('date_selector', 'end', get_string('close_label', 'block_eclass_course_management'));
        $mform->setType('end', PARAM_INT);
        if ($this->_end) {
            $mform->setDefault('end', $this->_end);
        }

        if (isset($this->_customdata['course'])) {
            $mform->addElement('hidden', 'course');
            $mform->setDefault('course', $this->_customdata['course']);
        }

        $this->add_action_buttons(true);
    }

    // Custom validation should be added here.
    public function validation($data, $files) {
        return array();
    }
}