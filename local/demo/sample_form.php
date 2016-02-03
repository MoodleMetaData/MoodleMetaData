<?php
require_once '../../config.php';
require_once $CFG->dirroot.'/lib/formslib.php';
require_once $CFG->dirroot.'/lib/datalib.php';

class sample_form extends moodleform {
	function definition() {
		global $CFG, $DB, $USER; //Declare our globals for use
		$mform = $this->_form; //Tell this object to initialize with the properties of the Moodle form.

		//Add all your form elements here
		$mform->addElement('text', 'email', get_string('email'));

		//get all courses from database
		$courses_query = "SELECT * FROM {course}";
		$courses = array();
		if($result=$DB->get_records_sql($courses_query)){
			foreach($result as $item){
				$courses[] = $item->shortname;
			}
		}
		
		$select = $mform->addElementaddElement('select', 'courses', get_string('courses'), $courses);

		//normally you use add_action_buttons instead of this code
		$buttonarray=array();
		$buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('savechanges'));
		$buttonarray[] = &$mform->createElement('reset', 'resetbutton', get_string('revert'));
		$buttonarray[] = &$mform->createElement('cancel');
		$mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
		$mform->closeHeaderBefore('buttonar');
	}
	
	//If you need to validate your form information, you can override  the parent's validation method and write your own.	
	function validation($data, $files) {
		$errors = parent::validation($data, $files);
		global $DB, $CFG, $USER; //Declare them if you need them

		//if ($data['data_name'] Some condition here)  {
		//	$errors['element_to_display_error'] = get_string('error', 'local_demo_plug-in');
		//}
	}
}

?>

