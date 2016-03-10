<?php
require_once '../../config.php';
require_once $CFG->dirroot.'/lib/formslib.php';
require_once $CFG->dirroot.'/lib/datalib.php';

require_once 'lib.php';

/**
 * The form to display the tab for general information.
 */
class general_form extends moodleform {
	
	function definition() {
		global $CFG, $DB, $USER; //Declare our globals for use
		global $course, $courseId;           

		// initialize the form.
		$mform = $this->_form; //Tell this object to initialize with the properties of the Moodle form.
	
		// retrieve data from course info and instructor table
		$courseinfo = $DB->get_record('courseinfo', array('courseid'=>$courseId));
		if($courseinfo != NULL){
			$contactinfo = $DB->get_record('courseinstructors', array('courseid'=>$courseinfo->id, 'userid'=>$USER->id));
		} else {
			$contactinfo = NULL;
		}
		
		// setup form elements
		$this->setup_general($mform, $courseinfo, $contactinfo);
		$this->setup_contact($mform, $courseinfo, $contactinfo);
		$this->setup_description($mform, $courseinfo);
		$this->setup_format($mform, $courseinfo);
		$this->setup_upload_course_obj($mform);
		
		// setup course objectives
		$learning_objectives = get_course_learning_objectives();
		$knowledge_list = array();
		$skill_list = array();
		$attitude_list = array();

		foreach($learning_objectives as $obj){
			if($obj->objectivetype === 'Knowledge'){
				$knowledge_list[] = $this->get_learning_obj($obj->id, $obj->objectivename);
			}else if($obj->objectivetype === 'Skill'){
				$skill_list[] = $this->get_learning_obj($obj->id, $obj->objectivename);
			}else{
				$attitude_list[] = $this->get_learning_obj($obj->id, $obj->objectivename);
			}
		} 
				
		$this->setup_course_obj($mform, 'knowledge', $knowledge_list, 'skill');
		$this->setup_course_obj($mform, 'skill', $skill_list, 'attitude');
		$this->setup_course_obj($mform, 'attitude', $attitude_list, 'gradatt');
	
		//TODO: To be added once grad attribute has been decided
/*
		// setup graduate attributes
		$mform->addElement('header', 'course_gradatt_header', get_string('course_gradatt_header', 'local_metadata'));

		$gradAtt_array = array();

		// TODO: MANIPULATE THE LIST FROM DB
		$course_gradAtts = array();
		$course_gradAtts[] = 'attribute 1';
		$course_gradAtts[] = 'attribute 2';

		$gradAtt_array[] = $mform->createElement('select', 'gradAtt_option', get_string('course_gradAtt', 'local_metadata'), $course_gradAtts, '');
		$gradAtt_array[] = $mform->createElement('hidden', 'gradAtt_id', 0);

		if ($this->_instance){
			$repeatg = $DB->count_records('gradAtt_options', array('gradAtt_id'=>$this->_instance));
			$repeatg += 1;
		} else {
			$repeatg = 1;
		}

		$gradAtt_options = array();
		$mform->setType('gradAtt_option', PARAM_CLEANHTML);
		$mform->setType('gradAtt_id', PARAM_INT);
		$this->repeat_elements($gradAtt_array, $repeatg, $gradAtt_options, 'option_repeats4', 'option_add_fields_gradAtt', 1, get_string('add_gradAtt', 'local_metadata'), true);

*/
		// Add form buttons
		$this->add_action_buttons(true, "Save general information");
	}
	
	/**
	 * Add form elements for general course information.
	 */
	private function setup_general($mform, $courseinfo, $contactinfo){
		global $CFG, $DB, $USER; //Declare our globals for use
        global $course;           
	    $mform->addElement('header', 'course_general_header', get_string('course_general_header', 'local_metadata'));
				
		// Course shortname
		$courseCode = $course->shortname;
		$mform->addElement('static', 'course_code', get_string('course_code', 'local_metadata'));
		$mform->setDefault('course_code', $courseCode);
	
		$courseName = $course->fullname;
		$mform->addElement('static', 'course_name', get_string('course_name', 'local_metadata'));
		$mform->setDefault('course_name', $courseName);
				
		// Instructor
		$courseInstructor = $USER->lastname.', '.$USER->firstname;
		$course_instructor = $mform->addElement('text', 'course_instructor', get_string('course_instructor', 'local_metadata'), '');
		if($contactinfo){
			$mform->setDefault('course_instructor', $contactinfo->name);
		}else{
			$mform->setDefault('course_instructor', $courseInstructor);
		}
		$mform->addRule('course_instructor', get_string('required'), 'required', null, 'client');
		$mform->setType('course_instructor', PARAM_TEXT);
		
		// Faculty
		$course_faculty = $mform->addElement('text', 'course_faculty', get_string('course_faculty', 'local_metadata'), '');
		if($courseinfo){
			$mform->setDefault('course_faculty', $courseinfo->coursefaculty);
		}   
		$mform->addRule('course_faculty', get_string('required'), 'required', null, 'client');
		$mform->setType('course_faculty', PARAM_TEXT);

		// Program types
		// TODO: FETCH DATA FROM DBTO MANIPULATE THE LIST
		$program_types = array();
		$program_types[] = 'program type 1';
		$program_types[] = 'program type 2';
		// -------------------------------------
		$program_type_selection = $mform->addElement('select', 'program_type', get_string('program_type', 'local_metadata'), $program_types, '');
		$mform->addRule('program_type', get_string('required'), 'required', null, 'client');


		// Courses category
		// TODO: FETCH DATA FROM DBTO MANIPULATE THE LIST
		$course_categories = array();
		$course_categories[] = 'category 1';
		$course_categories[] = 'category 2';
		// -------------------------------------
		$course_category_selection = $mform->addElement('select', 'course_category', get_string('course_category', 'local_metadata'), $course_categories, '');
		$mform->addRule('course_category', get_string('required'), 'required', null, 'client');
		
		$mform->closeHeaderBefore('course_contact_header');  

		$mform->setExpanded('course_general_header');
	}
	
	/**
	 * Add form elements for course contact information.
	 */
	private function setup_contact($mform, $courseinfo, $contactinfo){
		global $CFG, $DB, $USER; //Declare our globals for use
        global $course;  
		$mform->addElement('header', 'course_contact_header', get_string('course_contact_header', 'local_metadata'));

		// Email
		$course_email = $mform->addElement('text', 'course_email', get_string('course_email', 'local_metadata'), '');
		if($contactinfo){
			$mform->setDefault('course_email', $contactinfo->email);
		}
		$mform->addRule('course_email', get_string('required'), 'required', null, 'client');
		$mform->setType('course_email', PARAM_TEXT);

		// Phone
		$course_phone = $mform->addElement('text', 'course_phone', get_string('course_phone', 'local_metadata'), '');
		if($contactinfo){
			$mform->setDefault('course_phone', $contactinfo->phonenumber);
		}
		$mform->setType('course_phone', PARAM_TEXT);

		// Office
		$course_office = $mform->addElement('text', 'course_office', get_string('course_office', 'local_metadata'), '');
		if($contactinfo){
			$mform->setDefault('course_office', $contactinfo->officelocation);
		}
		$mform->addRule('course_office', get_string('required'), 'required', null, 'client');  
		$mform->setType('course_office', PARAM_TEXT);

		// Office hours
		$course_officeh = $mform->addElement('text', 'course_officeh', get_string('course_officeh', 'local_metadata'), '');
		if($contactinfo){
			$mform->setDefault('course_officeh', $contactinfo->officehours);
		}
		$mform->setType('course_officeh', PARAM_TEXT);

		$mform->addRule('course_officeh', get_string('required'), 'required', null, 'client');

		$mform->closeHeaderBefore('course_desc_header');

		$mform->setExpanded('course_contact_header');
	}

	/**
	 * Add form elements for course description.
	 */
	private function setup_description($mform, $courseinfo){
		global $CFG, $DB, $USER; //Declare our globals for use
        global $course;
		$mform->addElement('header', 'course_desc_header', get_string('course_desc_header', 'local_metadata'));
		// TODO: EDITOR HAS AUTOSAVE AND AUTORESTORE DATA, WHICH WILL REMOVE THE FETCHED DATA FROM DB
		// Add editor for create or modify course description.              
		// Get default course description from DB.
		// If description does not exist in the extra table, display the default description.
				
		// Course summary
		$default_description = $course->summary;
		$course_description_editor = $mform->addElement('editor', 'course_description', get_string('course_description', 'local_metadata'));
		//$mform->addElement('textarea', 'course_description', get_string("course_description", "local_metadata"), 'wrap="virtual" rows="5" cols="70"');
		if($courseinfo){
			$current_description = $courseinfo->coursedescription;
			//$mform->setDefault('course_description', $current_description);
			$course_description_editor->setValue(array('text' => $current_description) );
		}else{
			//$mform->setDefault('course_description', $default_description);
			$course_description_editor->setValue(array('text' => $default_description) );
		}
		$mform->addRule('course_description', get_string('required'), 'required', null, 'client');
		$mform->setType('course_description', PARAM_TEXT);      

		$mform->closeHeaderBefore('course_format_header');

		$mform->setExpanded('course_desc_header');
	}
	
	/**
	 * Add form elements for course format.
	 */
	private function setup_format($mform, $courseinfo){
		global $CFG, $DB, $USER; //Declare our globals for use
        global $course;
		$mform->addElement('header', 'course_format_header', get_string('course_format_header', 'local_metadata'));
		// Assessment
		// TODO: MANIPULATE ASSESSMENT FIELD AS SPECIFIED
		$course_assessment = $mform->addElement('text', 'course_assessment', get_string('assessment_counter', 'local_metadata'), '');
		$mform->addRule('course_assessment', get_string('required'), 'required', null, 'client');
		$mform->addRule('course_assessment', get_string('err_numeric', 'local_metadata'), 'numeric', null, 'client');

		if($courseinfo){
			$mform->setDefault('course_assessment', $courseinfo->assessmentnumber);
		}
		$mform->setType('course_assessment', PARAM_INT);

		// Session
		// TODO: MANIPULATE SESSION FIELD AS SPEFICIED
		$course_assessment = $mform->addElement('text', 'course_session', get_string('session_counter', 'local_metadata'), '');
		$mform->addRule('course_session', get_string('required'), 'required', null, 'client');
		$mform->addRule('course_session', get_string('err_numeric', 'local_metadata'), 'numeric', null, 'client');

		if($courseinfo){
			$mform->setDefault('course_session', $courseinfo->sessionnumber);
		}
		$mform->setType('course_session', PARAM_INT);
		
		$mform->closeHeaderBefore('course_obj_header');

		$mform->setExpanded('course_format_header');
	}
	
	/**
	 * Add form elements for upload course objective.
	 */
	private function setup_upload_course_obj($mform){
		$mform->addElement('header', 'course_obj_header', get_string('course_obj_header', 'local_metadata'));
		$mform->addHelpButton('course_obj_header', 'course_obj_header', 'local_metadata');
		$mform->addElement('filepicker', 'temp_course_obj', get_string('file'), null, array('maxbytes' => 0, 'accepted_types' => '.csv'));
		$mform->addElement('submit', 'upload_course_obj', get_string('upload_course_obj', 'local_metadata'));
	}
	
	/**
	 * Get the learning objective id and name.
	 * @param $list		objective type list
	 * @param $id		objective id
	 * @param $name		objective name
	 * return $obj		learning objective object
	 * return $obj		learning objective object
	 */
	function get_learning_obj($id, $name){
		$obj = new stdClass();
		$obj->id = $id;
		$obj->name = $name;
		return $obj;
	} 
	
	/**
	 * Add form elements for course objective.
	 */
	private function setup_course_obj($mform, $type, $list, $nextheader){
		global $CFG, $DB, $USER; //Declare our globals for use
		global $course;
		
		$mform->addElement('header', 'obj_'.$type.'_header', get_string('obj_'.$type.'_header', 'local_metadata'));
		$mform->addHelpButton('obj_'.$type.'_header', 'obj_'.$type.'_header', 'local_metadata');

		$_desc = $mform->addElement('static', $type.'_desc', '', get_string($type.'_desc', 'local_metadata'));
		$_array = array();

		$_array[] = $mform->createElement('text', $type.'_option', get_string($type.'_label', 'local_metadata'));
		$_array[] = $mform->createElement('hidden', $type.'_id', -1);

		$_options = array();       
		$mform->setType($type.'_option', PARAM_TEXT);
		$mform->setType($type.'_id', PARAM_INT);
		$this->repeat_elements($_array, count($list), $_options, 'option_repeats_'.$type, 'option_add_fields_'.$type, 1, get_string('add_'.$type, 'local_metadata'), true);

		$key = 0;
		foreach ($list as $_item) {
			$index = '['.$key.']';
			$mform->setDefault($type.'_option'.$index, $_item->name);
			$mform->setDefault($type.'_id'.$index, $_item->id);
			$key += 1;
		}

		$mform->closeHeaderBefore('obj_'.$nextheader.'_header');

		// If list is not empty, open the header
		if(count($list) > 0){
			$mform->setExpanded('obj_'.$type.'_header');
		}
	}
	
	/**
	 * Insert a record to learning and course objective table.
	 * @param $name		learning objective name
	 * @param $type		learning objective type
	 *
	 */
	function insert_course_objective($name, $type){
		global $DB, $CFG, $USER; //Declare them if you need them
		global $course, $courseId;  
		$info = new stdClass();
		$info->objectivename = $name;
		$info->objectivetype = $type;
		$insert_learningobj = $DB->insert_record('learningobjectives', $info, true, false);

		$cobj = new stdClass();
		$cobj->objectiveid = $insert_learningobj;
		$cobj->courseid = $course->id;
		$insert_courseobj = $DB->insert_record('courseobjectives', $cobj, true, false);
	}
	
	/**
	 * Validate the form and handle uploading course objective.
	 */
	function validation($data, $files) {
		$errors = parent::validation($data, $files);
		global $DB, $CFG, $USER; //Declare them if you need them
		global $course, $courseId;  
		
		if(!empty($data['upload_course_obj'])){
	
			$files = $this->get_draft_files('temp_course_obj');
			if(!empty($files)){
				$file = reset($files); 
				$content = $file->get_content();
				
				$parsed = str_getcsv($content, "\n");
				foreach($parsed as $row){
					$type = strrchr($row, ",")[1];
					$name = substr($row, 0, strrpos($row, ","));
					if($type === 'K'){
						$this->insert_course_objective($name, 'Knowledge');
					} else if ($type === 'S'){
						$this->insert_course_objective($name, 'Skills');
					} else {
						$this->insert_course_objective($name, 'Attitudes');
					}
				}
			}
		}
		
		return $errors;
    }
	
	/**
	 * Save the given data to database.
	 * @param $data data generated by the form
	 */
	public function save_data($data) {
		global $CFG, $DB, $USER; //Declare our globals for use
		global $course, $courseId;
	
		$course_info = new stdClass();
		$course_info->courseid = $courseId;
		$course_info->coursename = $course->fullname;
		$course_info->coursedescription = $data->course_description['text'];
		$course_info->coursefaculty = $data->course_faculty;
		//$course_info->coursedescription = $data->course_description;
		$course_info->assessmentnumber = $data->course_assessment;
		$course_info->sessionnumber = $data->course_session;

		$instructor_info = new stdClass();
		$instructor_info->name = $data->course_instructor;
		$instructor_info->officelocation = $data->course_office;
		$instructor_info->officehours = $data->course_officeh;
		$instructor_info->email = $data->course_email;
		if($data->course_phone != NULL){
			$instructor_info->phonenumber = $data->course_phone;
		}
		$instructor_info->userid = $USER->id;

		// learningobjectives
		// objectivename = user input
		// type = knowledge, skill, attitude
		//
		// courseobjectives
		// objectiveid = learningobjectives->id
		// courseid = courseinfo->id

		if($existCourseInfo = $DB->get_record('courseinfo', array('courseid'=>$course->id))){
		// Must have an entry for 'id' to map the table specified.
			$course_info->id = $existCourseInfo->id;
			$update_courseinfo = $DB->update_record('courseinfo', $course_info, false);

			// Handle instructor/contact information
			if($existInstructorInfo = $DB->get_record('courseinstructors', array('courseid'=>$existCourseInfo->id, 'userid'=>$USER->id))){
				$instructor_info->id = $existInstructorInfo->id;
				$update_instructorinfo = $DB->update_record('courseinstructors', $instructor_info, false);
			}else{
				$instructor_info->courseid = $existCourseInfo->id;
				$insert_instructorinfo = $DB->insert_record('courseinstructors', $instructor_info, false);
			}

			// Handle course objectives

			if(!empty($data->knowledge_option)){
				$k_name = $data->knowledge_option;
				$k_id = $data->knowledge_id;
				for($i = 0; $i < count($k_id); $i++){
					// if name is empty and id is exist -> delete record
					if($k_name[$i] === ''){
						if($learnObjExist = $DB->record_exists('learningobjectives', array('id'=>$k_id[$i]))){
							$delete_courseObj = $DB->delete_records('courseobjectives', array('objectiveid'=>$k_id[$i]));
							$delete_learnObj = $DB->delete_records('learningobjectives', array('id'=>$k_id[$i]));
						}
					}else{
					// if name is not empty and id is exist -> update, otherwise -> insert
						if($learnObjExist = $DB->record_exists('learningobjectives', array('id'=>$k_id[$i]))){
							$k = new stdClass();
							$k->id = $k_id[$i];
							$k->objectivename = $k_name[$i];
							$update_courseObj = $DB->update_record('learningobjectives', $k, false);
						}else{
							$this->insert_course_objective($k_name[$i], 'Knowledge');
						}
					}
				}
			}
			
			if(!empty($data->skill_option)){
				$s_name = $data->skill_option;
				$s_id = $data->skill_id;
				for($i = 0; $i < count($s_id); $i++){
					// if name is empty and id is exist -> delete record
					if($s_name[$i] === ''){
						if($learnObjExist = $DB->record_exists('learningobjectives', array('id'=>$s_id[$i]))){
							$delete_courseObj = $DB->delete_records('courseobjectives', array('objectiveid'=>$s_id[$i]));
							$delete_learnObj = $DB->delete_records('learningobjectives', array('id'=>$s_id[$i]));
						}
					}else{
					// if name is not empty and id is exist -> update, otherwise -> insert
						if($learnObjExist = $DB->record_exists('learningobjectives', array('id'=>$s_id[$i]))){
							$s = new stdClass();
							$s->id = $s_id[$i];
							$s->objectivename = $s_name[$i];
							$update_courseObj = $DB->update_record('learningobjectives', $s, false);
						}else{
							$skill_info = new stdClass();
							$skill_info->objectivename = $s_name[$i];
							$skill_info->objectivetype = 'Skill';
							$insert_learningobj = $DB->insert_record('learningobjectives', $skill_info, true, false);
							$scobj = new stdClass();
							$scobj->objectiveid = $insert_learningobj;
							$scobj->courseid = $course->id;
							$insert_courseobj = $DB->insert_record('courseobjectives', $scobj, true, false);
						}
					}
				}
			}
			
			if(!empty($data->attitude_option)){
				$a_name = $data->attitude_option;
				$a_id = $data->attitude_id;
				for($i = 0; $i < count($a_id); $i++){
					// if name is empty and id is exist -> delete record
					if($a_name[$i] === ''){
						if($learnObjExist = $DB->record_exists('learningobjectives', array('id'=>$a_id[$i]))){
							$delete_courseObj = $DB->delete_records('courseobjectives', array('objectiveid'=>$a_id[$i]));
							$delete_learnObj = $DB->delete_records('learningobjectives', array('id'=>$a_id[$i]));
						}
					}else{
					// if name is not empty and id is exist -> update, otherwise -> insert
						if($learnObjExist = $DB->record_exists('learningobjectives', array('id'=>$a_id[$i]))){
							$a = new stdClass();
							$a->id = $a_id[$i];
							$a->objectivename = $a_name[$i];
							$update_courseObj = $DB->update_record('learningobjectives', $a, false);
						}else{
							$attitude_info = new stdClass();
							$attitude_info->objectivename = $a_name[$i];
							$attitude_info->objectivetype = 'Attitude';
							$insert_learningobj = $DB->insert_record('learningobjectives', $attitude_info, true, false);
							$acobj = new stdClass();
							$acobj->objectiveid = $insert_learningobj;
							$acobj->courseid = $course->id;
							$insert_courseobj = $DB->insert_record('courseobjectives', $acobj, true, false);
						}
					}
				}	
			}

		}else{
			$insert_courseinfo = $DB->insert_record('courseinfo', $course_info, true, false);

			// Handle instructor/contact information
			// courseinfo->id => courseinstructor->courseid
			$instructor_info->courseid = $insert_courseinfo;
			$insert_instructorinfo = $DB->insert_record('courseinstructors', $instructor_info, false);
			
			// Handle course objectives
			// knowledge
			if(!empty($data->knowledge_option)){
				foreach($data->knowledge_option as $knowledge_temp){
					if($knowledge_temp != NULL){
						$this->insert_course_objective($knowledge_temp, 'Knowledge');
					}
				}
			}

			// skill
			if(!empty($data->skill_option)){
				foreach($data->skill_option as $skill_temp){
					if($skill_temp != NULL){
						$this->insert_course_objective($skill_temp, 'Skills');
					}
				}
			}

			// attitude
			if(!empty($data->attitude_option)){
				foreach($data->attitude_option as $attitude_temp){
					if($attitude_temp != NULL){ 
						$this->insert_course_objective($attitude_temp, 'Attitudes');
					}  
				}
			}

		}

	}

}

?>
