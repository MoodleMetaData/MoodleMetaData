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
		$assessment_description_text = $mform->addElement('textarea', 'assessment_description', get_string('assessment_description', 'local_metadata'), 'wrap="virtual" rows="10" cols="70"');
		$mform->addRule('course_description', get_string('required'),'required', null, 'client');
		$mform->setType('assessment_description',PARAM_RAW);
		
		//REPLACE WITH DB CALLS		
		$assessment_test_array = array();
		$assessment_test_array[0] = 'Select Learning Objective';
		$assessment_test_array[1] = 'Distinguishing between PHP and C';
		$assessment_test_array[2] = 'Working with git';
		//REPLACE WITH DB CALLS

		$assessment_LearningObjectives = $mform -> addElement('select', 'assessments', get_string('learning_objective_selection_description', 'local_metadata'), $assessment_test_array, '');
		//$mform -> addRule('assessments', get_string('required'), 'required', null, client'); 
		
		// For Testing Purposes, Probably should be replaced with db calls
		$assessment_type_array = array();
		$assessment_type_array[0] = 'Exam';
		$assessment_type_array[1] = 'Assignment';
		$assessment_type_array[2] = 'Lab';
		$assessment_type_array[3] = 'Lab Exam';
		//REPLACE WITH DB CALLS

		$assessment_type = $mform -> addElement('select','assessment_type', get_string('assessment_type','local_metadata'), $assessment_type_array, '');
		
		$textattribs = array('size'=>'20');
		$assessment_type = $mform-> addElement('text','grade_weight',get_string('grade_weight','local_metadata'),$attributes);
		
		$assessment_objectives = $mform -> addElement('tags', 'learning_objectives', get_string('objective_description','local_metadata'), $options, $attributes);
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
