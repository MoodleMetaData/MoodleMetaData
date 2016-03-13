<?php
require_once '../../config.php';
require_once $CFG->dirroot.'/lib/formslib.php';
require_once $CFG->dirroot.'/lib/datalib.php';

require_once 'lib.php';
require_once 'recurring_element_parser.php';

class assessment_form extends moodleform {
	function definition() {
		global $CFG, $DB, $USER; //Declare our globals for use
		$mform = $this->_form; //Tell this object to initialize with the properties of the Moodle form.
                $courseId = get_course_id();
		$this -> add_assessment_template(1);

		$this->add_action_buttons();
	}
	
	function add_assessment_template($assessmentCount){
		
		$mform = $this->_form;
		
		//DUMMY DATA
		$type_array = array();
		$type_array[1] = 'Assignment';
		$type_array[2] = 'Lab';
		$type_array[3] = 'Lab Exam';
		//DUMMY DATA
		
		$elementArray = array();
		$optionsArray = array();
		
		
		//Set the options
		$optionsArray['assessmentname']['type'] = PARAM_TEXT;
		$optionsArray['assessment_prof']['type'] = PARAM_TEXT;
		$optionsArray['description']['type'] = PARAM_TEXT;
		$optionsArray['gradingDesc']['type'] = PARAM_TEXT;
		$optionsArray['assessmentweight']['type'] = PARAM_TEXT;
		$optionsArray['assessment_prof']['disabledif'] = array('isexam', 'eq', 1);
		$optionsArray['assessment_knowledge']['setmultiple'] = true;
		// Form elements

		$elementArray[] = $mform -> createElement('header', 'general_header', get_string('general_header', 'local_metadata'));
		$elementArray[] = $mform -> createElement('text', 'assessmentname', get_string('assessmentname', 'local_metadata'));
		
		//$mform->setDefault('assessment_prof', get_string('assessment_prof_default', 'local_metadata'));
		
		$elementArray[] = $mform ->createElement('selectyesno', 'isexam', get_string('assessment_isexam', 'local_metadata'));
		$elementArray[] = $mform -> createElement('select','type', get_string('type','local_metadata'), $type_array, '');
		$elementArray[] = $mform -> createElement('text', 'assessment_prof', get_string('assessment_prof', 'local_metadata'));
		$elementArray[] = $mform -> createElement('date', 'assessmentduedate', get_string('assessmentduedate', 'local_metadata'));
		
		
		$elementArray[] = $mform->createElement('textarea', 'description', get_string('description', 'local_metadata'), 'wrap="virtual" rows="10" cols="70"');
		//$mform->addRule('description', get_string('required'),'required', null, 'client');
		
				// For Testing Purposes, Probably should be replaced with db calls

		//REPLACE WITH DB CALLS

		
		
		//Set the disabledIf rules
		$mform -> disabledIf('type','isexam','eq','1');
		$mform -> disabledIf('assessment_prof','isexam','eq','1');
		

		$elementArray[] = $mform -> createElement('textarea', 'gradingDesc', get_string('assessment_grading_desc', 'local_metadata'), 'wrap="virtual" rows="10" cols="70"');
		$elementArray[] = $mform-> createElement('text','assessmentweight',get_string('assessmentweight','local_metadata'));
		
		//copied from session_form.php
		/////////////////////////////////////////////////
		$learningObjectives = get_course_learning_objectives();
		$learningObjectivesList = array();
        foreach ($learningObjectives as $learningObjective) {
            $learningObjectivesList[$learningObjective->objectivetype][$learningObjective->id] = $learningObjective->objectivename;
        }
        $learningObjectiveTypes = get_learning_objective_types();
        foreach ($learningObjectiveTypes as $learningObjectiveType) {
            $options = array();
            if (array_key_exists($learningObjectiveType, $learningObjectivesList)) {
                $options = $learningObjectivesList[$learningObjectiveType];
            }
            
            $learningObjectivesEl = $mform->createElement('select', 'learning_objective_'.$learningObjectiveType, get_string('learning_objective_'.$learningObjectiveType, 'local_metadata'), $options);
            $learningObjectivesEl->setMultiple(true);
            $elementArray[] = $learningObjectivesEl;
        }
		/////////////////////////////////////////////////
		
		$this->repeat_elements($elementArray, 1,
            $optionsArray, 'assessment_list', 'assessment_list_add_element', 1, get_string('add_assessment', 'local_metadata'), true);
		
	}
	
	//If you need to validate your form information, you can override  the parent's validation method and write your own.	
	function validation($data, $files) {
		$errors = parent::validation($data, $files);
		return $errors;
	}
	
	function save_assessment_list($data){
		global $DB;
		$changed = array('assessmentname', 'isexam', 'type', 'assessment_prof', 'description', 'gradingDesc', 'assessmentweight');
		$assessment_parser = new recurring_element_parser('courseassessment', 'assessment_list', $changed, null);
		
		$tuples = $assessment_parser->getTuplesFromData($data);
		print_object($tuples);
		$assessment_parser -> saveTuplesToDB($tuples);
	}
	function get_knowledge(){
		global $DB;

	}
}
	

?>
