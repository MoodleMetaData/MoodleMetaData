<?php
require_once '../../config.php';
require_once $CFG->dirroot . '/lib/formslib.php';
require_once $CFG->dirroot . '/lib/datalib.php';

class categories_form extends moodleform {
	function definition() {
		global $CFG, $DB, $USER; // Declare our globals for use
		$mform = $this->_form; // Tell this object to initialize with the properties of the Moodle form.
		
		$category_list = array();
		
		$faculty_records = $DB->get_records('course_categories');

		
		$this->setup_categories($mform, $category_list);
		$this->setup_upload_categories($mform, $faculty_list);
	}
	
	/**
	 * Add form elements for displaying course categories uploaded by administrator.
	 * @param object $mform		form definition
	 * @param array $list		a list of formatted graduate attribute records
	 * @return void
	 */
	private function setup_categories($mform, $list){
		$mform->addElement('header', 'category_header', get_string('category_header', 'local_metadata'));
		
		$gradatt_selection = $mform->addElement ('select', 'course_category', get_string ( 'course_category', 'local_metadata' ), $list);
		$gradatt_selection->setMultiple(true);
		
		// Delete Button
		$mform->addElement ( 'submit', 'delete_category', get_string ( 'delete_category', 'local_metadata' ) );
		$mform->addHelpButton('delete_category', 'delete_category', 'local_metadata');
	}
	
	/**
	 * Add form elements for upload course categories.
	 * @param object $mform		form definition
	 * @return void
	 */
	private function setup_upload_categories($mform, $faculty_list){
		$mform->addElement('header', 'upload_category_header', get_string('upload_category_header', 'local_metadata'));
		$mform->addHelpButton('upload_category_header', 'upload_category_header', 'local_metadata');
		
		$mform->addElement('text', 'category_label', get_string('category_label', 'local_metadata'));
		
		$faculty_selection = $mform->addElement ('select', 'course_faculty', get_string ( 'course_faculty', 'local_metadata' ), $faculty_list);
		
		$mform->addElement('filepicker', 'temp_gradatt', get_string('file'), null, array('maxbytes' => 0, 'accepted_types' => '.csv'));
		$mform->addElement('submit', 'sumbit_category', get_string('submit_category', 'local_metadata'));
	}
	
	/**
	 * Upload graduate attributes and insert all entries to graduateattributes table.
	 * @param object $mform		form definition
	 * @return void
	 */
	private function upload_graduate_attributes($mform){
		global $DB, $CFG, $USER;
		
		$gradatt_was_uploaded = $mform->getSubmitValue('upload_gradatt');
		if($gradatt_was_uploaded){
	
			$files = $this->get_draft_files('temp_gradatt');
			if(!empty($files)){
				$file = reset($files); 
				$content = $file->get_content();
				$all_rows = explode("\n", $content);
				
				$current_parent = 0;
				foreach($all_rows as $row){
					$parsed = str_getcsv($row);
					if(!is_null($parsed[0])){
						// $parsed[0] is not empty, then it is the main level
						if($parsed[0] != ''){
							$parent = new stdClass();
							$parent->attribute = $parsed[1];
							$insert_gradatt = $DB->insert_record('graduateattributes', $parent, true, false);
							$current_parent = $insert_gradatt;
							
							$node = new stdClass();
							$node->attribute = $parsed[3];
							$node->node = $current_parent;
							$insert_sub_gradatt = $DB->insert_record('graduateattributes', $node, true, false);
						} else {
							$node = new stdClass();
							$node->attribute = $parsed[3];
							$node->node = $current_parent;
							$insert_sub_gradatt = $DB->insert_record('graduateattributes', $node, true, false);
						}
					}
				}
				
			}
		}
	}
	
	/**
	 * This function is used for uploading and deleting graduate attributes.
	 * @return void
	 */
	function definition_after_data() {
        parent::definition_after_data();
		global $general_url;
		
        $mform = $this->_form;
		
		//$this->delete_graduate_attributes($mform);
		$this->upload_graduate_attributes($mform);
	}
	
	// If you need to validate your form information, you can override the parent's validation method and write your own.
	function validation($data, $files) {
		$errors = parent::validation ( $data, $files );
		global $DB, $CFG, $USER; // Declare them if you need them
		
		return $errors;
	}
	
	// Saves data from form to the database. Passed in is the data
	public static function save_data($data) {
		global $CFG, $DB, $USER;
	}
	
	// Deletes all selected already existing elements from the database
	public static function delete_data($data) {
		global $CFG, $DB, $USER;
	
		foreach ($data->course_gradatt as $value) {
			// TODO : delete parent -> delete children
			if($getParent = $DB->get_record('graduateattributes', array('id'=>$value))){
				if(is_null($getParent->node)){
					// delete the corresponding record in course graduate attribute
					/*
					$delete_coursegradatt = $DB->delete_records('coursegradattributes', array('gradattid'=>$value));
					if($getChildren = $DB->get_records('graduateattributes', array('node'=>$value))){
						foreach($getChildren as $child){
							$delete_coursegradatt = $DB->delete_records('coursegradattributes', array('gradattid'=>$child));
						}
					}*/
					// it is parent -> delete parent and its children
					$delete_parent = $DB->delete_records('graduateattributes', array('id'=>$value));
					$delete_children = $DB->delete_records('graduateattributes', array('node'=>$value));
				} else {
					// it is the child -> delete child
					$delete_child = $DB->delete_records('graduateattributes', array('id'=>$value));
					// delete the corresponding record in course graduate attribute
					$delete_coursegradatt = $DB->delete_records('coursegradattributes', array('gradattid'=>$value));
				}
			}
		}
	
	}
	
}

?>
