<?php
require_once '../../config.php';
require_once $CFG->dirroot.'/lib/formslib.php';
require_once $CFG->dirroot.'/lib/datalib.php';

/*
 *
 * This form is to be used to mark which headers should be excluded from the syllabus
 *
 * Headers are:
 *   Course_Description
 *   Course_Readings
 *   Course_Objectives
 *   Grading
 *   Course_Sessions
 *   Policy
 *
 *
 */
class exclude_form extends moodleform {
	function definition() {
		$mform = $this->_form; //Tell this object to initialize with the properties of the Moodle form.

        $this->setup_header_items($mform);
        
        $this->set_excluded_item_defaults($mform);
        
        $this->add_action_buttons();
	}
	
	/**
	 *  Add a checkbox for every item in the general tab
	 * @param $mform		form definition
	 * @return void
	 */
	private function setup_header_items($mform){
        $mform->addElement('checkbox', 'Course_Description', get_string('exclude_Course_Description', 'local_metadata'));
        $mform->addHelpButton('Course_Description', 'exclude_Course_Description', 'local_metadata');
        
        $mform->addElement('checkbox', 'Course_Objectives', get_string('exclude_Course_Objectives', 'local_metadata'));
        $mform->addHelpButton('Course_Objectives', 'exclude_Course_Objectives', 'local_metadata');
        
        $mform->addElement('checkbox', 'Grading', get_string('exclude_Grading', 'local_metadata'));
        $mform->addHelpButton('Grading', 'exclude_Grading', 'local_metadata');
        
        $mform->addElement('checkbox', 'Course_Sessions', get_string('exclude_Course_Sessions', 'local_metadata'));
        $mform->addHelpButton('Course_Sessions', 'exclude_Course_Sessions', 'local_metadata');
        
        $mform->addElement('checkbox', 'Policy', get_string('exclude_Policy', 'local_metadata'));
        $mform->addHelpButton('Policy', 'exclude_Policy', 'local_metadata');
	}
    
    /**
	 * Will set the checkboxes defaults to true iff there is an entry for its form and elementname in the database for the 
     *   current category
     *
	 * @param $mform		form definition
	 * @return void
	 */
	private function set_excluded_item_defaults($mform) {
        global $DB;
        
		// TODO: Properly get the category
        $category_id = 1;
        
        if ($requiredItems = $DB->get_records("excludedelements", array('category'=>$category_id), '', 'id, header')) {
            
            foreach ($requiredItems as $requiredItem) {
                // Mark its corresponding element as required
                    // Would be form_elementname
                $mform->setDefault($requiredItem->header, true);
            }
        }
	}
    
    public function save_data($data) {
        global $DB;
        
        // TODO: Properly get the category
        $category_id = 1;
        
        // Clear the table for the category
        $DB->delete_records('excludedelements', array('category'=>$category_id));
        
        // Remove the submitbutton, since it isn't part of the main form,
        // and trying to save it would cause issues
        unset($data->submitbutton);
        
        
        foreach ($data as $header=>$_) {
            
            // Insert into database
            $newRequired = new stdClass();
            $newRequired->category = $category_id;
            $newRequired->header = $header;
            $DB->insert_record('excludedelements', $newRequired);
        }
    }
}


?>
