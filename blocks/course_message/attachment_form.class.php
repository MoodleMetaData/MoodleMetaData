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
require_once(dirname(__FILE__).'/../../config.php');
global $CFG;
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/blocks/course_message/locallib.php');

/**
 * Modded attachment class
 *
 * This is the attachment form that lets users attach files to a mail.  For the most part, the code is
 * standard Moodle code for using the file manager.  This code is listed on the web at:
 * http://docs.moodle.org/dev/Using_the_File_API_in_Moodle_forms.  This attachment class sets up the
 * draft session and allows the user to access the draft ID.  The draft ID for the session is fixed
 * when the session is created, it does not change as files are added.
 *
 * @package    block_course_message
 * @copyright  2012 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class attachment_form extends moodleform {
    /** the context, used for getting an ID */
    private $context;
    /** idmodifier - not visible as HTML, but used for behat tests */
    private $idmodifier;
    /** the draft attachment id that is generated by Moodle for this session */
    private $draftid;

    /**
     * Constructor -> Save the context so it can be used in the function below.
     *
     */
    public function __construct($context, $idmodifier) {
        $this->context = $context;
        $this->idmodifier = $idmodifier;
        parent::__construct();
    }

    /**
     * Form definition: creates the file manager interface using standard Moodle code, then
     * stores the session draft ID so that it can be retrieved.  This draft ID is new for
     * every session, but does not change as files are picked.
     *
     */
    public function definition() {
        $mform = &$this->_form;

        // Grab max attachment size from config.
        $maxbytes = get_config('course_message', 'Attachment_Size');
        $elementname = 'attachments'.$this->idmodifier;
        // Create a filemanager interface - label is now set since behat testing uses this parameter.
        $mform->addElement('filemanager', $elementname, $this->idmodifier.' files', null,
                            array('subdirs' => 0, 'maxbytes' => $maxbytes, 'maxfiles' => BLOCK_CM_MAX_FILES));

        // Setup the "entry" object.
        $entry = new stdClass();
        $entry->id = null;

        // Setup the session (not sure why bytes, files are specified twice).
        $draftitemid = file_get_submitted_draft_itemid($elementname);
        file_prepare_draft_area($draftitemid, $this->context->id, BLOCK_CM_COMPONENT_NAME, BLOCK_CM_FILE_AREA_NAME,
                                $entry->id, array('subdirs' => 0, 'maxbytes' => $maxbytes, 'maxfiles' => BLOCK_CM_MAX_FILES));
        $entry->$elementname = $draftitemid;
        // Setup the form with the entry.
        $this->set_data($entry);

        // Store the draft ID so it can be retrieved.
        $this->draftid = $draftitemid;
    }

    /**
     * This function returns the draft ID of the session, so that it can be passed along to
     * send_message.php and the files can be saved.
     *
     * @return int The draft ID of the message attachment.
     *
     */
    public function get_draft_id() {
        return $this->draftid;
    }
}