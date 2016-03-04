<?php
require_once '../../config.php';
require_once $CFG->dirroot.'/lib/formslib.php';
require_once $CFG->dirroot.'/lib/datalib.php';

class knowledge_form extends moodleform {
	function definition() {
		global $CFG, $DB, $USER; //Declare our globals for use
		$mform = $this->_form; //Tell this object to initialize with the properties of the Moodle form.

		// Form elements
		
		// Multiselect for program topics
		// Get all from DB
		$program_topics = array();
		$program_topics = $DB->get_records('learningobjectives', array ('objectivetype' => 'knowledge'));
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
		//$add_group =& $mform->addRule('new_psla', get_string('required'), 'required', null, 'client');
		
		// Submit button
		$mform->addElement('submit', 'create_knowledge', get_string('create_knowledge', 'local_metadata'));
	}
	
	//If you need to validate your form information, you can override  the parent's validation method and write your own.	
	function validation($data, $files) {
		$errors = parent::validation($data, $files);
		global $DB, $CFG, $USER; //Declare them if you need them
		
		// Validate that on creating a new objective it is not empty or already in the database
		if (!empty($data[create_knowledge])) {
			if(empty($data[new_knowledge])) {
				$errors['new_knowledge'] = get_string('mcreate_required', 'local_metadata');
			} else {
				$check = $DB->get_records('learningobjectives', array ('objectivename' => $data[new_knowledge],
						'objectivetype' => 'knowledge'));
				if (count($check) != 0) {
					$errors['new_knowledge'] = get_string('psla_exists', 'local_metadata');
				}
			}
		}
		
		return $errors;
	}
	
	// Saves data from form to the database. Passed in is the data
	public static function save_data($data) {
		global $CFG, $DB, $USER;
		$new_la = new stdClass();
		$new_la->objectivename = $data->new_knowledge;
		$new_la->objectivetype = 'knowledge';

		$insert_newla = $DB->insert_record('learningobjectives', $new_la, false);
	}
	
	// Deletes all selected already existing elements from the database
	public static function delete_data($data) {
		global $CFG, $DB, $USER;

		foreach ($data->manage_knowledge as $value) {
			$delete_oldla = $DB->delete_records('learningobjectives', array('id'=>$value));
		}

	}
}


?>
