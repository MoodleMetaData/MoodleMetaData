<?php
require_once '../../config.php';
require_once $CFG->dirroot . '/lib/formslib.php';
require_once $CFG->dirroot . '/lib/datalib.php';

/**
 * The form to display the tab for graduate attributes.
 */
class gradatt_form extends moodleform {
	function definition() {
		global $CFG, $DB, $USER; // Declare our globals for use
		$mform = $this->_form; // Tell this object to initialize with the properties of the Moodle form.
		
		$gradatt_records = array ();
		$gradatt_records = $DB->get_records('graduateattributes', null, 'id');
		$gradatt_list = array ();
		
		$parent_number = 0;
		$child_number = 1;
		
		foreach($gradatt_records as $value) {
			if(is_null($value->node)){
				// parent
				$parent_number += 1;
				$gradatt_list[$value->id] = $parent_number.'. '.$value->attribute;
				$child_number = 1; // reset child number
			} else {
				// child
				$gradatt_list[$value->id] = '&nbsp;&nbsp;&nbsp;'.$parent_number.'.'.$child_number.'. '.$value->attribute;
				$child_number += 1;
			}
			
		}
		
		$this->setup_graduate_attributes($mform, $gradatt_list);
		$this->setup_upload_gradatt($mform);
		
	}
	
	/**
	 * Add form elements for displaying graduate attributes.
	 * @param object $mform		form definition
	 * @param array $list		a list of formatted graduate attribute records
	 * @return void
	 */
	private function setup_graduate_attributes($mform, $list){
		$mform->addElement('header', 'gradatt_header', get_string('course_gradatt_header', 'local_metadata'));
		$gradatt_selection = $mform->addElement ( 'select', 'course_gradatt', get_string ( 'course_gradatt', 'local_metadata' ), $list, 'size="15",  style="width: 500px;"');
		$gradatt_selection->setMultiple(true);
		
		// Delete Button
		$mform->addElement ( 'submit', 'delete_gradatt', get_string ( 'delete_gradatt', 'local_metadata' ) );
		$mform->addHelpButton('course_gradatt', 'course_gradatt', 'local_metadata');
		
	}
	
	/**
	 * Add form elements for upload graduate attributes.
	 * @param object $mform		form definition
	 * @return void
	 */
	private function setup_upload_gradatt($mform){
		$mform->addElement('header', 'upload_gradatt_header', get_string('upload_gradatt_header', 'local_metadata'));
		$mform->addHelpButton('upload_gradatt_header', 'upload_gradatt_header', 'local_metadata');
		$mform->addElement('filepicker', 'temp_gradatt', get_string('file'), null, array('maxbytes' => 0, 'accepted_types' => '.csv'));
		$mform->addElement('submit', 'upload_gradatt', get_string('upload_gradatt', 'local_metadata'));
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
		
        $mform = $this->_form;

		$this->upload_graduate_attributes($mform);
	}
	
	/**
	 * Ensure that the data the user entered is valid.
	 * @see lib/moodleform#validation()
	 */
	function validation($data, $files) {
		$errors = parent::validation ( $data, $files );
		global $DB, $CFG, $USER; // Declare them if you need them
		
		return $errors;
	}
	
	/**
	 * Delete the graduate attributes and the corresponding record in course graduate attribute table.
	 * @param object $data		data generated by the form
	 * @return void
	 */
	public function delete_data($data) {
		global $CFG, $DB, $USER;
	
		foreach ($data->course_gradatt as $value) {
			if($getParent = $DB->get_record('graduateattributes', array('id'=>$value))){
				if(is_null($getParent->node)){
					// delete the corresponding record in course graduate attribute
					$delete_coursegradatt = $DB->delete_records('coursegradattributes', array('gradattid'=>$value));
					if($getChildren = $DB->get_records('graduateattributes', array('node'=>$value))){
						foreach($getChildren as $child){
							$delete_coursegradatt = $DB->delete_records('coursegradattributes', array('gradattid'=>$child->id));
						}
					}
					
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
