<?php
require_once '../../config.php';
require_once $CFG->dirroot.'/lib/formslib.php';
require_once $CFG->dirroot.'/lib/datalib.php';

class knowledge_form extends moodleform {
	function definition() {
		global $CFG, $DB, $USER; //Declare our globals for use
		$mform = $this->_form; //Tell this object to initialize with the properties of the Moodle form.
		$this->setup_program_obj($mform);
		$this->setup_upload_program_obj($mform);
	}
	
	private function setup_program_obj($mform) {
		global $CFG, $DB, $USER;
		$mform->addElement('header', 'program_grp_header', get_string('program_grp_header', 'local_metadata'));
		
		$program_topics = array();
		$program_topics = $DB->get_records('objectivetypes', array ('category' => 1));
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
	 * @param $mform		form definition
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
				$type-> category = 1;
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
								
								//echo "writing group\n";
								$groupid = $DB->insert_record('objectivegroups', $group, true);
								//echo "wrote group\n";
								
								//$groupid = 1;
								
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
	
	function definition_after_data() {
		parent::definition_after_data();
		$mform = $this->_form;
	
		$this->upload_program_obj($mform);
	}
	
	//Custom validator for form data
	function validation($data, $files) {
		$errors = parent::validation($data, $files);
		global $DB, $CFG, $USER; //Declare them if you need them
	
		return $errors;
	}
	
	/**
	 * Saves new knowledge program objective to the DB
	 * @param $data	form data
	 * @return void
	 */
	public static function save_knowledge($data) {
		global $CFG, $DB, $USER;
		$new_la = new stdClass();
		$new_la->objectivename = $data->new_knowledge;
		$new_la->parent = 1;

		$insert_newla = $DB->insert_record('programobjectives', $new_la, false);
	}
	
	/**
	 * Saves new skills program objective to the DB
	 * @param $data	form data
	 * @return void
	 */
	public static function save_skills($data) {
		global $CFG, $DB, $USER;
		$new_la = new stdClass ();
		$new_la->objectivename = $data->new_skills;
		$new_la->parent = 2;
	
		$insert_newla = $DB->insert_record ( 'programobjectives', $new_la, false );
	}
	
	/**
	 * Saves new attitudes program objective to the DB
	 * @param $data	form data
	 * @return void
	 */
	public static function save_attitudes($data) {
		global $CFG, $DB, $USER;
		$new_la = new stdClass ();
		$new_la->objectivename = $data->new_attitudes;
		$new_la->parent = 3;
	
		$insert_newla = $DB->insert_record ( 'programobjectives', $new_la, false );
	}
	
	/**
	 * Deletes all selected program objective groups from the DB
	 * @param $data	form data
	 * @return void
	 */
	public static function delete_groups($data) {
		global $CFG, $DB, $USER;

		foreach ($data->manage_groups as $value) {
			$delete_oldla = $DB->delete_records('objectivetypes', array('id'=>$value));
		}

	}
	
	/**
	 * Deletes all selected skills program objectives from the DB
	 * @param $data	form data
	 * @return void
	 */
	public static function delete_skills($data) {
		global $CFG, $DB, $USER;
	
		foreach ($data->manage_skills as $value) {
			$delete_oldla = $DB->delete_records('programobjectives', array('id'=>$value));
		}
	
	}
	
	/**
	 * Deletes all selected attitudes program objectives from the DB
	 * @param $data	form data
	 * @return void
	 */
	public static function delete_attitudes($data) {
		global $CFG, $DB, $USER;
	
		foreach ($data->manage_attitudes as $value) {
			$delete_oldla = $DB->delete_records('programobjectives', array('id'=>$value));
		}
	
	}
}


?>
