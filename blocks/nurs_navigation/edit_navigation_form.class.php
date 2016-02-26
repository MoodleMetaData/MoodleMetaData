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
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/blocks/nurs_navigation/lib.php');
require_once('section_icon.php');

/**
 * edit_navigation_form class
 *
 * This class contains the form definition for the page used to edit the icons
 * for each section.
 *
 * @package    block_nurs_navigation
 * @copyright  2012 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class edit_navigation_form extends moodleform {

    /** This is the number of sections in the course. */
    private $numberofsections;
    /** This array of strings contains the section headers. */
    private $sectionheaders;

    /**
     * This method constructs a the form based on the number of sections, headers, and
     * the courseid.
     *
     * @param int $numberofsections The total number of sections in the course.
     * @param array $sectionheaders Array of strings containing the headers for all of the sections.
     * @param int $courseid The ID of the course to create the form for.
     *
     */
    public function __construct($numberofsections, $sectionheaders, $courseid) {

        $this->numberofsections = $numberofsections;
        $this->sectionheaders = $sectionheaders;
        $this->courseid = $courseid;

        parent::__construct();
    }

    /**
     * Form definition: creates the form by creating a new segment for every section.  Each
     * segment contains the ID, section header, a picture of the old image, the filemanager
     * element, and two checkboxes for controls.  I also store the blockid and courseid at
     * the bottom of the page so that posting works properly.
     *
     */
    public function definition() {
        global $DB;

        $mform = &$this->_form;

        for ($i = 0; $i < $this->numberofsections; $i++) {
            $headername = 'displayinfo_'."$i";
            $filepickername = 'fileinfo_'."$i";
            $hiddenid = 'hiddenid_'."$i";
            $masterupdatename = 'masterid_'."$i";
            $deleteiconname = 'deleteid_'."$i";
            $noiconname = 'noicon_'."$i";
            $customlabelfield = 'customlabelfield_'."$i";
            $customlabelcheckbox = 'customlbelcheckbox_'."$i";

            $sectionname = $this->sectionheaders[$i];
            $courseid = $this->courseid;

            $si = new section_icon($courseid, $sectionname);
            $imagefile = $si->get_image(true);

            $mform->addElement('hidden', $hiddenid, $si->get_id());
            $mform->setType($hiddenid, PARAM_INT);
            $mform->addElement('header', $headername, $this->sectionheaders[$i]);

            $label = get_string('existingimage', BNN_LANG_TABLE);
            $mform->addElement('html', "<i>$label</i> <img alt='blank' src='$imagefile' height='35' width='50'/>");
            $mform->addElement('filemanager', $filepickername, get_string('newimage', BNN_LANG_TABLE), null,
                               array('subdirs' => 0, 'maxbytes' => BNN_MAX_BYTES, 'maxfiles' => BNN_MAX_FILES,
                               'accepted_types' => 'image'));
            // Icon settings.
            $pagegroup = array();
            $pagegroup[] = $mform->createElement('checkbox', $deleteiconname, '', get_string('deleteicon', BNN_LANG_TABLE));
            $pagegroup[] = $mform->createElement('advcheckbox', $noiconname, '', get_string('noicon', BNN_LANG_TABLE),
                                                 null, array(0, 1));
            // Can only disable if not deleting.
            $mform->disabledIf($noiconname, $deleteiconname, 'checked');
            $pagegroup[] = $mform->createElement('checkbox', $masterupdatename, '', get_string('updatecourseonly', BNN_LANG_TABLE));
            // Can only update a record if not deleting/disabling.
            $mform->disabledIf($masterupdatename, $deleteiconname, 'checked');
            $mform->disabledIf($masterupdatename, $noiconname, 'checked');
            $mform->addGroup($pagegroup, 'deletebar', get_string('iconsettings', BNN_LANG_TABLE), null, false);
            // Custom section text.
            $pagegroup = array();
            $pagegroup[] = $mform->createElement('text', $customlabelfield, '', array());
            $mform->setType($customlabelfield, PARAM_TEXT);
            $pagegroup[] = $mform->createElement('advcheckbox', $customlabelcheckbox, '', get_string('customlabel', BNN_LANG_TABLE),
                                                 null, array(0, 1));
            $mform->disabledIf($customlabelfield, $customlabelcheckbox); // Custom text must be selected.
            $mform->disabledIf($customlabelfield, $deleteiconname, 'checked'); // Invalid when deleting/disabling.
            $mform->disabledIf($customlabelfield, $noiconname, 'checked');
            $mform->disabledIf($customlabelcheckbox, $deleteiconname, 'checked'); // Invalid when deleting/disabling.
            $mform->disabledIf($customlabelcheckbox, $noiconname, 'checked');
            $mform->addGroup($pagegroup, 'customtext', '', null, false);
        }

        // Hidden elements (courseid + blockid: needed for posting).
        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);
        $mform->addElement('hidden', 'blockid');
        $mform->setType('blockid', PARAM_INT);

        $this->add_action_buttons();
    }
}