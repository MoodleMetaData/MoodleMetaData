<?php
require_once '../../config.php';
require_once $CFG->dirroot.'/lib/formslib.php';
require_once $CFG->dirroot.'/lib/datalib.php';

class policy_form extends moodleform {
	function definition() {
		global $CFG, $DB, $USER;
		global $categoryId;
		
		$pform = $this->_form;
		
		//Form Elements
		// Rich text editor
		$defaulttext = '';
		if($exists = $DB->get_record('syllabuspolicy', array ('category' => $categoryId))) {
			$defaulttext = $exists->policy;
		}
		
		$pform->addElement('editor', 'policy_editor', get_string('policy_editor', 'local_metadata'))->setValue( array('text' => $defaulttext));
		//$pform->addRule('policy_editor', get_string('err_required', 'local_metadata'), 'required', null, 'client');
		$pform->setType('policy_editor', PARAM_RAW);
		
		//Save Changes Button
		$pform->addElement('submit', 'submit_policy', get_string('submit_policy', 'local_metadata'));
		
	}
	
	/*
	function validation($data, $file) {
		$errors = parent::validation ( $data, $files );
		return $errors;
	} */
	
	
	/**
	 * Save the policy from the form into the database
	 * @param object $data the data from the form
	 * @return void
	 */
	public static function save_data($data) {
		global $DB, $CFG, $USER;
		global $categoryId;
		
		$policyInfo = new stdClass();
		
		$policyInfo->category = $categoryId;
		$policyInfo->policy = $data->policy_editor['text'];
		
		if ($existsRecord = $DB->get_record('syllabuspolicy', array('category' => $policyInfo->category))) {
			$policyInfo->id = $existsRecord->id;
			$updatePolicy = $DB->update_record('syllabuspolicy', $policyInfo, false);
		} else {
			$createPolicy = $DB->insert_record('syllabuspolicy', $policyInfo, false);
		}
	}
}

?>