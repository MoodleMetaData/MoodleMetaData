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
		$program_topics = $DB->get_records('learningobjectives', array ('objectivetype' => 'knowledge'), $sort='', $fields='objectivename', $limitfrom='', $limitnum='');
		//$mform->addRule('new_psla', get_string('required'), 'required', null, 'client');
		
		$psla_default = array();
		$i = 0;
		foreach ($program_topics as $value) {
			$psla_default[$i] = $value->objectivename;
			$i++;
		}
		
		$course_topic_selection = $mform->addElement('select', 'manage_knowledge', get_string('manage_knowledge', 'local_metadata'), $psla_default, '');
		$course_topic_selection->setMultiple(true);
		
		// Delete Button
		$mform->addElement('button', 'delete_knowledge', get_string('delete_knowledge', 'local_metadata'));
		
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
		
		return $errors;
	}
	
	public static function save_data($data) {
		global $CFG, $DB, $USER;
		$new_la = new stdClass();
		$new_la->objectivename = $data->new_knowledge;
		$new_la->objectivetype = 'knowledge';

		$insert_newla = $DB->insert_record('learningobjectives', $new_la, false);
	}
	
	public static function delete_data($data) {
		global $CFG, $DB, $USER;

		$old_la = new stdClass();
		$old_la->objectivename = $data->manage_knowledge;
		$old_la->objectivetype = 'knowledge';
		
		$delete_oldla = $DB->remove_records('learningobjectives', $old_la);
	}
}


?>
