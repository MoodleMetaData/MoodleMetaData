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

                $courseCode = $course->shortname;
                $mform->addElement('static', 'course_code', get_string('course_code', 'local_metadata'));
                $mform->setDefault('course_code', $courseCode);
            
                $courseName = $course->fullname;
                $mform->addElement('static', 'course_name', get_string('course_name', 'local_metadata'));
                $mform->setDefault('course_name', $courseName);

                $courseInstructor = $USER->firstname.' '.$USER->lastname;
                $mform->addElement('static', 'course_instructor', get_string('course_instructor', 'local_metadata'));
                $mform->setDefault('course_instructor', $courseInstructor);

				// Form elements                

                // Enter faculty name.
                $course_faculty = $mform->addElement('text', 'course_faculty', get_string('course_faculty', 'local_metadata'), $attributes);
                //$mform->addRule('course_faculty', get_string('required'), 'required', null, 'client');
                if($courseinfo = $DB->get_record('courseinfo', array('courseid'=>$courseId))){
                    $mform->setDefault('course_faculty', $courseinfo->coursefaculty);
                }             

                // Add drop down menu for program types
                // TODO: FETCH DATA FROM DBTO MANIPULATE THE LIST
                $program_types = array();
                // -------------------------------------
                $program_type_selection = $mform->addElement('select', 'program_type', get_string('program_type', 'local_metadata'), $program_types, '');
                $mform->addRule('program_type', get_string('required'), 'required', null, 'client');


                // Add drop down menu for course categories
                // TODO: FETCH DATA FROM DBTO MANIPULATE THE LIST
                $course_categories = array();
                // -------------------------------------
                $course_category_selection = $mform->addElement('select', 'course_category', get_string('course_category', 'local_metadata'), $course_categories, '');
                $mform->addRule('course_category', get_string('required'), 'required', null, 'client');


                // TODO: EDITOR HAS AUTOSAVE AND AUTORESTORE DATA, WHICH WILL REMOVE THE FETCHED DATA FROM DB
                // Add editor for create or modify course description.              
                // Get default course description from DB.
                // If description does not exist in the extra table, display the default description.
                $default_description = $course->summary;
                //$course_description_editor = $mform->addElement('editor', 'course_description', get_string('course_description', 'local_metadata'));
                $mform->addElement('textarea', 'course_description', get_string("course_description", "local_metadata"), 'wrap="virtual" rows="5" cols="70"');
                if($courseinfo){
                    $current_description = $courseinfo->coursedescription;
                    $mform->setDefault('course_description', $current_description);
                    //$course_description_editor->setValue(array('text' => $current_description) );
                }else{
                    $mform->setDefault('course_description', $default_description);
                    //$course_description_editor->setValue(array('text' => $default_description) );
                }
                $mform->addRule('course_description', get_string('required'), 'required', null, 'client');
                $mform->setType('course_description', PARAM_RAW);      

                // Add course objective
                //-------------------------------------------------------------------------------
                $mform->addElement('header', 'obj_knowledge_header', get_string('obj_knowledge_header', 'local_metadata'));
                $knowledge_desc = $mform->addElement('text', 'knowledge_desc', get_string('knowledge_desc', 'local_metadata'), '');
                $knowledge_array = array();
                $knowledge_array[] = $mform->createElement('text', 'knowledge_option', get_string('knowledge_label', 'local_metadata'));
                $knowledge_array[] = $mform->createElement('hidden', 'knowledge_id', 0);
                         
                if ($this->_instance){
                    $repeatk = $DB->count_records('knowledge_options', array('knowledge_id'=>$this->_instance));
                    $repeatk += 1;
                } else {
                    $repeatk = 1;
                }
                                 
                $knowledge_options = array();       
                $mform->setType('knowledge_option', PARAM_CLEANHTML);
                $mform->setType('knowledge_id', PARAM_INT);
                $this->repeat_elements($knowledge_array, $repeatk, $knowledge_options, 'option_repeats', 'option_add_fields', 1, null, true);

                $mform->closeHeaderBefore('obj_skill_header');

                //-------------------------------------------------------------------------------
                $mform->addElement('header', 'obj_skill_header', get_string('obj_skill_header', 'local_metadata'));
                $skill_desc = $mform->addElement('text', 'skill_desc', get_string('skill_desc', 'local_metadata'), '');
                $skill_array = array();
                $skill_array[] = $mform->createElement('text', 'skill_option', get_string('skill_label', 'local_metadata'));
                $skill_array[] = $mform->createElement('hidden', 'skill_id', 0);
                                                            
                if ($this->_instance){
					$repeats = $DB->count_records('skill_options', array('skill_id'=>$this->_instance));
                    $repeats += 1;
                } else {
                    $repeats = 1;
                }
                                                                                                 
                $skill_options = array();       
                $mform->setType('skill_option', PARAM_CLEANHTML);
                $mform->setType('skill_id', PARAM_INT);
                $this->repeat_elements($skill_array, $repeats, $skill_options, 'option_repeats', 'option_add_fields', 1, null, true);

                $mform->closeHeaderBefore('obj_attitude_header');

                //-------------------------------------------------------------------------------
                $mform->addElement('header', 'obj_attitude_header', get_string('obj_attitude_headerr', 'local_metadata'));
                $attitude_desc = $mform->addElement('text', 'attitude_desc', get_string('attitude_desc', 'local_metadata'), '');
                $attitude_array = array();
                $attitude_array[] = $mform->createElement('text', 'attitude_option', get_string('attitude_label', 'local_metadata'));
                $attitude_array[] = $mform->createElement('hidden', 'attitude_id', 0);
                                                            
                if ($this->_instance){
					$repeata = $DB->count_records('attitude_options', array('attitude_id'=>$this->_instance));
                    $repeata += 1;
                } else {
                    $repeata = 1;
                }
                                                                                                 
                $attitude_options = array();       
                $mform->setType('attitude_option', PARAM_CLEANHTML);
                $mform->setType('attitude_id', PARAM_INT);
                $this->repeat_elements($attitude_array, $repeata, $attitude_options, 'option_repeats', 'option_add_fields', 1, null, true);

                $mform->closeHeaderBefore('course_assessment');

                // Add number of assessment
                // TODO: MANIPULATE ASSESSMENT FIELD AS SPECIFIED
                $course_assessment = $mform->addElement('text', 'course_assessment', get_string('assessment_counter', 'local_metadata'), $attributes);
                $mform->addRule('course_assessment', get_string('required'), 'required', null, 'client');
                $mform->addRule('course_assessment', get_string('err_numeric', 'local_metadata'), 'numeric', null, 'client');

                if($courseinfo){
                    $mform->setDefault('course_assessment', $courseinfo->assessmentnumber);
                }

                // Add number of session
                // TODO: MANIPULATE SESSION FIELD AS SPEFICIED
                $course_assessment = $mform->addElement('text', 'course_session', get_string('session_counter', 'local_metadata'), $attributes);
                $mform->addRule('course_session', get_string('required'), 'required', null, 'client');
                $mform->addRule('course_session', get_string('err_numeric', 'local_metadata'), 'numeric', null, 'client');

                if($courseinfo){
                    $mform->setDefault('course_session', $courseinfo->sessionnumber);
                }

                // Add multi selection list for graduate attributes.
                // TODO: MANIPULATE THE LIST FROM DB
                $course_gradAtts = array();
                $course_gradAtts[] = 'attribute 1';
                $course_gradAtts[] = 'attribute 2';
                // -------------------------------------
                $course_gradAtts_selection = $mform->addElement('select', 'course_gradAtt', get_string('course_gradAtt', 'local_metadata'), $course_gradAtts, '');
                $course_gradAtts_selection->setMultiple(true);
                //$mform->addRule('course_gradAtt', get_string('required'), 'required', null, 'client');


		// Add form buttons
		$this->add_action_buttons();
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
                //$course_info->coursedescription = $data->course_description['text'];
                $course_info->coursedescription = $data->course_description;
                $course_info->coursefaculty = $data->course_faculty;
                $course_info->assessmentnumber = $data->course_assessment;
                $course_info->sessionnumber = $data->course_session;

                if($existRecord = $DB->get_record('courseinfo', array('courseid'=>$course->id)) ){
                // Must have an entry for 'id' to map the table specified.
                    $course_info->id = $existRecord->id;
                    $update_courseinfo = $DB->update_record('courseinfo', $course_info, false);
                    echo 'Existing data is updated.';
                }else{
                    $insert_courseinfo = $DB->insert_record('courseinfo', $course_info, false);
                    echo 'New data is added.';
                }
        }


}

?>
