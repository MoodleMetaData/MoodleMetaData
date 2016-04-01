<?php
require_once '../../config.php';
require_once $CFG->dirroot.'/lib/formslib.php';
require_once $CFG->dirroot.'/lib/datalib.php';

require_once 'lib.php';
require_once 'metadata_form.php';
require_once 'recurring_element_parser.php';

/**
 * The form to display the tab for assessments
 *
 * Requires the argument 'assessments', which should be the array of assessments
 *   for the current course loaded from the database
 *
 *
 */
class assessment_form extends metadata_form {
    /**
     * @var int NUM_PER_PAGE Number of assessments to be displayed per page
     */
    const NUM_PER_PAGE = 10;
    /**
     * @var int DATE_FROM_FROM_FILE Format used when parsing the data from upload
     */
    const DATE_FROM_FROM_FILE = 'Y-m-d';
    
    /**
     * Will set up the form elements
     *
     * @see lib/moodleform#definition()
     */
	function definition() {
		global $CFG, $DB, $USER; //Declare our globals for use
		$mform = $this->_form; //Tell this object to initialize with the properties of the Moodle form.
        $courseId = get_course_id();
		$assessments = $this->_customdata['assessments'];
        $page_num = optional_param('page', 0, PARAM_INT);
        $subset_included = array_slice($assessments, $page_num * self::NUM_PER_PAGE, self::NUM_PER_PAGE);
        $assessmentCount = count($assessments);
        $displayed_count = count($subset_included);
        
		$this -> add_upload($assessmentCount);
		$this -> add_assessment_template($displayed_count);
        
        $this->add_page_buttons($page_num, $assessmentCount);
		
		$this->add_action_buttons();
		$this->populate_from_db($subset_included);
		
	}
	
    /**
     * Will determine if the assessments were uploaded
     *
     * @return boolean for if use wanted to upload file
     */
    public function assessments_were_uploaded() {
        return $this->_form->getSubmitValue('upload_assessments') !== null;
    }
    
	/**
     * Will use the csv file submitted by the instructor to create all of the assessments
     *   Note that it does clear all existing data in the assessment related tables first
     *
     */
    public function upload_assessments() {
		global $course, $DB;
		
        $files = $this->get_draft_files('uploaded_assessments');
        
        if (!empty($files)) {
            $file = reset($files); 
            $content = $file->get_content();
            $all_rows = explode("\n", $content);
            
            $courseid = get_course_id();
            // Need to delete everything related to course assessments, and each assessment
            foreach ($this->_customdata['assessments'] as $assessment) {
                $this->delete_all_relations_to_assessment($assessment->id);
            }
            
            $DB->delete_records('courseassessment', array('courseid'=>$courseid));
            
            
            foreach ($all_rows as $row) {
                if ($row != "") {
                    $this->parse_and_save_assessment_row($row, $courseid);
                }
            }
        }
    }
    
    /**
     * Will determine if the rubric were uploaded
     *
     * @return boolean for if user wanted to upload file
     */
	public function rubric_was_uploaded(){
		return $this->_form->getSubmitValue(gradingDescription_uploaded) !== null;
	}
    
    /**
     * Will save the file the user added for the rubric
     *
     */
	public function upload_rubric(){
		$file = $this -> save_stored_file('gradingDescription_uploaded');
	}
    
    /**
     * Will use given line csv file submitted by the instructor to create an assessment
     *
     * @param string $row The current row of the csv file being operated on
     * @param integer $courseid The id for the course this assessment will be added to
     *
     */
    private function parse_and_save_assessment_row($row, $courseid) {
        global $DB;
        // Parse the row
        $row = str_getcsv($row);
        
        $data = array();
        $data['courseid'] = $courseid;
        $data['assessmentname'] = $row[0];
        $data['type'] = $row[1];
		$data['assessmentweight'] = $row[2];
        $data['description'] = $row[3];
        $data['gdescription'] = $row[4];
        
        $date = DateTime::createFromFormat(assessment_form::DATE_FROM_FROM_FILE, $row[5]);
        if (is_object($date)) {
            $data['assessmentduedate'] = $date->getTimestamp();
        }
        
        if ($data['type'] == 'Exam') {
            $data['assessmentprof'] = $row[6];
            $data['assessmentexamtype'] = $row[7];
        }
        
        
        // Then, save the assessment and get ids
        $id = $DB->insert_record('courseassessment', $data);
        
    }
    
    /**
     *  Will set up a repeating template, with elements for each piece of required data
     *
     *  Does not set defaults for the elements.
     *
     *  @param int $assessmentCount number of assessments that have been created for the course
     */
	function add_assessment_template($assessmentCount){
		
		$mform = $this->_form;
		
		$elementArray = array();
		$optionsArray = array();
		
		
		//Set the options
		$optionsArray['assessmentname']['type'] = PARAM_TEXT;
		$optionsArray['assessmentprof']['type'] = PARAM_TEXT;
		$optionsArray['description']['type'] = PARAM_TEXT;
		$optionsArray['gradingDesc']['type'] = PARAM_TEXT;
		$optionsArray['assessmentweight']['type'] = PARAM_INT;
        
        // If is not an exam, should disable these two
		$optionsArray['assessmentprof']['disabledif'] = array('type', 'neq', 0); 
		$optionsArray['assessmentexamtype']['disabledif'] = array('type', 'neq', 0);
        
		$optionsArray['assessment_knowledge']['setmultiple'] = true;
		$optionsArray['courseassessment_id']['type'] = PARAM_TEXT;
		$optionsArray['was_deleted']['type'] = PARAM_TEXT;
		
        $optionsArray['assessment_header']['default'] = get_string('new_assessment_header', 'local_metadata');

		// Form elements

		$elementArray[] = $mform -> createElement('header', 'assessment_header');
		$elementArray[] = $mform -> createElement('text', 'assessmentname', get_string('assessment_title', 'local_metadata'));
		
		
		$elementArray[] = $mform -> createElement('select','type', get_string('assessment_type','local_metadata'), get_assessment_types(), '');
		$elementArray[] = $mform -> createElement('text', 'assessmentprof', get_string('assessment_prof', 'local_metadata'));
        
        $exam_types = get_exam_types();
		$elementArray[] = $mform -> createElement('select', 'assessmentexamtype', get_string('assessment_examtype', 'local_metadata'), $exam_types);
		$optionsArray['assessmentexamtype']['default'] = array_search('Other', $exam_types);
        
		$elementArray[] = $mform-> createElement('text','assessmentweight',get_string('grade_weight','local_metadata'));
		$elementArray[] = $mform -> createElement('date_selector', 'assessmentduedate', get_string('assessment_due', 'local_metadata'));
        
        
		
		
		$elementArray[] = $mform->createElement('textarea', 'description', get_string('assessment_description', 'local_metadata'));
	
		
		$elementArray[] = $mform -> createElement('filepicker', 'gradingDescription_uploaded', get_string('assessment_grading_upload', 'local_metadata'), null, array('maxbytes' => 2000, 'accepted_types' => '*'));
		$elementArray[] = $mform -> createElement('submit', 'gradingDescription_upload', get_string('assessment_grading_upload_submit', 'local_metadata'));
		$elementArray[] = $mform -> createElement('textarea', 'gdescription', get_string('assessment_grading_desc', 'local_metadata'));

		
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
            
            $element_name = 'learning_objective_'.$learningObjectiveType;
            $learningObjectivesEl = $mform->createElement('select', $element_name, get_string('learning_objective_'.$learningObjectiveType, 'local_metadata'), $options);
            $learningObjectivesEl->setMultiple(true);
            $optionsArray[$element_name]['helpbutton'] = array('multi_select', 'local_metadata');
            $elementArray[] = $learningObjectivesEl;
        }
		/////////////////////////////////////////////////
        
        $elementArray[] = $mform->createElement('submit', 'delete_assessment', get_string('deleteassessment', 'local_metadata'));
        $this->add_recurring_element_nosubmit_button($mform, 'delete_assessment');
		
		$this->repeat_elements($elementArray, $assessmentCount,
            $optionsArray, 'assessment_list', 'assessment_list_add_element', 1, get_string('assessment_add', 'local_metadata'));
		
	}
    
    /**
     *  This function is used for deleteing an assessment.
     *
     */
     function definition_after_data() {
        parent::definition_after_data();
        $mform = $this->_form;
        
        $numRepeated = $mform->getElementValue('assessment_list');
        
        // Go through each assessment, and delete elements for ones that should be deleted
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
                $mform->removeElement('assessment_header'.$index);
                $mform->removeElement('assessmentname'.$index);
                $mform->removeElement('type'.$index);
                $mform->removeElement('assessmentprof'.$index);
                $mform->removeElement('assessmentexamtype'.$index);
                $mform->removeElement('assessmentduedate'.$index);

                $mform->removeElement('description'.$index);
                $mform->removeElement('gradingDescription_uploaded'.$index);
                $mform->removeElement('gradingDescription_upload'.$index);
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
                    $mform->setExpanded('assessment_header'.$index);
                }
            }
        }
		
		// navigate to the newest added element
		if(isset($_POST['assessment_list_add_element'])) redirect_to_anchor('assessment', 'id_assessment_list_add_element', -1000);
    }
	
	/**
     * Ensure that the data the user entered is valid
     *
     * @see lib/moodleform#validation()
     *
     * @param array $data array of ("fieldname"=>value) of submitted data
     * @param array $files array of uploaded files "element_name"=>tmp_file_path
     * @return array of "element_name"=>"error_description" if there are errors,
     *         or an empty array if everything is OK (true allowed for backwards compatibility too).
     */
	function validation($data, $files) {
		$errors = parent::validation($data, $files);
		return $errors;
	}
	
    /**
     * Will save the given data, that should be from calling the get_data function. Data will be all of the assessments in the course
     *
     * Also handles removing elements that should be deleted from the form.
     *
     * @param object $data value from calling get_data on this form
     *
     */
	function save_assessment_list($data){
		global $DB;
        
		$changed = array('assessmentname', 'type', 'assessmentprof', 'assessmentexamtype', 'assessmentduedate', 'description', 'gdescription', 'assessmentweight', 'was_deleted');
		
		$learningObjectiveTypes = get_learning_objective_types();
        foreach ($learningObjectiveTypes as $learningObjectiveType) {
            $changed[] = 'learning_objective_'.$learningObjectiveType;
        }
        
        $assessment_types = get_assessment_types();
        $exam_types = get_exam_types();
        $convertedAttributes = array('type' => function($value) use ($assessment_types) { return $assessment_types[$value]; },
                                    'assessmentexamtype' => function($value) use ($exam_types) { return $exam_types[$value]; });
		
		$assessment_parser = new recurring_element_parser('courseassessment', 'assessment_list', $changed, $convertedAttributes);
		
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
						print_object($newLink);
                        $DB->insert_record('assessmentobjectives', $newLink, false);
						
                    }
                }
            }
		}
		
		
	}
    
    /**
     * Determine what change should be done to the page number
     *
     * @return integer page change amount
     */
    public function get_page_change() {
        if ($this->_form->getSubmitValue('previousPage') !== null) {
            return -1;
        } else if ($this->_form->getSubmitValue('nextPage') !== null) {
            return 1;
        } else {
            return 0;
        }
    }
	
    /**
     *  Will add the buttons for changing the current page
     *
     *  @param int $page_num Current page that the form is on
     *  @param int $num_assessments The number of assessments that there are in total
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
    
    /**
     *  Will set up the data for each of the elements in the repeat_elements
     *  
     * @param object $assessments The array containing all of the assessments that was loaded from the database
     *
     *
     */
	function populate_from_db($assessments){
		$mform = $this->_form;
		$key = 0;
		
		foreach($assessments as $assessment){
			$index = '['.$key.']';
            
			if ($assessment->assessmentname == '') {
                $mform->setDefault('assessment_header'.$index, get_string('unnamed_assessment', 'local_metadata'));
            } else {
                $mform->setDefault('assessment_header'.$index, $assessment->assessmentname);
            }
            
			$mform->setDefault('assessmentname'.$index, $assessment->assessmentname);
			$mform->setDefault('assessmentweight'.$index, $assessment->assessmentweight);
			$mform->setDefault('type'.$index, array_search($assessment->type, get_assessment_types()));
			$mform->setDefault('assessmentprof'.$index, $assessment->assessmentprof);
			$mform->setDefault('assessmentexamtype'.$index, array_search($assessment->assessmentexamtype, get_exam_types()));
			$mform->setDefault('assessmentduedate'.$index, $assessment->assessmentduedate);
			$mform->setDefault('description'.$index, $assessment->description);
			$mform->setDefault('gdescription'.$index, $assessment->gdescription);
			$mform->setDefault('courseassessment_id'.$index, $assessment->id);
			
			$this->setup_data_from_database_for_assessment($mform, $index, $assessment);
			$key += 1;
		}
		
	}
	
	/**
     *  For the current assessment, will populate the learning objectives, related assessments, and topics from the database
     *
	 *  Stolen from assessment_form
     *
     *  @param int $mform Form that will be added to
     *  @param string $index Index that must be used to access form elements for the current assessment
     *  @param object $assessment The database tuple for the current assessment
     *
     */
	function setup_data_from_database_for_assessment($mform, $index, $assessment) {
        global $DB;
        // Load the learning objectives for the assessment
        // Template for this was found in \mod\glossary\edit.php
        if ($learningObjectivesArr = $DB->get_records_menu("assessmentobjectives", array('assessmentid'=>$assessment->id), '', 'id, objectiveid')) {
            $learningObjectiveTypes = get_learning_objective_types();
            foreach ($learningObjectiveTypes as $learningObjectiveType) {
                $mform->setDefault('learning_objective_'.$learningObjectiveType.$index, array_values($learningObjectivesArr));
            }
            
        }
	}
    
    /**
	 * Add form elements for uploading all assessments
     *
     *  @param int $num_assessments number of assessments saved in the database
     *
	 */
	function add_upload($num_assessments){
		$mform = $this -> _form;
        
		$mform->addElement('header', 'upload_assessments_header', get_string('upload_assessments_header', 'local_metadata'));
		$mform->addHelpButton('upload_assessments_header', 'upload_assessments_header', 'local_metadata');
        $mform->setExpanded('upload_assessments_header', $num_assessments === 0);
		$mform->closeHeaderBefore('assessment_list_add_elements');
		
		$mform->addElement('filepicker', 'uploaded_assessments', get_string('assessment_filepicker', 'local_metadata'), null, array('maxbytes' => 0, 'accepted_types' => '.csv'));
		$mform->addElement('submit', 'upload_assessments', get_string('upload_assessments', 'local_metadata'));
	}
}
	

?>
