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
require_once(dirname(__FILE__).'/../../../config.php');
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/course/format/collblct/locallib.php');

/**
 * settings_edit_form class
 *
 * This class contains the form definition for the page used to edit the colors
 * for the course and to enable/disable the display of collapsed labels.
 *
 * @package    format_collblct
 * @copyright  2012 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class settings_edit_form extends moodleform {

    /** This is the course ID */
    private $courseid;

    /**
     * This method constructs a the form based on the number of sections, headers, and
     * the courseid.
     *
     * @param int $courseid The ID of the course to create the form for.
     *
     */
    public function __construct($courseid) {
        $this->courseid = $courseid;

        parent::__construct();
    }

    /**
     * Form definition: create the color editing controls and then create a checkbox for
     * each section to enable/disable the collapsed label display.
     *
     */
    public function definition() {
        global $DB;

        $mform = &$this->_form;

        $mform->addElement('header', 'colorheader', get_string('editcolorsheader', 'format_collblct'));
        $mform->registerRule('color', 'regex', '/^#([a-fA-F0-9]{6})$/');
        $mform->addElement('text', 'foregroundcolor', get_string('foregroundcolor', 'format_collblct'), null);
        $mform->setType('foregroundcolor', PARAM_TEXT);
        $mform->addRule('foregroundcolor', get_string('invalidcolor', FORMAT_CTWCL_LANG_TABLE), 'color');
        $mform->addElement('text', 'backgroundcolor', get_string('backgroundcolor', 'format_collblct'), null);
        $mform->setType('backgroundcolor', PARAM_TEXT);
        $mform->addRule('backgroundcolor', get_string('invalidcolor', FORMAT_CTWCL_LANG_TABLE), 'color');
        // Add button to return to defaults.
        $mform->addElement('checkbox', 'returndefault', get_string('returndefault', FORMAT_CTWCL_LANG_TABLE));

        $mform->addElement('header', 'courseheader', 'Enable collapsed labels by section');
        $sectionheaders = array();
        $numberofsections = ctwcl_get_section_titles($this->courseid, $sectionheaders);
        for ($i = 0; $i < $numberofsections; $i++) {
            $sectionfield = 'sectionfield_'."$i";
            $sectioncheckbox = 'sectioncheckbox_'."$i";

            $pagegroup = array();
            $pagegroup[] = $mform->createElement('advcheckbox', $sectioncheckbox, '', null, null, array(0, 1));
            $pagegroup[] = $mform->createElement('static', $sectionfield, null, $sectionheaders[$i]);
            $mform->addGroup($pagegroup, 'newlistbar'."$i", null, null, false);
        }

        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);

        $this->add_action_buttons();
    }
}