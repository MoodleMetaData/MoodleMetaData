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
		$mform -> addElement('header', 'general_header', get_string('general_header', 'local_metadata'));
		$assessment_title = $mform -> addElement('text', 'assessment_title', get_string('assessment_title', 'local_metadata'));
		
		$assessment_prof = $mform -> addElement('text', 'assessment_prof', get_string('assessment_prof', 'local_metadata'));
		
		$mform->setDefault('assessment_prof', get_string('assessment_prof_default', 'local_metadata'));
		
		$assessment_isexam = $mform -> addElement('selectyesno', 'isexam', get_string('assessment_isexam', 'local_metadata'));
		$assessmet_duration = $mform -> addElement('duration', 'assessment_duration', get_string('assessment_duration', 'local_metadata'));
		
		
		$assessment_description_text = $mform->addElement('textarea', 'assessment_description', get_string('assessment_description', 'local_metadata'), 'wrap="virtual" rows="10" cols="70"');
		$mform->addRule('assessment_description', get_string('required'),'required', null, 'client');
		$mform->setType('assessment_description',PARAM_RAW);
		$mform->setType('assessment_title', PARAM_TEXT);
		$mform->setType('assessment_prof', PARAM_TEXT);
		$mform->setType('selectyesno', PARAM_TEXT);
		$mform->setType('duration', PARAM_INT);
		
		// For Testing Purposes, Probably should be replaced with db calls
		$assessment_type_array = array();
		$assessment_type_array[1] = 'Assignment';
		$assessment_type_array[2] = 'Lab';
		$assessment_type_array[3] = 'Lab Exam';
		//REPLACE WITH DB CALLS

		$assessment_type = $mform -> addElement('select','assessment_type', get_string('assessment_type','local_metadata'), $assessment_type_array, '');
		
		$assessment_date = $mform -> addElement('date_selector', 'assessment_date', get_string('assessment_due', 'local_metadata'));
		//Set the disabledIf rules
		$mform -> disabledIf('assessment_type','isexam','eq','1');
		$mform -> disabledIf('assessment_prof','isexam','eq','1');
		$mform -> disabledIf('assessment_duration', 'isexam', 'eq', '0');
		
		//REPLACE WITH DB CALLS		
		$assessment_test_array = array();
		$assessment_test_array[0] = 'Select Learning Objective';
		$assessment_test_array[1] = 'Distinguishing between PHP and C';
		$assessment_test_array[2] = 'Working with git';
		//REPLACE WITH DB CALLS
		$repeatOptions = array();
		$repeatOptions['knowledge_text']['type'] = PARAM_TEXT;
		$repeatOptions['skills_text']['type'] = PARAM_TEXT;
		$repeatOptions['attitudes_text']['type'] = PARAM_TEXT;
		
		

		
		//Knowledge Header
		$mform -> addElement('header', 'knowledge_header', get_string('knowledge_header', 'local_metadata'));
			$knowledge_objectives = array(); // make the array
				$knowledge_objectives[] = $mform -> createElement('text', 'knowledge_text', get_string('knowledge_text', 'local_metadata'));
				
			//repeat the elements
			$this -> repeat_elements($knowledge_objectives, 1,$repeatOptions, 'knowlege_repeats', 'knowledge_add_fields', 1, null, true);
		
		//Skills Header
		$mform -> addElement('header','skills_header', get_string('skills_header', 'local_metadata'));
			$skills_onjectives = array(); //Make the arra
				$skills_onjectives[] = $mform -> createElement('text', 'skills_text', get_string('knowledge_text', 'local_metadata'));
		
			//repeat the elements
			$this -> repeat_elements($skills_onjectives, 1, $repeatOptions, 'skills_repeats', 'skills_add_fields', 1, null, true);
		
		//Attitudes Header
		$mform -> addElement('header','attitudes_header', get_string('attitudes_header', 'local_metadata'));
			$attitudes = array();
				$attitudes[] = $mform -> createElement('text', 'attitudes_text', get_string('knowledge_text', 'local_metadata'));

			//repeat the elements
			$this -> repeat_elements($attitudes, 1, $repeatOptions, 'attitudes_repeats', 'attitudes_add_fields', 1, null, true);
			
			
			
	
		//Grading Header
		$mform -> addElement('header', 'grading_header', get_string('grading_header', 'local_metadata'));
		
		
		$textattribs = array('size'=>'20');
		$assessment_gradingDesc = $mform -> addElement('textarea', 'gradingDesc', get_string('assessment_grading_desc', 'local_metadata'), 'wrap="virtual" rows="10" cols="70"');
		
		$assessment_weight = $mform-> addElement('text','grade_weight',get_string('grade_weight','local_metadata'));
		$mform->setType('grade_weight', PARAM_TEXT);
		
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
