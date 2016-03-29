<?php
require_once '../../config.php';
require_once $CFG->dirroot.'/lib/formslib.php';
require_once $CFG->dirroot.'/lib/datalib.php';

class knowledge_form extends moodleform {
	function definition() {
		global $CFG, $DB, $USER; //Declare our globals for use
		$mform = $this->_form; //Tell this object to initialize with the properties of the Moodle form.

		$this->setup_knowledge_program_obj($mform);
		$this->setup_skills_program_obj($mform);
		$this->setup_attitudes_program_obj($mform);
		$this->setup_upload_program_obj($mform);
	}
	
	/**
	 * Add form elements for upload a file for program objective.
	 * @param $mform		form definition
	 * @return void
	 */
	private function setup_upload_program_obj($mform){
		$mform->addElement('header', 'program_obj_header', get_string('program_obj_header', 'local_metadata'));
		$mform->addHelpButton('program_obj_header', 'program_obj_header', 'local_metadata');
		$mform->addElement('filepicker', 'temp_program_obj', get_string('file'), null, array('maxbytes' => 0, 'accepted_types' => '.csv'));
		$mform->addElement('submit', 'upload_program_obj', get_string('upload_program_obj', 'local_metadata'));
	}
	
	/**
	 * Add form elements for creation of knowledge objectives.
	 * @param $mform		form definition
	 * @return void
	 */
	private function setup_knowledge_program_obj($mform){
		global $CFG, $DB, $USER;
		$mform->addElement('header', 'program_knowledge_header', get_string('program_knowledge_header', 'local_metadata'));
		// Form elements
		
		// Multiselect for program topics
		// Get all from DB
		$program_topics = array();
		$program_topics = $DB->get_records('programobjectives', array ('objectivetype' => 1));
		//$mform->addRule('new_psla', get_string('required'), 'required', null, 'client');
		
		$psla_default = array();
		foreach ($program_topics as $value) {
			$psla_default[$value->id] = $value->objectivename;
		}
		
		$course_topic_selection = $mform->addElement('select', 'manage_knowledge', get_string('manage_knowledge', 'local_metadata'), $psla_default, '');
		$course_topic_selection->setMultiple(true);
		
		// Delete Button
		$mform->addElement('submit', 'delete_knowledge', get_string('delete_knowledge', 'local_metadata'));
		
		// Text box to add new program specific learning objectives
		$mform->addElement('text', 'new_knowledge', get_string('new_knowledge', 'local_metadata'), '');
		$mform->setType('new_knowledge', PARAM_RAW);
		
		// Submit button
		$mform->addElement('submit', 'create_knowledge', get_string('create_knowledge', 'local_metadata'));
	}
	
	/**
	 * Add form elements for creation of skillsobjectives.
	 * @param $mform		form definition
	 * @return void
	 */
	private function setup_skills_program_obj($mform) {
		global $CFG, $DB, $USER;
		$mform->addElement('header', 'program_skills_header', get_string('program_skills_header', 'local_metadata'));
		
		$program_topics = array ();
		$program_topics = $DB->get_records ( 'programobjectives', array ('objectivetype' => 2) );
		// $mform->addRule('new_psla', get_string('required'), 'required', null, 'client');
		
		$psla_default = array ();
		foreach ( $program_topics as $value ) {
			$psla_default [$value->id] = $value->objectivename;
		}
		
		$course_topic_selection = $mform->addElement ( 'select', 'manage_skills', get_string ( 'manage_skills', 'local_metadata' ), $psla_default, '' );
		$course_topic_selection->setMultiple ( true );
		
		// Delete Button
		$mform->addElement ( 'submit', 'delete_skills', get_string ( 'delete_skills', 'local_metadata' ) );
		
		// Text box to add new program specific learning objectives
		$mform->addElement ( 'text', 'new_skills', get_string ( 'new_skills', 'local_metadata' ), '' );
		$mform->setType('new_skills', PARAM_RAW);
		
		// $add_group =& $mform->addRule('new_psla', get_string('required'), 'required', null, 'client');
		
		// Submit button
		$mform->addElement ( 'submit', 'create_skills', get_string ( 'create_skills', 'local_metadata' ) );
	}
	
	/**
	 * Add form elements for creation of attitudes objectives.
	 * @param $mform		form definition
	 * @return void
	 */
	private function setup_attitudes_program_obj($mform) {
		global $CFG, $DB, $USER;
		$mform->addElement('header', 'program_attitudes_header', get_string('program_attitudes_header', 'local_metadata'));
		
		// Multiselect for program topics
		// Get all from DB
		$program_topics = array ();
		$program_topics = $DB->get_records ( 'programobjectives', array ('objectivetype' => 3));
		// $mform->addRule('new_psla', get_string('required'), 'required', null, 'client');
		
		$psla_default = array ();
		foreach ( $program_topics as $value ) {
			$psla_default [$value->id] = $value->objectivename;
		}
		
		$course_topic_selection = $mform->addElement ( 'select', 'manage_attitudes', get_string ( 'manage_attitudes', 'local_metadata' ), $psla_default, '' );
		$course_topic_selection->setMultiple ( true );
		
		// Delete Button
		$mform->addElement ( 'submit', 'delete_attitudes', get_string ( 'delete_attitudes', 'local_metadata' ) );
		
		// Text box to add new program specific learning objectives
		$mform->addElement ( 'text', 'new_attitudes', get_string ( 'new_attitudes', 'local_metadata' ), '' );
		$mform->setType('new_attitudes', PARAM_RAW);
		
		// $add_group =& $mform->addRule('new_psla', get_string('required'), 'required', null, 'client');
		
		// Submit button
		$mform->addElement ( 'submit', 'create_attitudes', get_string ( 'create_attitudes', 'local_metadata' ) );
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
	
				foreach($all_rows as $row){
					$parsed = str_getcsv($row);
						
					if(!is_null($parsed[0])){
						if($parsed[0] != ''){
							$this->insert_program_objective($parsed[0], 1);
						}
						if($parsed[1] != ''){
							$this->insert_program_objective($parsed[1], 2);
						}
						if($parsed[2] != ''){
							$this->insert_program_objective($parsed[2], 3);
						}
					}
						
				}
			}
		}
	}
	
	/**
	 * Insert a record to program objective table.
	 * @param $name		program objective name
	 * @param $type		program objective type
	 * @return void
	 */
	private function insert_program_objective($name, $type){
		global $DB, $CFG, $USER; //Declare them if you need them
		$info = new stdClass();
		$info->objectivename = $name;
		$info->objectivetype = $type;
		
		$table = 'programobjectives';
		$select = $DB->sql_compare_text('objectivename')." = '".$info->objectivename."' AND objectivetype = ".$info->objectivetype;
		$check = $DB->get_records_select($table, $select);
		if (count($check) == 0) {
			$insert_learningobj = $DB->insert_record('programobjectives', $info, false);
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
	
		// Validate that on creating a new objective it is not empty or already in the database
		if (!empty($data['create_knowledge'])) {
			if(empty($data['new_knowledge'])) {
				$errors['new_knowledge'] = get_string('mcreate_required', 'local_metadata');
			} else {
				$table = 'programobjectives';
				$select = $DB->sql_compare_text('objectivename')." = '".$data['new_knowledge']."' AND objectivetype = 1";
				$check = $DB->get_records_select($table, $select);
				if (count($check) != 0) {
					$errors['new_knowledge'] = get_string('psla_exists', 'local_metadata');
				}
			}
		}
		if (!empty($data['create_skills'])) {
			if(empty($data['new_skills'])) {
				$errors['new_skills'] = get_string('mcreate_required', 'local_metadata');
			} else {
				$table = 'programobjectives';
				$select = $DB->sql_compare_text('objectivename')." = '".$data['new_skills']."' AND objectivetype = 2";
				$check = $DB->get_records_select($table, $select);
				if (count($check) != 0) {
					$errors['new_skills'] = get_string('psla_exists', 'local_metadata');
				}
			}
		}
		if (!empty($data['create_attitudes'])) {
			if(empty($data['new_attitudes'])) {
				$errors['new_attitudes'] = get_string('mcreate_required', 'local_metadata');
			} else {
				$table = 'programobjectives';
				$select = $DB->sql_compare_text('objectivename')." = '".$data['new_attitudes']."' AND objectivetype = 3";
				$check = $DB->get_records_select($table, $select);
				if (count($check) != 0) {
					$errors['new_attitudes'] = get_string('psla_exists', 'local_metadata');
				}
			}
		}
	
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
		$new_la->objectivetype = 1;

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
		$new_la->objectivetype = 2;
	
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
		$new_la->objectivetype = 3;
	
		$insert_newla = $DB->insert_record ( 'programobjectives', $new_la, false );
	}
	
	/**
	 * Deletes all selected knowledge program objectives from the DB
	 * @param $data	form data
	 * @return void
	 */
	public static function delete_knowledge($data) {
		global $CFG, $DB, $USER;

		foreach ($data->manage_knowledge as $value) {
			$delete_oldla = $DB->delete_records('programobjectives', array('id'=>$value));
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
