<?php
require_once '../../config.php';
require_once $CFG->dirroot.'/lib/formslib.php';
require_once $CFG->dirroot.'/lib/datalib.php';

class knowledge_form extends moodleform {
	/**
	 * The form to display the tab for program objectives.
	 */
	function definition() {
		global $CFG, $DB, $USER; //Declare our globals for use
		global $categoryId;
		
		$mform = $this->_form; //Tell this object to initialize with the properties of the Moodle form.
		$this->setup_program_obj($mform);
		$this->setup_upload_program_obj($mform);
	}
	
	/**
	 * Add form elements for deleting program objective groups
	 * @param object $mform	a form definition
	 * @return void
	 */
	private function setup_program_obj($mform) {
		global $CFG, $DB, $USER;
		global $categoryId;
		
		$mform->addElement('header', 'program_grp_header', get_string('program_grp_header', 'local_metadata'));
		
		$program_topics = array();
		$program_topics = $DB->get_records('objectivetypes', array ('category' => $categoryId));
		//$mform->addRule('new_psla', get_string('required'), 'required', null, 'client');
		
		$psla_default = array();
		foreach ($program_topics as $value) {
			$psla_default[$value->id] = $value->typename;
		}
		
		$course_topic_selection = $mform->addElement('select', 'manage_groups', get_string('manage_groups', 'local_metadata'), $psla_default, '');
		$course_topic_selection->setMultiple(true);
		
		// Delete Button
		$mform->addElement('submit', 'delete_groups', get_string('delete_groups', 'local_metadata'));
	}
	
	/**
	 * Add form elements for upload a file for program objective.
	 * @param object $mform		form definition
	 * @return void
	 */
	private function setup_upload_program_obj($mform){
		$mform->addElement('header', 'program_obj_header', get_string('program_obj_header', 'local_metadata'));
		
		// Text box to add new program specific learning objectives
		$mform->addElement('text', 'new_group', get_string('new_group', 'local_metadata'), '');
		//$mform->addRule('new_group', get_string('err_required', 'local_metadata'), 'required', null, 'client');
		$mform->setType('new_group', PARAM_RAW);
		
		$mform->addHelpButton('program_obj_header', 'program_obj_header', 'local_metadata');
		$mform->addElement('filepicker', 'temp_program_obj', get_string('file'), null, array('maxbytes' => 0, 'accepted_types' => '.csv'));
		$mform->addElement('submit', 'upload_program_obj', get_string('upload_program_obj', 'local_metadata'));
	}
	
	/**
	 * Upload program objectives.
	 * @param $mform	form definition
	 * @return void
	 */
	private function upload_program_obj($mform){
		global $DB, $CFG, $USER; 
		global $categoryId;
		
		$obj_was_uploaded = $mform->getSubmitValue('upload_program_obj');
		if($obj_was_uploaded){
	
			$files = $this->get_draft_files('temp_program_obj');
			if(!empty($files)){
				$file = reset($files);
				$content = $file->get_content();
				$all_rows = explode("\n", $content);
				$groups = array();
				$titles = array();
				
				$type = new stdClass();
				$type->typename = $mform->getSubmitValue('new_group');
				$type-> category = $categoryId;
				$masterid = $DB->insert_record('objectivetypes', $type, true);
	
				foreach($all_rows as $row){
					$parsed = str_getcsv($row);
						
					if(!is_null($parsed[0])){
						if($parsed[0] != '' && $parsed[1] != ''){
							$parsed[0] = fix_utf8($parsed[0]);
							$parsed[1] = fix_utf8($parsed[1]);
							if(!array_key_exists($parsed[0], $groups)) {
								$group = new stdClass();
								$group->groupname = $parsed[0];
								$group->parent = $masterid;

								$groupid = $DB->insert_record('objectivegroups', $group, true);
								
								$groups[$parsed[0]] = $groupid; // set to ID
							}
							if(!array_key_exists($parsed[1], $titles)) {
								$title = new stdClass();
								$title->objectivename = $parsed[1];
								$title->parent = null;
								$title->objectivegroup = $groups[$parsed[0]];
								
								//echo "writing title\n";
								//echo $parsed[1];
								$titleid = $DB->insert_record('programobjectives', $title, true);
								//echo "wrote title\n";
								//$titleid = 1;
								$titles[$parsed[1]] = $titleid; // set to ID
							}
							if($parsed[2] != '') {
								$parsed[2] = fix_utf8($parsed[2]);
								
								$title = new stdClass();
								$title->objectivename = $parsed[2];
								$title->parent =  $titles[$parsed[1]];
								$title->objectivegroup = $groups[$parsed[0]];
								
								$DB->insert_record('programobjectives', $title, false);
							}
							//$this->insert_program_objective($groups[$parsed[0]], $groups[$parsed[1]], $parsed[2]);
						}
					}
					
						
				}
			}
		}
	}
	
	/**
	 * This function is used for uploading program objectives
	 * @return void
	 */
	function definition_after_data() {
		parent::definition_after_data();
		$mform = $this->_form;
	
		$this->upload_program_obj($mform);
	}
	
	/**
	 * Ensure that the data the user entered is valid.
	 * @see lib/moodleform#validation()
	 */
	function validation($data, $files) {
		$errors = parent::validation($data, $files);
	
		return $errors;
	}
	
	/**
	 * Deletes all selected program objective groups from the DB
	 * @param object $data	data generated by the form
	 * @return void
	 */
	public static function delete_groups($data) {
		global $CFG, $DB, $USER;

		foreach ($data->manage_groups as $value) {
			$group_records = array();
			
			// grab groups to delete
			$group_records = $DB->get_records('objectivegroups', array ('parent' => $value));
			
			foreach ($group_records as $group) {
				// grab all members of groups to delete
				$program_records = array();
				$program_records = $DB->get_records('programobjectives', array('objectivegroup' => $group->id));
				foreach ($program_records as $program) {
					//delete all tagged program policies
					//print_r($program);
					$DB->delete_records('programpolicytag', array('tagid' => $program->id));
					$DB->delete_records('programobjectives', array('id' => $program->id));
				}
				$DB->delete_records('objectivegroups', array('id' => $group->id));
			}
			$DB->delete_records('objectivetypes', array('id'=>$value));
		}

	}
	
}


?>
