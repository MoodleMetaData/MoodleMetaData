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
			$coursegradattributes = $DB->get_records('coursegradattributes', array('courseinfoid'=>$courseinfo->id));
		} else {
			$contactinfo = NULL;
			$coursegradattributes = NULL;
		}
		$coursecategories = $DB->get_records('course_categories');
		$coursereadings = get_course_readings();
		$graduateattributes = $DB->get_records('graduateattributes');
		
		
		$reading_list = array();
		foreach($coursereadings as $reading){
			$obj = new stdClass();
			$obj->id = $reading->id;
			$obj->name = $reading->readingname;
			$obj->url = $reading->readingurl;
			$reading_list[] = $obj;
		}
		
		$gradatt_list = array();
        if (!is_null($coursegradattributes)) {
            foreach($coursegradattributes as $gradatt){
                $obj = new stdClass();
                $obj->id = $gradatt->id;
                $gradatts = $DB->get_record('graduateattributes', array('id'=>$gradatt->gradattid));
                $obj->gradattid = $gradatts->id;
                $gradatt_list[] = $obj;
            }
        }
		
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
		
		// setup form elements
		$this->setup_general($mform, $courseinfo, $contactinfo, $coursecategories);
		$this->setup_contact($mform, $courseinfo, $contactinfo);
		$this->setup_description($mform, $courseinfo);
		$this->setup_upload_req_reading($mform);	
		$this->setup_req_reading($mform, $reading_list);	
		$this->setup_format($mform, $courseinfo);
		$this->setup_upload_course_obj($mform);	
		$this->setup_course_obj($mform, 'knowledge', $knowledge_list, 'skill');
		$this->setup_course_obj($mform, 'skill', $skill_list, 'attitude');
		$this->setup_course_obj($mform, 'attitude', $attitude_list, 'gradatt');
		$this->setup_graduate_attributes($mform, $graduateattributes, $gradatt_list);
		$this->setup_teaching_assumption($mform, $courseinfo);
 
		// Add form buttons
		$this->add_action_buttons(true, "Save general information");
	}
	
	/**
	 * Add form elements for general course information.
	 * @param $mform		form definition
	 * @param $courseinfo	a record of general information from course info table.
	 * @param $contactinfo	a record of contact information from course instructor table.
	 * @return void
	 */
	private function setup_general($mform, $courseinfo, $contactinfo, $coursecategories){
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
		$mform->addRule('course_instructor', get_string('err_required', 'local_metadata'), 'required', null, 'client');
		$mform->setType('course_instructor', PARAM_TEXT);
		
		// Faculty
		$course_faculty = $mform->addElement('text', 'course_faculty', get_string('course_faculty', 'local_metadata'), '');
		if($courseinfo){
			$mform->setDefault('course_faculty', $courseinfo->coursefaculty);
		}   
		$mform->addRule('course_faculty', get_string('err_required', 'local_metadata'), 'required', null, 'client');
		$mform->setType('course_faculty', PARAM_TEXT);

		/*
		// Program types
		// TODO: FETCH DATA FROM DBTO MANIPULATE THE LIST
		$program_types = array();
		$program_types[] = 'program type 1';
		$program_types[] = 'program type 2';
		// -------------------------------------
		$program_type_selection = $mform->addElement('select', 'program_type', get_string('program_type', 'local_metadata'), $program_types, '');
		$mform->addRule('program_type', get_string('err_required', 'local_metadata'), 'required', null, 'client');
		*/

		// Courses category
		$category_list = array();
		foreach($coursecategories as $coursecategory){
			$category_list[$coursecategory->id] = $coursecategory->name;
		}
		
		$course_category_selection = $mform->addElement('select', 'course_category', get_string('course_category', 'local_metadata'), $category_list, '');
		$mform->addRule('course_category', get_string('err_required', 'local_metadata'), 'required', null, 'client');
		if($courseinfo){
			$course_category_selection->setSelected($courseinfo->categoryid);
		}
		
		
		$mform->closeHeaderBefore('course_contact_header');  

		$mform->setExpanded('course_general_header');
	}
	
	/**
	 * Add form elements for course contact information.
	 * @param $mform		form definition
	 * @param $courseinfo	a record of general information from course info table.
	 * @param $contactinfo	a record of contact information from course instructor table.
	 * @return void
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
		$mform->addRule('course_email', get_string('err_required', 'local_metadata'), 'required', null, 'client');
		$mform->addRule('course_email', get_string('err_email', 'local_metadata'), 'email', null, 'client');
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
		$mform->addRule('course_office', get_string('err_required', 'local_metadata'), 'required', null, 'client');  
		$mform->setType('course_office', PARAM_TEXT);

		// Office hours
		$course_officeh = $mform->addElement('text', 'course_officeh', get_string('course_officeh', 'local_metadata'), '');
		if($contactinfo){
			$mform->setDefault('course_officeh', $contactinfo->officehours);
		}
		$mform->setType('course_officeh', PARAM_TEXT);

		$mform->addRule('course_officeh', get_string('err_required', 'local_metadata'), 'required', null, 'client');

		$mform->closeHeaderBefore('course_desc_header');

		$mform->setExpanded('course_contact_header');
	}

	/**
	 * Add form elements for course description.
	 * @param $mform		form definition
	 * @param $courseinfo	a record of general information from course info table.
	 * @return void
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
		if($courseinfo){
			$current_description = $courseinfo->coursedescription;
			//$mform->setDefault('course_description', $current_description);
			$course_description_editor->setValue(array('text' => $current_description) );
		}else{
			//$mform->setDefault('course_description', $default_description);
			$course_description_editor->setValue(array('text' => $default_description) );
		}
		$mform->addRule('course_description', get_string('err_required', 'local_metadata'), 'required', null, 'client');
		$mform->setType('course_description', PARAM_TEXT);      



		$mform->closeHeaderBefore('course_format_header');

		$mform->setExpanded('course_desc_header');
	}
	
	/**
	 * Add form elements for upload requred readinds.
	 * @param $mform		form definition.
	 * @return void
	 */
	private function setup_upload_req_reading($mform){
		$mform->addElement('header', 'upload_reading_header', get_string('upload_reading_header', 'local_metadata'));
		$mform->addHelpButton('upload_reading_header', 'upload_reading_header', 'local_metadata');
		$mform->addElement('filepicker', 'temp_reading', get_string('file'), null, array('maxbytes' => 0, 'accepted_types' => '.csv'));
		$mform->addElement('submit', 'upload_reading', get_string('upload_reading', 'local_metadata'));
	}
	
	/**
	 * Add form elements for required readings.
	 * @param $mform	form definition
	 * @param $list		a list of readings
	 * @return void
	 */
	private function setup_req_reading($mform, $list){
		global $CFG, $DB, $USER; //Declare our globals for use
        global $course;
		
		$mform->addElement('header', 'course_reading_header', get_string('course_reading_header', 'local_metadata'));
		$mform->addHelpButton('course_reading_header', 'course_reading_header', 'local_metadata');

		$_array = array();
		$_array[] = $mform->createElement('static', 'course_reading_desc', '', get_string('course_reading_desc', 'local_metadata'));
		$_array[] = $mform->createElement('text', 'readingname_option', get_string('readingname_label', 'local_metadata') ,'size="60"');
		$_array[] = $mform->createElement('text', 'readingurl_option', get_string('readingurl_label', 'local_metadata') ,'size="60"');
		$_array[] = $mform->createElement('submit', 'delete_req_reading', get_string('delete_reading_label', 'local_metadata'));
		$_array[] = $mform->createElement('hidden', 'reading_id', -1);

		$_options = array();       
		$mform->setType('readingname_option', PARAM_TEXT);
		$mform->setType('readingurl_option', PARAM_TEXT);
		$mform->setType('reading_id', PARAM_INT);
		$this->repeat_elements($_array, count($list), $_options, 'option_repeats_reading', 'option_add_fields_reading', 1, get_string('add_reading', 'local_metadata'), true);

		$key = 0;
		foreach ($list as $_item) {
			$index = '['.$key.']';
			$mform->setDefault('readingname_option'.$index, $_item->name);
			if($_item->url !== '0'){
				$mform->setDefault('readingurl_option'.$index, $_item->url);
			}
			$mform->setDefault('reading_id'.$index, $_item->id);
			$key += 1;
		}
		
		$mform->closeHeaderBefore('course_format_header');

		// If list is not empty, open the header
		if(count($list) > 0){
			$mform->setExpanded('course_reading_header');
		}
		
	}
	
	/**
	 * Add form elements for course format.
	 * @param $mform		form definition
	 * @param $courseinfo	a record of general information from course info table.
	 * @return void
	 */
	private function setup_format($mform, $courseinfo){
		global $CFG, $DB, $USER; //Declare our globals for use
        global $courseId;
		$mform->addElement('header', 'course_format_header', get_string('course_format_header', 'local_metadata'));
		$mform->addHelpButton('course_format_header', 'course_format_header', 'local_metadata');
		
		// Assessment
		$course_assessment = $mform->addElement('text', 'course_assessment', get_string('assessment_counter', 'local_metadata'), '');
		$mform->addRule('course_assessment', get_string('err_required', 'local_metadata'), 'required', null, 'client');
		$mform->addRule('course_assessment', get_string('err_numeric', 'local_metadata'), 'numeric', null, 'client');

		$courseassessment = $DB->count_records('courseassessment', array('courseid'=>$courseId));
		if($courseassessment != 0){
			$mform->setDefault('course_assessment', $courseassessment);
		} else {
			if($courseinfo){
				$mform->setDefault('course_assessment', $courseinfo->assessmentnumber);
			}
		}

		$mform->setType('course_assessment', PARAM_INT);

		// Session
		$course_assessment = $mform->addElement('text', 'course_session', get_string('session_counter', 'local_metadata'), '');
		$mform->addRule('course_session', get_string('err_required', 'local_metadata'), 'required', null, 'client');
		$mform->addRule('course_session', get_string('err_numeric', 'local_metadata'), 'numeric', null, 'client');

		$coursesession = $DB->count_records('coursesession', array('courseid'=>$courseId));
		if($coursesession != 0){
			$mform->setDefault('course_session', $coursesession);
		} else {
			if($courseinfo){
				$mform->setDefault('course_session', $courseinfo->sessionnumber);
			}
		}

		$mform->setType('course_session', PARAM_INT);
		
		$mform->closeHeaderBefore('course_obj_header');

		$mform->setExpanded('course_format_header');
	}
	
	/**
	 * Add form elements for upload course objective.
	 * @param $mform		form definition
	 * @return void
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
	 */
	private function get_learning_obj($id, $name){
		$obj = new stdClass();
		$obj->id = $id;
		$obj->name = $name;
		return $obj;
	} 
	
	/**
	 * Add form elements for course objective.
	 * @param $mform		form definition
	 * @param $type			course objective type
	 * @param $list			a list of learning objective with specified type
	 * @param $nextheader	the next form header name
	 * @return void
	 */
	private function setup_course_obj($mform, $type, $list, $nextheader){
		global $CFG, $DB, $USER; //Declare our globals for use
		global $course;
		
		$mform->addElement('header', 'obj_'.$type.'_header', get_string('obj_'.$type.'_header', 'local_metadata'));
		$mform->addHelpButton('obj_'.$type.'_header', 'obj_'.$type.'_header', 'local_metadata');

		$_desc = $mform->addElement('static', $type.'_desc', '', get_string($type.'_desc', 'local_metadata'));
		$_array = array();

		$_array[] = $mform->createElement('text', $type.'_option', get_string($type.'_label', 'local_metadata') ,'size="60"');
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
	 * Add form elements for graduate attributes.
	 */
	private function setup_graduate_attributes($mform, $graduateattributes, $list){
		global $CFG, $DB, $USER; //Declare our globals for use
		global $course;
		
		$mform->addElement('header', 'course_gradatt_header', get_string('course_gradatt_header', 'local_metadata'));

		$gradAtt_array = array();

		$course_gradAtts = array();
		foreach($graduateattributes as $graduateattribute){
			$course_gradAtts[$graduateattribute->id] = $graduateattribute->attribute;
		}		

		$gradAtt_array[] = $mform->createElement('select', 'gradAtt_option', get_string('course_gradAtt', 'local_metadata'), $course_gradAtts, '');
		$gradAtt_array[] = $mform->createElement('hidden', 'gradAtt_id', -1);

		$gradAtt_options = array();
		$mform->setType('gradAtt_option', PARAM_CLEANHTML);
		$mform->setType('gradAtt_id', PARAM_INT);
		$this->repeat_elements($gradAtt_array, count($list), $gradAtt_options, 'option_repeats_gradAtt', 'option_add_fields_gradAtt', 1, get_string('add_gradAtt', 'local_metadata'), true);

		$key = 0;
		foreach ($list as $_item) {
			$index = '['.$key.']';
			$mform->setDefault('gradAtt_option'.$index, $_item->gradattid);
			$mform->setDefault('gradAtt_id'.$index, $_item->id);
			$key += 1;
		}
		
		$mform->closeHeaderBefore('teaching_assumption_header');

		// If list is not empty, open the header
		if(count($list) > 0){
			$mform->setExpanded('course_gradatt_header');
		}
		
		
	}
	
	/**
	 * Add form elements for teaching assumption.
	 * @param $mform		form definition
	 * @param $courseinfo	a record of general information from course info table.
	 * @return void
	 */
	private function setup_teaching_assumption($mform, $courseinfo){
		global $CFG, $DB, $USER; //Declare our globals for use
		global $course;
		
		$mform->addElement('header', 'teaching_assumption_header', get_string('teaching_assumption_header', 'local_metadata'));
		$teaching_assumption_editor = $mform->addElement('editor', 'teaching_assumption', get_string('teaching_assumption', 'local_metadata'));
		if(!empty($courseinfo->teachingassumption)){
			$teaching_assumption_editor->setValue(array('text' => $courseinfo->teachingassumption));
			$mform->setExpanded('teaching_assumption_header');
		}
		$mform->setType('teaching_assumption', PARAM_TEXT);     
	}
	
	/**
	 * Insert a record to learning and course objective table.
	 * @param $name		learning objective name
	 * @param $type		learning objective type
	 * @patam void
	 */
	private function insert_course_objective($name, $type){
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
	 * Upload course objectives.
	 * @param $mform	form definition
	 * @return void
	 */
	private function upload_course_obj($mform){
		global $DB, $CFG, $USER; //Declare them if you need them
		global $course, $courseId;  
		$obj_was_uploaded = $mform->getSubmitValue('upload_course_obj');
		if($obj_was_uploaded){
	
			$files = $this->get_draft_files('temp_course_obj');
			if(!empty($files)){
				$file = reset($files); 
				$content = $file->get_content();
				$all_rows = explode("\n", $content);
				
				foreach($all_rows as $row){
					$parsed = str_getcsv($row);
					
					if(!is_null($parsed[0])){
						if($parsed[0] != ''){
							$this->insert_course_objective($parsed[0], 'Knowledge');
						}
						if($parsed[1] != ''){
							$this->insert_course_objective($parsed[1], 'Skill');
						}
						if($parsed[2] != ''){
							$this->insert_course_objective($parsed[2], 'Attitude');
						}
					}
					
				}
			}
		}
	}
	
	/**
	 * Upload course required readings.
	 * @param $mform	form definition
	 * @return void
	 */
	private function upload_req_reading($mform){
		global $DB, $CFG, $USER; //Declare them if you need them
		global $course, $courseId;
		$reading_was_uploaded = $mform->getSubmitValue('upload_reading');
		if($reading_was_uploaded){

			$files = $this->get_draft_files('temp_reading');
			if(!empty($files)){
				$file = reset($files); 
				$content = $file->get_content();

				
				$parsed = str_getcsv($content, "\n");
				foreach($parsed as $row){
					$url = substr($row, strrpos($row, ",")+1);
					$name = substr($row, 0, strrpos($row, ","));
					$this->insert_readings($name, $url);
				}
			}
		}
	}
	
	/**
	 * Insert a record to course reading table.
	 * @param $name	reading's name/title
	 * @param $url	reading's url
	 * @return void
	 */
	private function insert_readings($name, $url){
		global $DB, $CFG, $USER; //Declare them if you need them
		global $course, $courseId;

		$_reading = new stdClass();
		$_reading->readingname = $name;
		$_reading->readingurl = $url;
		$_reading->courseid = $courseId;
		$insert_reading = $DB->insert_record('coursereadings', $_reading, true, false);
	}
	
	/**
	 * Delete course reading when user clicks on "Delete" button in required readings.
	 * @param $mform	form definition
	 * @return void
	 */
	private function delete_reading($mform){
		global $DB, $CFG, $USER; //Declare them if you need them
		global $course, $courseId;
		
		if($reading_was_deleted = $mform->getSubmitValue('delete_req_reading')){
			foreach($reading_was_deleted as $key=>$value) {
				$index = '['.$key.']';
				$r_id = $mform->getSubmitValue('reading_id'.$index);
				$delete_reading = $DB->delete_records('coursereadings', array('id'=>$r_id));
			}
		}
		
	}
	
	/**
	 * Insert a record to course format (session or assessment)
	 * @param $course_format	either 'session' or 'assessment'
	 * @return void
	 */
	private function insert_number_of_course_format($course_format){
		global $DB, $CFG, $USER; //Declare them if you need them
		global $courseId;
		$format = new stdClass();
		$format->courseid = $courseId;
		$insert_format = $DB->insert_record($course_format, $format, true, false);
	}
	
	/**
	 * Edit the number of course session or assessment.
	 * @param $course_format				either 'session' or 'assessment'
	 * @param $new_course_format_number		a new number of session or assessment
	 * @return void
	 */
	private function edit_course_format($course_format, $new_course_format_number){
		global $DB, $CFG, $USER; //Declare them if you need them
		global $course, $courseId;
		
		$current_course_format = $DB->count_records($course_format, array('courseid'=>$courseId));
		if($new_course_format_number > $current_course_format){
			// insert new record
			$new_add = $new_course_format_number - $current_course_format;
			for($i = 0; $i < $new_add; $i++){
				$this->insert_number_of_course_format($course_format);
			}			
		} else {
			// delete the latest record
			$number_deleted = $current_course_format - $new_course_format_number;
			for($i = 0; $i < $number_deleted; $i++){
				$exist_sessions = get_table_data_for_course($course_format);
				$delete_session = $DB->delete_records($course_format, array('id'=>(end($exist_sessions)->id)));		
			}	
		}
	}
	
	/**
	 * This function is used for uploading course objective and required readings.
	 * and delete required reading.
	 * @return void
	 */
	function definition_after_data() {
        parent::definition_after_data();
        $mform = $this->_form;
		
		$this->delete_reading($mform);
		$this->upload_req_reading($mform);
		$this->upload_course_obj($mform);
	}
	
	
	/**
	 * Validate the form.
	 */
	function validation($data, $files) {
		$errors = parent::validation($data, $files);
		if($data['course_session'] < 0){
			$errors['course_session'] = get_string('err_positivenumber', 'local_metadata');
		}
		if($data['course_assessment'] < 0){
			$errors['course_assessment'] = get_string('err_positivenumber', 'local_metadata');
		}
		return $errors;
    }
	
	/**
	 * Save the given data to database.
	 * @param $data 	data generated by the form
	 * @return void
	 */
	public function save_data($data) {
		global $CFG, $DB, $USER; //Declare our globals for use
		global $course, $courseId;
		
		$course_info = new stdClass();
		$course_info->courseid = $courseId;
		$course_info->coursename = $course->fullname;
		$course_info->coursedescription = $data->course_description['text'];
		$course_info->coursefaculty = $data->course_faculty;
		$course_info->categoryid = $data->course_category;
		$course_info->assessmentnumber = $data->course_assessment;
		$course_info->sessionnumber = $data->course_session;
		if($data->teaching_assumption != NULL){
			$course_info->teachingassumption = $data->teaching_assumption['text'];
		}

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

		if($existCourseInfo = $DB->get_record('courseinfo', array('courseid'=>$courseId))){
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

			// Handle session and assessment
			$this->edit_course_format('coursesession', $data->course_session);
			$this->edit_course_format('courseassessment', $data->course_assessment);
			
			// Handle course readings
			if(!empty($data->readingname_option)){
				$r_name = $data->readingname_option;
				$r_url = $data->readingurl_option;
				$r_id = $data->reading_id;
				for($i = 0; $i < count($r_name); $i++){
					if($r_name[$i] === ''){
						$delete_reading = $DB->delete_records('coursereadings', array('id'=>$r_id[$i]));
					} else {
						if($readingExist = $DB->record_exists('coursereadings', array('id'=>$r_id[$i]))){
							$r = new stdClass();
							$r->id = $r_id[$i];
							$r->readingname = $r_name[$i];
							$r->readingurl = $r_url[$i];
							$update_reading = $DB->update_record('coursereadings', $r, false);
						}else{
							$this->insert_readings($r_name[$i], $r_url[$i]);
						}
					}
				}
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
							$this->insert_course_objective($s_name[$i], 'Skill');
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
							$this->insert_course_objective($a_name[$i], 'Atittude');
						}
					}
				}	
			}

			if(!empty($data->gradAtt_option)){
				$grad_gradattid = $data->gradAtt_option;
				$grad_id = $data->gradAtt_id;
				for($i = 0; $i < count($grad_id); $i++){
					if($gradattExist = $DB->record_exists('coursegradattributes', array('id'=>$grad_id[$i]))){
						$g = new stdClass();
						$g->id = $grad_id[$i];
						$g->gradattid = $grad_gradattid[$i];
						$update_courseObj = $DB->update_record('coursegradattributes', $g, false);
					} else {
						$grad_att = new stdClass();
						$grad_att->courseinfoid = $existCourseInfo->id;
						$grad_att->gradattid = $grad_gradattid[$i];
						$insert_coursegradatt = $DB->insert_record('coursegradattributes', $grad_att, true, false);
					}
				}
			}
			
		}else{
			$insert_courseinfo = $DB->insert_record('courseinfo', $course_info, true, false);

			// Handle instructor/contact information
			// courseinfo->id => courseinstructor->courseid
			$instructor_info->courseid = $insert_courseinfo;
			$insert_instructorinfo = $DB->insert_record('courseinstructors', $instructor_info, false);

			// Handle session and assessment form
			for($i = 0; $i < $data->course_session; $i++){
				$this->insert_number_of_course_format('coursesession');
			}
			
			for($i = 0; $i < $data->course_assessment; $i++){
				$this->insert_number_of_course_format('courseassessment');
			}
			
			// Handle course reading
			if(!empty($data->readingname_option)){
				$names = $data->readingname_option;
				$urls = $data->readingurl_option;
				for($i = 0; $i < count($names); $i++){
					if($names[$i] != NULL){
						$this->insert_readings($names[$i], $urls[$i]);
					}
				}
			}
			
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
						$this->insert_course_objective($skill_temp, 'Skill');
					}
				}
			}

			// attitude
			if(!empty($data->attitude_option)){
				foreach($data->attitude_option as $attitude_temp){
					if($attitude_temp != NULL){ 
						$this->insert_course_objective($attitude_temp, 'Attitude');
					}  
				}
			}
			
			// Handle graduate attributes
			if(!empty($data->gradAtt_option)){
				foreach($data->gradAtt_option as $gradAtt){
					$grad_att = new stdClass();
					$grad_att->courseinfoid = $insert_courseinfo;
					$grad_att->gradattid = $gradAtt;
					$insert_coursegradatt = $DB->insert_record('coursegradattributes', $grad_att, true, false);

				}
			}

		}

	}

}

?>