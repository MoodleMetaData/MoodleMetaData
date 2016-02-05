<?php
require_once '../../config.php';
require_once $CFG->dirroot.'/lib/formslib.php';
require_once $CFG->dirroot.'/lib/datalib.php';

class general_form extends moodleform {
	function definition() {
		global $CFG, $DB, $USER; //Declare our globals for use
		$mform = $this->_form; //Tell this object to initialize with the properties of the Moodle form.

		// Form elements
		
		// Add text area for course description
		$course_description_text = $mform->addElement('textarea', 'course_description', get_string('course_description', 'local_metadata'), 'wrap="virtual" rows="10" cols="70"');
		//$course_description_text = $mform->addElement('editor', 'course_description', get_string('course_description', 'local_metadata'));
		$mform->addRule('course_description', get_string('required'), 'required', null, 'client');
		$mform->setType('course_description', PARAM_RAW);	

		// Add selection list for course type		
		// ---------- testing purpose ----------
		$course_types = array();
		$course_types[] = 'type 1';
		$course_types[] = 'type 2';
		// -------------------------------------
		$course_type_selection = $mform->addElement('select', 'course_type', get_string('course_type', 'local_metadata'), $course_types, '');
		$mform->addRule('course_type', get_string('required'), 'required', null, 'client');

		// Add multi-selection list for course topics
		// ---------- testing purpose ----------
		$course_topics = array();
		$course_topics[] = 'topic 1';
		$course_topics[] = 'topic 2';
		// -------------------------------------
		$course_topic_selection = $mform->addElement('select', 'course_topic', get_string('course_topic', 'local_metadata'), $course_topics, '');
		$course_topic_selection->setMultiple(true);
		$mform->addRule('course_topic', get_string('required'), 'required', null, 'client');
		
		// Add multi-selection list for course learning objectives
		// ---------- testing purpose ----------
		$course_objectives = array();
		$course_objectives[] = 'objective 1';
		$course_objectives[] = 'objective 2';
		// -------------------------------------
		$course_objective_selection = $mform->addElement('select', 'course_objective', get_string('course_objective', 'local_metadata'), $course_objectives, '');
		$course_objective_selection->setMultiple(true);
		$mform->addRule('course_objective', get_string('required'), 'required', null, 'client');
		

		// US 1.05
		// some code here
		
		// US 1.06
		// some code here

		// US 1.07
		// some code here

		// Add form buttons
		$this->add_action_buttons();
	}
	
	//If you need to validate your form information, you can override  the parent's validation method and write your own.	
	function validation($data, $files) {
		$errors = parent::validation($data, $files);
		global $DB, $CFG, $USER; //Declare them if you need them

		//if ($data['data_name'] Some condition here)  {
		//	$errors['element_to_display_error'] = get_string('error', 'local_demo_plug-in');
		//}
	}
}

?>

