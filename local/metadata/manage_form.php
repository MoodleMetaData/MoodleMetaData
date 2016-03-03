<?php
require_once '../../config.php';
require_once $CFG->dirroot.'/lib/formslib.php';
require_once $CFG->dirroot.'/lib/datalib.php';

class manage_form extends moodleform {
	function definition() {
		global $CFG, $DB, $USER; //Declare our globals for use
		$mform = $this->_form; //Tell this object to initialize with the properties of the Moodle form.

		// Form elements
		
		// Multiselect for program topics
		// Get all from DB
		$program_topics = array();
		$program_topics = $DB->get_records('learningobjectives', null, $sort='', $fields='objectivename', $limitfrom='', $limitnum='');
		
		$psla_default = array();
		$i = 0;
		foreach ($program_topics as $value) {
			$psla_default[$i] = $value->objectivename;
			$i++;
		}
		
		$course_topic_selection = $mform->addElement('select', 'manage_psla', get_string('manage_psla', 'local_metadata'), $psla_default, '');
		$course_topic_selection->setMultiple(true);
		
		// Delete Button
		$mform->addElement('button', 'delete_psla', get_string('delete_psla', 'local_metadata'));
		
		// Text box to add new program specific learning objectives
		$mform->addElement('text', 'new_psla', get_string('new_psla', 'local_metadata'), '');
		$mform->addRule('new_psla', get_string('required'), 'required', null, 'client');
		
		// Submit button
		$mform->addElement('submit', 'create_psla', get_string('create_psla', 'local_metadata'));
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
		$new_la->objectivename = $data->new_psla;

		$insert_newla = $DB->insert_record('learningobjectives', $new_la, false);
	}
	
	public static function update_form($data) {
		global $DB, $CFG, $USER;
		echo 'Starting Update';
		$program_topics = array();
		$program_topics = $DB->get_records('learningobjectives', null, $sort='', $fields='objectivename', $limitfrom='', $limitnum='');
		
		$psla_default = array();
		$i = 0;
		foreach ($program_topics as $value) {
			$psla_default[$i] = $value->objectivename;
			$i++;
		}
		echo 'Entering form';
		print_r ($data);
		$mform->setDefaut('manage_psla', $psla_default);
		echo 'Updating form dynamically';
	}
	
}


?>
