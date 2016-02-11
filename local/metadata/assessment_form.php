<?php
require_once '../../config.php';
require_once $CFG->dirroot.'/lib/formslib.php';
require_once $CFG->dirroot.'/lib/datalib.php';

require_once 'lib.php';

class assessment_form extends moodleform {
	function definition() {
		global $CFG, $DB, $USER; //Declare our globals for use
		$mform = $this->_form; //Tell this object to initialize with the properties of the Moodle form.
                $courseId = get_course_id();

		// Form elements
		//$mform->addElement('text', 'email', get_string('email'));
		$assessment_description_text = $mform->addElement('textarea', 'assessment_description', get_string('assessment__description;, 'local_metadata'), 'wrap="virtual" rows="10" cols="70"');
		$mform->addRule('course_description', get_string('required'),'required', null, 'client');
		$mform->setType('assessment_description',PARAM_RAW);


		$this->add_action_buttons();
	}
	
	//If you need to validate your form information, you can override  the parent's validation method and write your own.	
	function validation($data, $files) {
		$errors = parent::validation($data, $files);
		global $DB, $CFG, $USER; //Declare them if you need them

		//if ($data['data_name'] Some condition here)  {
		//	$errors['element_to_display_error'] = get_string('error', 'local_demo_plug-in');
		//}
		return $errors;
	}
}

?>
