<?php
require_once '../../config.php';
require_once $CFG->dirroot . '/lib/formslib.php';
require_once $CFG->dirroot . '/lib/datalib.php';
class university_form extends moodleform {
	function definition() {
		global $CFG, $DB, $USER;
		$pform = $this->_form;
		
		//Form Elements
		// Rich text editor
		$pform->addElement('editor', 'university_editor', get_string('university_editor', 'local_metadata'));
		$pform->setType('university_editor', PARAM_TEXT);
		
		//Save Changes Button
		$pform->addElement('submit', 'submit_policy', get_string('submit_policy', 'local_metadata'));
		
	}
	
	function validation($data, $file) {
		$errors = parent::validation ( $data, $files );
		global $DB, $CFG, $USER; // Declare them if you need them
		
		return $errors;
	}
	
	public static function save_data($data) {
		global $DB, $CFG, $USER;
		$policyInfo = new stdClass();
		
		$policyInfo->category = -1;
		$policyInfo->policy = $data->university_editor['text'];
		
		print_r ($policyInfo);
		if ($existsRecord = $DB->get_record('syllabuspolicy', array('category' => $policyInfo->category))) {
			$policyInfo->id = $existsRecord->id;
			$updatePolicy = $DB->update_record('syllabuspolicy', $policyInfo, false);
		} else {
			$createPolicy = $DB->insert_record('syllabuspolicy', $policyInfo, false);
			print_r ($createPolicy);
		}
	}
}

?>