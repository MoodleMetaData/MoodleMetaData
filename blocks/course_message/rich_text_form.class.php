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


defined('MOODLE_INTERNAL') || die;
require_once(dirname(__FILE__).'/../../config.php');
global $CFG;
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/mod/page/locallib.php');
require_once($CFG->libdir.'/filelib.php');

/**
 * Rich text form
 *
 * This is the rich text form that lets users use the rich editing controls.  Different controls
 * are handled by parameterizing the id name that is given to moodle forms.  The tinymce name
 * on the page will be "id_$idmodifier".
 *
 * @package    block_course_message
 * @copyright  2012 Craig Jamieson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class rich_text_form extends moodleform {
    private $context;
    private $idmodifier;

    /**
     * Constructor -> Save the context and idmodifier so that they can be used in the function below.
     *
     */
    public function __construct($context, $idmodifier) {
        $this->context = $context;
        $this->idmodifier = $idmodifier;
        parent::__construct();
    }

    /**
     * Form definition is fairly straightforward, create an html editor control.  Any styling that
     * needs to be done should be handled via CSS.
     *
     */
    public function definition() {
        $mform = $this->_form;

        // Third parameter as null should prevent users from adding files.
        $mform->addElement('editor', $this->idmodifier, null);
    }
}