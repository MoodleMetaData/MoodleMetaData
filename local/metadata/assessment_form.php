<?php
require_once '../../config.php';
require_once $CFG->dirroot.'/lib/formslib.php';
require_once $CFG->dirroot.'/lib/datalib.php';

require_once 'lib.php';
require_once 'metadata_form.php';
require_once 'recurring_element_parser.php';

class assessment_form extends metadata_form {
    const NUM_PER_PAGE = 10;
    
	function definition() {
		global $CFG, $DB, $USER; //Declare our globals for use
		$mform = $this->_form; //Tell this object to initialize with the properties of the Moodle form.
        $courseId = get_course_id();
		$assessments = $this->_customdata['assessments'];
        $page_num = optional_param('page', 0, PARAM_INT);
        $subset_included = array_slice($assessments, $page_num * self::NUM_PER_PAGE, self::NUM_PER_PAGE);
        $assessmentCount = count($assessments);
        $displayed_count = count($subset_included);
        
		$this -> add_upload(200);
		$this -> add_assessment_template($displayed_count);
        
        $this->add_page_buttons($page_num, $assessmentCount);
		
		$this->add_action_buttons();
		$this->populate_from_db($subset_included);
		
	}
	
	
	function add_assessment_template($assessmentCount){
		
		$mform = $this->_form;
		
		//DUMMY DATA
		$type_array = array();
		$type_array[0] = 'Exam';
		$type_array[1] = 'Assignment';
		$type_array[2] = 'Lab';
		$type_array[3] = 'Lab Exam';
		//DUMMY DATA
		
		$elementArray = array();
		$optionsArray = array();
		
		
		//Set the options
		$optionsArray['assessmentname']['type'] = PARAM_TEXT;
		$optionsArray['assessmentprof']['type'] = PARAM_TEXT;
		$optionsArray['description']['type'] = PARAM_TEXT;
		$optionsArray['gradingDesc']['type'] = PARAM_TEXT;
		$optionsArray['assessmentweight']['type'] = PARAM_TEXT;
		$optionsArray['assessmentprof']['disabledif'] = array('type', 'eq', 0);
		$optionsArray['assessment_knowledge']['setmultiple'] = true;
		$optionsArray['courseassessment_id']['type'] = PARAM_TEXT;
		$optionsArray['was_deleted']['type'] = PARAM_TEXT;
		

		// Form elements

		$elementArray[] = $mform -> createElement('header', 'general_header', get_string('general_header', 'local_metadata'));
		$elementArray[] = $mform -> createElement('text', 'assessmentname', get_string('assessment_title', 'local_metadata'));
		
		
		//$elementArray[] = $mform ->createElement('selectyesno', 'isexam', get_string('assessment_isexam', 'local_metadata'));
		$elementArray[] = $mform -> createElement('select','type', get_string('assessment_type','local_metadata'), $type_array, '');
		$elementArray[] = $mform -> createElement('text', 'assessmentprof', get_string('assessment_prof', 'local_metadata'));
		$elementArray[] = $mform -> createElement('date', 'assessmentduedate', get_string('assessment_due', 'local_metadata'));
		
		
		$elementArray[] = $mform->createElement('textarea', 'description', get_string('assessment_description', 'local_metadata'), 'wrap="virtual" rows="10" cols="70"');
		//$mform->addRule('description', get_string('required'),'required', null, 'client');
		
				// For Testing Purposes, Probably should be replaced with db calls

		//REPLACE WITH DB CALLS

		
	
		

		$elementArray[] = $mform -> createElement('textarea', 'gdescription', get_string('assessment_grading_desc', 'local_metadata'), 'wrap="virtual" rows="10" cols="70"');
		$elementArray[] = $mform-> createElement('text','assessmentweight',get_string('grade_weight','local_metadata'));
		
		        // Add needed hidden elements
        // Stores the id for each element
        $elementArray[] = $mform->createElement('hidden', 'courseassessment_id', -1);
        $elementArray[] = $mform->createElement('hidden', 'was_deleted', false);
		
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
        
        $elementArray[] = $mform->createElement('submit', 'delete_assessment', get_string('deleteassessment', 'local_metadata'));
        $mform->registerNoSubmitButton('delete_assessment');
        $this->_recurring_nosubmit_buttons[] = 'delete_assessment';
		
		$this->repeat_elements($elementArray, $assessmentCount,
            $optionsArray, 'assessment_list', 'assessment_list_add_element', 1, get_string('add_assessment', 'local_metadata'));
		
	}
    
     function definition_after_data() {
        parent::definition_after_data();
        $mform = $this->_form;
        
        $numRepeated = $mform->getElementValue('assessment_list');
        
        // Go through each session, and delete elements for ones that should be deleted
        for ($key = 0; $key < $numRepeated; ++$key) {
            $index = '['.$key.']';
            $deleted = $mform->getSubmitValue('delete_assessment'.$index);
            
            // If a button is pressed, then doing $mform->getSubmitValue(buttonId) will return a non-null value
                // However, if other buttons are subsequently pressed, then $mform->getSubmitValue(buttonId) will return null
                // So use the element 'was_deleted' for that repeated element to store if has been deleted
            // Otherwise, if the assessment is new, should expand its header
            if ($deleted or $mform->getElementValue('was_deleted'.$index) == true) {
                // If deleted, just remove the visual elements
                    // Will not save to the database until the user presses submit
                $mform->removeElement('general_header'.$index);
                $mform->removeElement('assessmentname'.$index);
                $mform->removeElement('type'.$index);
                $mform->removeElement('assessmentprof'.$index);
                $mform->removeElement('assessmentduedate'.$index);

                $mform->removeElement('description'.$index);
                $mform->removeElement('gdescription'.$index);

                $mform->removeElement('assessmentweight'.$index);
                
                
                $learningObjectiveTypes = get_learning_objective_types();
                foreach ($learningObjectiveTypes as $learningObjectiveType) {
                    $mform->removeElement('learning_objective_'.$learningObjectiveType.$index);
                }
                
                $mform->removeElement('delete_assessment'.$index);
                
                $mform->getElement('was_deleted'.$index)->setValue(true);
            } else {
                if ($mform->getElement('courseassessment_id'.$index)->getValue() == -1) {
                    $mform->setExpanded('general_header'.$index);
                }
            }
        }
    }
	
	//If you need to validate your form information, you can override  the parent's validation method and write your own.	
	function validation($data, $files) {
		$errors = parent::validation($data, $files);
		return $errors;
	}
	
	function save_assessment_list($data){
		global $DB;
		$changed = array('assessmentname', 'type', 'assessmentprof', 'description', 'gdescription', 'assessmentweight', 'was_deleted');
		$assessment_parser = new recurring_element_parser('courseassessment', 'assessment_list', $changed);
		
		$tuples = $assessment_parser->getTuplesFromData($data);
        
        foreach ($tuples as $tupleKey => $tuple) {
            // TODO: Should also delete all the relations for each assessment
            
            // If the tuple has been deleted, then remove it from the database
            if ($tuple['was_deleted'] == true) {
                $assessment_parser->deleteTupleFromDB($tuple);
                
                // Finally, remove it from the tuples that will be saved, because otherwise will just be resaved anyway
                unset($tuples[$tupleKey]);
            }
        }
        

		$assessment_parser -> saveTuplesToDB($tuples);
		
		foreach ($tuples as $tuplekey => $tuple){
			$learningObjectiveTypes = get_learning_objective_types();
			foreach ($learningObjectiveTypes as $learningObjectiveType) {
                $key = 'learning_objective_'.$learningObjectiveType;
                if (array_key_exists($key, $tuple) and is_array($tuple[$key])) {
                    foreach ($tuple[$key] as $objectiveId) {
                        $newLink = new stdClass();
                        $newLink->assessmentid = $tuple['id'];
                        $newLink->objectiveid = $objectiveId;
						print_object($newlink);
                        $DB->insert_record('assessmentobjectives', $newLink, false);
                    }
                }
            }
		}
		
		
	}
    
    public function get_page_change() {
        if ($this->_form->getSubmitValue('previousPage') !== null) {
            return -1;
        } else if ($this->_form->getSubmitValue('nextPage') !== null) {
            return 1;
        } else {
            return 0;
        }
    }
    
	function get_knowledge(){
		global $DB;

	}
	
    /**
     *  Will add the buttons on the bottom
     *  
     *
     */
    private function add_page_buttons($page_num, $num_assessments) {
        $mform = $this->_form;
        
        $page_change_links=array();
        
        // Back page button
        $page_change_links[] = $mform->createElement('submit', 'previousPage', get_string('previous_page', 'local_metadata'));
        
        // If is on the first page
        if ($page_num === 0) {
            $mform->disabledIf('previousPage', null);
        }
    
        // Next page button
        $page_change_links[] = $mform->createElement('submit', 'nextPage', get_string('next_page', 'local_metadata'));
        
        // If the next page would be empty
        if (($page_num + 1) * self::NUM_PER_PAGE >= $num_assessments) {
            $mform->disabledIf('nextPage', null);
        }
        
        $mform->addGroup($page_change_links, 'buttonarray', '', array(' '), false);
    }
    
	function populate_from_db($assessments){
		$mform = $this->_form;
		$key = 0;
		
		foreach($assessments as $assessment){
			$index = '['.$key.']';
			
			$mform->setDefault('general_header'.$index, $assessment->assessmentname);
			$mform->setDefault('assessmentname'.$index, $assessment->assessmentname);
			$mform->setDefault('assessmentweight'.$index, $assessment->assessmentweight);
			$mform->setDefault('assessmentprof'.$index, $assessment->assessmentprof);
			$mform->setDefault('assessmentduedate'.$index, $assessment->assessmentduedate);
			$mform->setDefault('description'.$index, $assessment->description);
			$mform->setDefault('gdescription'.$index, $assessment->gdescription);
			$mform->setDefault('courseassessment_id'.$index, $assessment->id);
			
			$key += 1;
		}
		
	}
	
	function add_upload($maxbytes){
		$mform = $this -> _form;
		
		$mform->addElement('filepicker', 'assessmentFile', get_string('filepicker', 'local_metadata'), null, array('maxbytes' => $maxbytes, 'accepted_types' => '.csv'));
	}
}
	

?>
