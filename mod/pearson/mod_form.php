<?php
/**
 *
 *
 * @package    mod
 * @subpackage pearson
 * @copyright
 * @author
 * @license
 */

defined('MOODLE_INTERNAL') || die;
require_login();
if (isguestuser()) {
	print_error('guestsarenotallowed');
}

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/lti/locallib.php');

class mod_pearson_mod_form extends moodleform_mod {

    public function definition() {
        global $CFG, $DB, $PAGE, $OUTPUT, $USER, $COURSE;
        require_once($CFG->dirroot.'/blocks/pearson/locallib.php');

        // check capabilities and throw error if needed
        has_capability('mod/pearson:addinstance',  context_course::instance($COURSE->id));

    	$this->typeid = 0;
        $mform =& $this->_form;

        if (pearsondirect_is_global_configured()) {
        	if (pearsondirect_course_has_config($COURSE->id)) {
		        $data = $this->current;

		        $mform->addElement('hidden', 'pdcourseid', $data->course);
		        $mform->addElement('hidden', 'pdsection', $data->section);

		        $mform->addElement('header', 'product', get_string('addlinkheader', 'mod_pearson'));

				$availablelinks= array();

		        foreach (lti_get_types_for_add_instance() as $id => $type) {
		            if ($type->course == $COURSE->id) {
		                $idnumber = $type->tooldomain;
		                if (!strncmp($idnumber, 'pearson:', strlen('pearson:'))) {
		                	$availablelinks[$type->id] = $type->name;
		                }
		            }
		        }

		        $mform->addElement('select', 'selectedlink', get_string('selectedlink', 'mod_pearson'), $availablelinks);
		        $mform->addHelpButton('selectedlink', 'selectedlink', 'mod_pearson');
		        $mform->setType('selectedlink', PARAM_TEXT);
		        $mform->addRule('selectedlink', null, 'required', null, 'client');
		        $mform->addElement('text', 'linktitle', get_string('linktitle', 'mod_pearson'), '');
		        $mform->addHelpButton('linktitle', 'linktitle', 'mod_pearson');
		        $mform->addElement('text', 'customparams', get_string('customparams', 'mod_pearson'), array('size'=>'64'));
		        $mform->addHelpButton('customparams', 'customparams', 'mod_pearson');

		        $mform->addElement('header', 'media', get_string('addmediaheader', 'mod_pearson'));
		        $mform->addElement('text', 'mediaurl', get_string('mediaurl', 'mod_pearson'), array('size'=>'64'));
		        $mform->addHelpButton('mediaurl', 'mediaurl', 'mod_pearson');
				$mform->addElement('text', 'mediaurltitle', get_string('mediaurltitle', 'mod_pearson'), '');
				$mform->addHelpButton('mediaurltitle', 'mediaurltitle', 'mod_pearson');

				$mform->addElement('header', 'lauchpresentation', get_string('lauchpresentationheader', 'mod_pearson'));
				$mform->addElement('checkbox', 'inframe', get_string('inframe', 'mod_pearson'));
				$mform->addHelpButton('inframe', 'inframe', 'mod_pearson');
		        // add standard elements, common to all modules
		        $this->standard_coursemodule_elements();
		        // add standard buttons, common to all modules
		        $this->add_action_buttons();
        	}
        	else {
	    		$mform->addElement('header', 'moduleheader', 'Module settings');
	    	    $mform->addElement('static','info','',get_string('pearson_blocknotconfigured', 'block_pearson'));
		        // add standard elements, common to all modules
		        $this->standard_coursemodule_elements();
		        // add standard buttons, common to all modules
		        $this->add_action_buttons(true,false,false);
        	}
    	}
    	else {
    		$mform->addElement('header', 'moduleheader', 'Module settings');
    	    $mform->addElement('static','info','',get_string('pearson_notconfigured', 'block_pearson'));
	        // add standard elements, common to all modules
	        $this->standard_coursemodule_elements();
	        // add standard buttons, common to all modules
	        $this->add_action_buttons(true,false,false);
    	}

    }

   function standard_hidden_coursemodule_elements(){
        $mform =& $this->_form;
        $mform->addElement('hidden', 'course', 0);
        $mform->setType('course', PARAM_INT);

        $mform->addElement('hidden', 'coursemodule', 0);
        $mform->setType('coursemodule', PARAM_INT);

        $mform->addElement('hidden', 'section', 0);
        $mform->setType('section', PARAM_INT);

        $mform->addElement('hidden', 'module', 0);
        $mform->setType('module', PARAM_INT);

        $mform->addElement('hidden', 'modulename', '');
        $mform->setType('modulename', PARAM_PLUGIN);

        $mform->addElement('hidden', 'instance', 0);
        $mform->setType('instance', PARAM_INT);

        $mform->addElement('hidden', 'add', 0);
        $mform->setType('add', PARAM_ALPHA);

        $mform->addElement('hidden', 'update', 0);
        $mform->setType('update', PARAM_INT);

        $mform->addElement('hidden', 'return', 0);
        $mform->setType('return', PARAM_BOOL);
    }

    /**
     * Overriding formslib's add_action_buttons() method, to add an extra submit "save changes and return" button.
     *
     * @param bool $cancel show cancel button
     * @param string $submitlabel null means default, false means none, string is label text
     * @param string $submit2label  null means default, false means none, string is label text
     * @return void
     */
    function add_action_buttons($cancel=true, $submitlabel=null, $submit2label=null) {

        if (is_null($submit2label)) {
            $submit2label = get_string('savechangesandreturntocourse');
        }

        $mform = $this->_form;

        // elements in a row need a group
        $buttonarray = array();

        if ($submit2label !== false) {
            $buttonarray[] = &$mform->createElement('submit', 'submitbutton2', $submit2label);
        }

        if ($cancel) {
            $buttonarray[] = &$mform->createElement('cancel');
        }

        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->setType('buttonar', PARAM_RAW);
        $mform->closeHeaderBefore('buttonar');
    }

    /**
     * Function overwritten to change default values using
     * global configuration
     *
     * @param array $default_values passed by reference
     */
    public function data_preprocessing(&$default_values) {

    }
}

