<?php 
require_once '../../config.php';
require_once $CFG->dirroot.'/lib/formslib.php';
require_once $CFG->dirroot.'/lib/datalib.php';


class tag_form extends moodleform {
	function definition() {
		global $CFG, $DB, $USER; //Declare our globals for use
		global $course, $courseId, $objectiveId, $groupId;
		
		$mform = $this->_form; //Tell this object to initialize with the properties of the Moodle form.
		
		// Dropdown select for course objectives
		$objoptions = array();
		$courseobj = $DB->get_records ( 'courseobjectives', array ('courseid' => $course->id));
		foreach($courseobj as $record) {
			$objective = $DB->get_record ( 'learningobjectives', array ('id' => $record->objectiveid));
			$objoptions[$record->id] = $objective->objectivename;
		}
		
		$selectobjectives = $mform->addElement('select', 'admobj_select', get_string('admobj_select', 'local_metadata'), $objoptions, '');
		
		$mform->addElement('submit', 'admselcourse', get_string('admselcourse', 'local_metadata'));
		
		if($objectiveId != -1) {
			// Dropdown select for objective groups
			$objoptions = array();
			$groupobj = $DB->get_records ( 'objectivegroups', array ());
			foreach($groupobj as $record) {
				$objoptions[$record->id] = $record->groupname;
			}
			
			$selectobjectives = $mform->addElement('select', 'group_select', get_string('group_select', 'local_metadata'), $objoptions, '');
			$mform->setDefault('group_select', $groupId);
			
			$mform->addElement('submit', 'groupsel', get_string('admselcourse', 'local_metadata'));
			
			//Set defaults
			$mform->setDefault('admobj_select', $objectiveId);
			
			// Multiselect for program objectives
			$programoptions = array();
			$progobj = $DB->get_records ( 'programobjectives', array('objectivegroup' => $groupId));
			foreach($progobj as $record) {
				if($record->parent == '') {
					$programoptions[$record->id] = $record->objectivename;
				} else {
					$programoptions[$record->id] = '- '.$record->objectivename;
				}
			}
			$programobj_select = $mform->addElement('select', 'admpro_select', get_string('admpro_select', 'local_metadata'), $programoptions,'');
			$programobj_select->setMultiple ( true );
			
			$mform->addElement('submit', 'admaddobjective', get_string('admaddobjective', 'local_metadata'));
			
			$currentoptions = array();
			$curtags = $DB->get_records ( 'programpolicytag', array('courseid' => $courseId, 'tagid' => $objectiveId));
			foreach($curtags as $record) {
				$objective = $DB->get_record ( 'programobjectives', array ('id' => $record->objectiveid));
				$currentoptions[$record->id] = $objective->objectivename;
			}
			$programobj_select = $mform->addElement('select', 'admpro_current', get_string('admpro_current', 'local_metadata'), $currentoptions,'');
			$programobj_select->setMultiple ( true );
				
			$mform->addElement('submit', 'admdelobjective', get_string('admdelobjective', 'local_metadata'));
		}
	}
	
	/**
	 * Ensure that the data the user entered is valid.
	 * @see lib/moodleform#validation()
	 */
	function validation($data, $files) {
		$errors = parent::validation($data, $files);
	
		if (!empty($data['admselcourse'])) {
			if(empty($data['admobj_select'])) {
				$errors['admobj_select'] = get_string('err_required', 'local_metadata');
			}
		} else if (!empty($data['group_sel'])) {
			if(empty($data['group_select'])) {
				$errors['group_select'] = get_string('err_required', 'local_metadata');
			}
		} else if (!empty($data['admaddobjective'])) {
			if(empty($data['admpro_select'])) {
				$errors['admpro_select'] = get_string('err_required', 'local_metadata');
			}
		} else if (!empty($data['admdelobjective'])) {
			if(empty($data['admpro_current'])) {
				$errors['admpro_current'] = get_string('err_required', 'local_metadata');
			}
		}
	
		return $errors;
	}
	
	/**
	 * Grabs the objective ID for url param
	 * @param $data data from the form
	 * @return objective id
	 */
	public static function get_obj($data){
		global $CFG, $DB, $USER;
		return $data->admobj_select;
	}
	
	/**
	 * Grabs the group ID for url param
	 * @param $data data from the form
	 * @return objective id
	 */
	public static function get_grp($data){
		global $CFG, $DB, $USER;
		return $data->group_select;
	}
	
	/**
	 * Saves the program objective links to the course learning objectives
	 * @param $data form data
	 * @return null
	 */
	public static function add_tags($data) {
		global $CFG, $DB, $USER; 
		global $courseId, $objectiveId;
		$newtags = new stdClass ();
		$newtags->courseid = $courseId;
		$newtags->tagid = $objectiveId;
		
		foreach ($data->admpro_select as $value) {
			$newtags->objectiveid = $value;
			$insert_tags = $DB->insert_record ( 'programpolicytag', $newtags, false );
		}
		
	}
	
	/**
	 * Removes program objective links from course learning objectives
	 * @param $data form data
	 * @return null
	 */
	public static function remove_tags($data){
		global $CFG, $DB, $USER;

		foreach ($data->admpro_current as $value) {
			$remove_tags = $DB->delete_records ( 'programpolicytag', array('id' => $value) );
		}
	}
	
}
?>