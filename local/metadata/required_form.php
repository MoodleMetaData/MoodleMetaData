<?php
require_once '../../config.php';
require_once $CFG->dirroot.'/lib/formslib.php';
require_once $CFG->dirroot.'/lib/datalib.php';

/*
 *
 * Currently, it doesn't allow selecting date items as being required. Same for learning objectives, or topics, since they may not apply.
 *
 * Requires get_data to be called.
 *
 * Implementation Note: All elements will be of the form:
 *     <form_belong_to>_<element_name>
 *
 *     Where form_belong_to would be general, assessment, or session.
 *     Then, the element_name will be what the name of it is on that form
 *
 */
class required_form extends moodleform {
	function definition() {
		$mform = $this->_form; //Tell this object to initialize with the properties of the Moodle form.

		$this->setup_general_items($mform);
		$this->setup_assessment_items($mform);
		$this->setup_session_items($mform);
        
        $this->set_required_item_defaults($mform);
        
        $this->add_action_buttons();
	}
	
	/**
	 *  Add a checkbox for every item in the general tab
	 * @param $mform		form definition
	 * @return void
	 */
	private function setup_general_items($mform){
		$mform->addElement('header', 'admin_general_header', get_string('admin_general_header', 'local_metadata'));
        $mform->addHelpButton('admin_general_header', 'admin_general_header', 'local_metadata');
        
        $mform->setExpanded('admin_general_header', false);
        
        $mform->addElement('checkbox', 'general_course_instructor', get_string('course_instructor', 'local_metadata'));
        
        $mform->addElement('checkbox', 'general_course_email', get_string('course_email', 'local_metadata'));
        $mform->addElement('checkbox', 'general_course_phone', get_string('course_phone', 'local_metadata'));
        $mform->addElement('checkbox', 'general_course_office', get_string('course_office', 'local_metadata'));
        $mform->addElement('checkbox', 'general_course_officeh', get_string('course_officeh', 'local_metadata'));
        
        $mform->addElement('checkbox', 'general_course_description', get_string('course_description', 'local_metadata'));
        $mform->addElement('checkbox', 'general_teaching_assumption', get_string('teaching_assumption', 'local_metadata'));
	}
	
	/**
	 *  Add a checkbox for every item in the session tab
	 * @param $mform		form definition
	 * @return void
	 */
	private function setup_assessment_items($mform){
		$mform->addElement('header', 'admin_assessment_header', get_string('admin_assessment_header', 'local_metadata'));
        $mform->addHelpButton('admin_assessment_header', 'admin_assessment_header', 'local_metadata');
        
        $mform->addElement('checkbox', 'assessment_assessmentname', get_string('assessment_title', 'local_metadata'));
        $mform->addElement('checkbox', 'assessment_type', get_string('assessment_type', 'local_metadata'));
        $mform->addElement('checkbox', 'assessment_assessmentprof', get_string('assessment_prof', 'local_metadata'));
        
        $mform->addElement('checkbox', 'assessment_description', get_string('assessment_description', 'local_metadata'));
        $mform->addElement('checkbox', 'assessment_gradingDescription_uploaded', get_string('require_uploaded_rubric', 'local_metadata'));
        $mform->addElement('checkbox', 'assessment_gdescription', get_string('assessment_grading_desc', 'local_metadata'));
        
        $mform->addElement('checkbox', 'assessment_assessmentweight', get_string('grade_weight', 'local_metadata'));
	}
	
	/**
	 * Add form elements for creation of skillsobjectives.
	 * @param $mform		form definition
	 * @return void
	 */
	private function setup_session_items($mform) {
		$mform->addElement('header', 'admin_session_header', get_string('admin_session_header', 'local_metadata'));
        $mform->addHelpButton('admin_session_header', 'admin_session_header', 'local_metadata');
        
        
        $mform->addElement('checkbox', 'session_sessiontitle', get_string('session_title', 'local_metadata'));
        $mform->addElement('checkbox', 'session_sessionguestteacher', get_string('session_guest_teacher', 'local_metadata'));
        $mform->addElement('checkbox', 'session_sessiontype', get_string('session_type', 'local_metadata'));
        $mform->addElement('checkbox', 'session_sessionlength', get_string('session_length', 'local_metadata'));
        $mform->addElement('checkbox', 'session_sessionteachingstrategy', get_string('session_teaching_strategy', 'local_metadata'));
	}
    
    /**
	 * Will set the checkboxes defaults to true iff there is an entry for its form and elementname in the database for the 
     *   current category
     *
	 * @param $mform		form definition
	 * @return void
	 */
	private function set_required_item_defaults($mform) {
        global $DB;
        
		// TODO: Properly get the category
        $category_id = 1;
        
        if ($requiredItems = $DB->get_records("requiredelements", array('category'=>$category_id), '', 'id, form, elementname')) {
            
            foreach ($requiredItems as $requiredItem) {
                // Mark its corresponding element as required
                    // Would be form_elementname
                $mform->setDefault($requiredItem->form . '_' . $requiredItem->elementname, true);
            }
        }
	}
    
    public function save_data($data) {
        global $DB;
        
        // TODO: Properly get the category
        $category_id = 1;
        
        // Clear the table for the category
        $DB->delete_records('requiredelements', array('category'=>$category_id));
        
        
        // Remove the submitbutton, since it isn't part of the main form,
        // and trying to save it would cause issues
        unset($data->submitbutton);
        
        foreach ($data as $key=>$_) {
            // Break up the key. 
            // Only break into two separate
            $split = explode('_', $key, 2);
            
            // Insert into database
            $newRequired = new stdClass();
            $newRequired->category = $category_id;
            $newRequired->form = $split[0];
            $newRequired->elementname = $split[1];
            $DB->insert_record('requiredelements', $newRequired);
        }
    }
	
}


?>
