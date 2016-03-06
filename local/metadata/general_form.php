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
                global $course;           

                // initialize the form.
                $mform = $this->_form; //Tell this object to initialize with the properties of the Moodle form.

		/***************************
		* GENERAL
		***************************/
	        $mform->addElement('header', 'course_general_header', get_string('course_general_header', 'local_metadata'));
				
		// Course shortname
                $courseCode = $course->shortname;
                $mform->addElement('static', 'course_code', get_string('course_code', 'local_metadata'));
                $mform->setDefault('course_code', $courseCode);
            
                $courseName = $course->fullname;
                $mform->addElement('static', 'course_name', get_string('course_name', 'local_metadata'));
                $mform->setDefault('course_name', $courseName);
				
		// Instructor
                $courseInstructor = $USER->firstname.' '.$USER->lastname;
                $mform->addElement('static', 'course_instructor', get_string('course_instructor', 'local_metadata'));
                $mform->setDefault('course_instructor', $courseInstructor);
				
		// Faculty
                $course_faculty = $mform->addElement('text', 'course_faculty', get_string('course_faculty', 'local_metadata'), '');
                //$mform->addRule('course_faculty', get_string('required'), 'required', null, 'client');
                if($courseinfo = $DB->get_record('courseinfo', array('courseid'=>$course->id))){
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

		/***************************
		* CONTACT 
		***************************/
                $mform->addElement('header', 'course_contact_header', get_string('course_contact_header', 'local_metadata'));

                // Email
                $course_email = $mform->addElement('text', 'course_email', get_string('course_email', 'local_metadata'), '');
                if($contactinfo = $DB->get_record('courseinstructors', array('courseid'=>$courseinfo->id, 'userid'=>$USER->id))){
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

		/***************************
		* DESCRIPTION
		***************************/
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
               
		/***************************
		* COURSE FORMAT
		 ***************************/;
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
				
		$mform->closeHeaderBefore('obj_knowledge_header');

                $mform->setExpanded('course_format_header');

                /***************************
		* COURSE OBJECTIVES
		***************************/
                
                // fetch from DB if already exist
                // TODO: FETCH COURSE OBJECTIVE DOES NOT WORK!
                $learning_objectives = get_course_learning_objectives();
                $knowledge_list = array();
                $skill_list = array();
                $attitude_list = array();

                foreach($learning_objectives as $obj){
                    if($obj->objectivetype === 'Knowledge'){
                        $k_obj = new stdClass();
                        $k_obj->id = $obj->id;
                        $k_obj->name = $obj->objectivename;
                        $knowledge_list[] = $k_obj;
                    }else if($obj->objectivetype === 'Skills'){
                        $s_obj = new stdClass();
                        $s_obj->id = $obj->id;
                        $s_obj->name = $obj->objectivename;
                        $skill_list[] = $s_obj;
                    }else{
                        $a_obj = new stdClass();
                        $a_obj->id = $obj->id;
                        $a_obj->name = $obj->objectivename;
                        $attitude_list[] = $a_obj;
                    }
                } 

                // Knowledge
                $mform->addElement('header', 'obj_knowledge_header', get_string('obj_knowledge_header', 'local_metadata'));
                $mform->addHelpButton('obj_knowledge_header', 'obj_knowledge_header', 'local_metadata');

                $knowledge_desc = $mform->addElement('static', 'knowledge_desc', '', get_string('knowledge_desc', 'local_metadata'));
                $knowledge_array = array();

                $knowledge_array[] = $mform->createElement('text', 'knowledge_option', get_string('knowledge_label', 'local_metadata'));
                $knowledge_array[] = $mform->createElement('hidden', 'knowledge_id', -1);

                $knowledge_options = array();       
                $mform->setType('knowledge_option', PARAM_TEXT);
                $mform->setType('knowledge_id', PARAM_INT);
                $this->repeat_elements($knowledge_array, count($knowledge_list), $knowledge_options, 'option_repeats1', 'option_add_fields_knowledge', 1, get_string('add_knowledge', 'local_metadata'), true);

                $key = 0;
                foreach ($knowledge_list as $knowledge_item) {
                    //echo $knowledge_item;
                    $index = '['.$key.']';
                    $mform->setDefault('knowledge_option'.$index, $knowledge_item->name);
                    $mform->setDefault('knowledge_id'.$index, $knowledge_item->id);
                    $key += 1;
                }

                $mform->closeHeaderBefore('obj_skill_header');

                // If knowlegde list is not empty, open the header
                if(count($knowledge_list) > 0){
                    $mform->setExpanded('obj_knowledge_header');
                }

                // Skill
                $mform->addElement('header', 'obj_skill_header', get_string('obj_skill_header', 'local_metadata'));
                $mform->addHelpButton('obj_skill_header', 'obj_skill_header', 'local_metadata');

                $skill_desc = $mform->addElement('static', 'skill_desc', '', get_string('skill_desc', 'local_metadata'));
                $skill_array = array();

                $skill_array[] = $mform->createElement('text', 'skill_option', get_string('skill_label', 'local_metadata'));
                $skill_array[] = $mform->createElement('hidden', 'skill_id', -1);
               
                $skill_options = array();       
                $mform->setType('skill_option', PARAM_TEXT);
                $mform->setType('skill_id', PARAM_INT);
                $this->repeat_elements($skill_array, count($skill_list), $skill_options, 'option_repeats2', 'option_add_fields_skill', 1, get_string('add_skill', 'local_metadata'), true);

                $key = 0;
                foreach ($skill_list as $skill_item) {
                    $index = '['.$key.']';
                    $mform->setDefault('skill_option'.$index, $skill_item->name);
                    $mform->setDefault('skill_id'.$index, $skill_item->id);
                    $key += 1;
                }


                $mform->closeHeaderBefore('obj_attitude_header');

                // If skill list is not empty, open the header
                if(count($skill_list) > 0){
                    $mform->setExpanded('obj_skill_header');
                }


                //Attitude
                $mform->addElement('header', 'obj_attitude_header', get_string('obj_attitude_header', 'local_metadata'));
                $mform->addHelpButton('obj_attitude_header', 'obj_attitude_header', 'local_metadata');

                $attitude_desc = $mform->addElement('static', 'attitude_desc', '',  get_string('attitude_desc', 'local_metadata'));
                $attitude_array = array();

                $attitude_array[] = $mform->createElement('text', 'attitude_option', get_string('attitude_label', 'local_metadata'));
                $attitude_array[] = $mform->createElement('hidden', 'attitude_id', 0);
                                                            
                $attitude_options = array();       
                $mform->setType('attitude_option', PARAM_TEXT);
                $mform->setType('attitude_id', PARAM_INT);
                $this->repeat_elements($attitude_array, count($attitude_list), $attitude_options, 'option_repeats3', 'option_add_fields_attitude', 1, get_string('add_attitude','local_metadata'), true);

                $key = 0;
                foreach ($attitude_list as $attitude_item) {
                    $index = '['.$key.']';
                    $mform->setDefault('attitude_option'.$index, $attitude_item->name);
                    $mform->setDefault('attitude_id'.$index, $attitude_item->id);
                    $key += 1;
                }

                $mform->closeHeaderBefore('course_gradatt_header');

                // If attitude list is not empty, open the header
                if(count($attitude_list) > 0){
                    $mform->setExpanded('obj_attitude_header');
                }

                /***************************
		* GRADUATE ATTRIBUTES
		***************************/
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


		// Add form buttons
		$this->add_action_buttons(true, "Save general  information");
	}
	
	//If you need to validate your form information, you can override  the parent's validation method and write your own.	
	function validation($data, $files) {
		$errors = parent::validation($data, $files);
		global $DB, $CFG, $USER; //Declare them if you need them

		//if ($data['data_name'] Some condition here)  {
		//	$errors['element_to_display_error'] = get_string('error', 'local_demo_plug-in');
		//}
		return $errors;
        }

        /**
         * Will save the given data.
         * @param $data data generated by the form
         */
        public static function save_data($data) {
                global $CFG, $DB, $USER; //Declare our globals for use
                global $course;
                
                $course_info = new stdClass();
                $course_info->courseid = $course->id;
                $course_info->coursename = $course->fullname;
                $course_info->coursetopic = $data->course_topic;
                $course_info->coursedescription = $data->course_description['text'];
                $course_info->coursefaculty = $data->course_faculty;
                //$course_info->coursedescription = $data->course_description;
                $course_info->coursefaculty = $data->course_faculty;
                $course_info->assessmentnumber = $data->course_assessment;
                $course_info->sessionnumber = $data->course_session;

                $instructor_info = new stdClass();
                $instructor_info->name = $USER->firstname.' '.$USER->lastname;
                $instructor_info->officelocation = $data->course_office;
                $instructor_info->officehours = $data->course_officeh;
                $instructor_info->email = $data->course_email;
                $instructor_info->phonenumber = $data->course_phone;
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
                    echo 'Existing course information is updated.<br />';

                    // Handle instructor/contact information
                    if($existInstructorInfo = $DB->get_record('courseinstructors', array('courseid'=>$existCourseInfo->id))){
                            
                        $instructor_info->id = $existInstructorInfo->id;
                        $update_instructorinfo = $DB->update_record('courseinstructors', $instructor_info, false);
                        echo 'Existing instructor information is updated.<br />';
                    }else{
                        $insert_instructorinfo = $DB->insert_record('courseinstructors', $instructor_info, false);
                        echo 'New instructor information is added.<br />';
                    }

                    // Handle course objectives

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
                                // TODO: CHECK THIS!
                                $knowledge_info = new stdClass();
                                $knowledge_info->objectivename = $k_name[$i];
                                $knowledge_info->objectivetype = 'Knowledge';
                                $insert_learningobj = $DB->insert_record('learningobjectives', $knowledge_info, true, false);

                                $kcobj = new stdClass();
                                $kcobj->objectiveid = $insert_learningobj;
                                $kcobj->courseid = $course->id;
                                $insert_courseobj = $DB->insert_record('courseobjectives', $kcobj, true, false);
                            }
                        }
                    }
        
                }else{
                    $insert_courseinfo = $DB->insert_record('courseinfo', $course_info, true, false);

                    // Handle instructor/contact information
                    // courseinfo->id => courseinstructor->courseid
                    $instructor_info->courseid = $insert_courseinfo;
                    $insert_instructorinfo = $DB->insert_record('courseinstructors', $instructor_info, false);
                    echo 'New course and instructor information are added.<br />';

                    // Handle course objectives
                    // TODO: dynamic course objectives type
                    // knowledge
                    foreach($data->knowledge_option as $knowledge_temp){
                        if($knowledge_temp != NULL){
                            $knowledge_info = new stdClass();
                            $knowledge_info->objectivename = $knowledge_temp;
                            $knowledge_info->objectivetype = 'Knowledge';
                            $insert_learningobj = $DB->insert_record('learningobjectives', $knowledge_info, true, false);

                            $kcobj = new stdClass();
                            $kcobj->objectiveid = $insert_learningobj;
                            $kcobj->courseid = $course->id;
                            $insert_courseobj = $DB->insert_record('courseobjectives', $kcobj, true, false);
                        }
                    }

                    // skill
                    foreach($data->skill_option as $skill_temp){
                        if($skill_temp != NULL){
                            $skill_info = new stdClass();
                            $skill_info->objectivename = $skill_temp;
                            $skill_info->objectivetype = 'Skills';
                            $insert_learningobj = $DB->insert_record('learningobjectives', $skill_info, true, false);

                            $scobj = new stdClass();
                            $scobj->objectiveid = $insert_learningobj;
                            $scobj->courseid = $course->id;
                            $insert_courseobj = $DB->insert_record('courseobjectives', $scobj, true, false);
                        }
                    }

                    // attitude
                    foreach($data->attitude_option as $attitude_temp){
                        if($attitude_temp != NULL){ 
                            $attitude_info = new stdClass();      
                            $attitude_info->objectivename = $attitude_temp;
                            $attitude_info->objectivetype = 'Attitudes';
                            $insert_learningobj = $DB->insert_record('learningobjectives', $attitude_info, true, false);

                            $acobj = new stdClass();
                            $acobj->objectiveid = $insert_learningobj;
                            $acobj->courseid = $course->id;
                            $insert_courseobj = $DB->insert_record('courseobjectives', $acobj, true, false);
                        }  
                    }

                }

        }

}

?>
