<?php

/**
 * Pearson block code.
 *
 * @package    blocks_pearson
 * @copyright  2012-2013 Pearson Education
 * @license
 */
defined('MOODLE_INTERNAL') || die;
require_login();

if (isguestuser()) {
	print_error('guestsarenotallowed');
}

class block_pearson_edit_form extends block_edit_form {

    protected function specific_definition($mform) {
    	global $CFG, $COURSE;
    	require_once($CFG->dirroot.'/blocks/pearson/locallib.php');

	    // check capabilities and throw error if needed
	    has_capability('block/pearson:manage',  context_course::instance($COURSE->id));

    	if (pearsondirect_is_global_configured()) {
	        // Fields for editing HTML block title and contents.
	        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));
	        $mform->addElement('text', 'config_title', get_string('blocktitle', 'block_pearson'));
	        $mform->addHelpButton('config_title', 'blocktitle', 'block_pearson');
		    $mform->setDefault('config_title', get_string('pearson', 'block_pearson'));
		    $mform->setType('config_title', PARAM_MULTILANG);

	        $availableproducts = pearsondirect_getavailableproducts($CFG->pearson_url);

			if ($availableproducts) {
		        $mform->addElement('select', 'config_codes', get_string('codes', 'block_pearson'), $availableproducts);
		        $mform->addHelpButton('config_codes', 'codes', 'block_pearson');
		        $mform->addElement('text', 'config_customcodes', get_string('customcodes', 'block_pearson'), '');
		        $mform->addHelpButton('config_customcodes', 'customcodes', 'block_pearson');
				$mform->addElement('text', 'config_parameters', get_string('parameters', 'block_pearson'), array('size'=>'64'));
				$mform->addHelpButton('config_parameters', 'parameters', 'block_pearson');
			}
			else {
	    		$mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));
	    		$mform->addElement('static','info','',get_string('pearson_couldnotaccessproduct', 'block_pearson'));
			}
    	}
    	else {
    		$mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));
    		$mform->addElement('static','info','',get_string('pearson_notconfigured', 'block_pearson'));
    	}
    }
}
