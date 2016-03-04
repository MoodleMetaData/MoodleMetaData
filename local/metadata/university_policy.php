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
		$pform->setType('policy_editor', PARAM_RAW);
		
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
		
		$policyInfo->facultyid = 1;
		$policyInfo->policytext = $data->policy_editor;
		
		if ($existsRecord = $DB->get_record('facultypolicy', array('facultyid' => $facultyid, 'policytext' => $policyText))) {
			$policyInfo->id = $existsRecord->id;
			$updatePolicy = $DB->update_record('facultypolicy', $policyInfo, false);
		} else {
			$createPolicy = $DB->instert_record('facultypolicy', $policyInfo, false);
		}
	}
}

?>