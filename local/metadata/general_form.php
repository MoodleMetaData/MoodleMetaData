<?php
require_once '../../config.php';
require_once $CFG->dirroot.'/lib/formslib.php';
require_once $CFG->dirroot.'/lib/datalib.php';

require_once 'lib.php';

class general_form extends moodleform {
	function definition() {
		global $CFG, $DB, $USER; //Declare our globals for use
                global $course;

                // initialize the form.
                $mform = $this->_form; //Tell this object to initialize with the properties of the Moodle form.

                $courseId = get_course_id();
		$mform->addElement('static', 'course_id', get_string('course_id', 'local_metadata'));
                $mform->setDefault('course_id', $courseId);
            
                $courseName = $course->fullname;
                $mform->addElement('static', 'course_name', get_string('course_name', 'local_metadata'));
                $mform->setDefault('course_name', $courseName);

                $courseInstructor = $USER->firstname.' '.$USER->lastname;
                $mform->addElement('static', 'course_instructor', get_string('course_instructor', 'local_metadata'));
                $mform->setDefault('course_instructor', $courseInstructor);

		// Form elements
		
                // Add editor for create or modify course description.              
                // Get default course description from DB.
                // If description does not exist in the extra table, display the default description.
                $default_description = $course->summary;
                $course_description_editor = $mform->addElement('editor', 'course_description', get_string('course_description', 'local_metadata'));
                
                if($courseinfo = $DB->get_record('courseinfo', array('courseid'=>$courseId))){
                    //echo 'Exist.';
                    $current_description = $courseinfo->coursedescription;
                    $course_description_editor->setValue(array('text' => $current_description) );
                }else{
                    //echo 'Does not exist.';
                    $course_description_editor->setValue(array('text' => $default_description) );
                }

                $mform->addRule('course_description', get_string('required'), 'required', null, 'client');
                $mform->setType('course_description', PARAM_RAW);      

		// Add selection list for course type		
		// ---------- testing purpose ----------
		$course_types = array();
		$course_types[] = 'type 1';
		$course_types[] = 'type 2';
		// -------------------------------------
		$course_type_selection = $mform->addElement('select', 'course_type', get_string('course_type', 'local_metadata'), $course_types, '');
		$mform->addRule('course_type', get_string('required'), 'required', null, 'client');

		// Add multi-selection list for course topics
		// ---------- testing purpose ----------
		$course_topics = array();
		$course_topics[] = 'topic 1';
		$course_topics[] = 'topic 2';
		// -------------------------------------
		$course_topic_selection = $mform->addElement('select', 'course_topic', get_string('course_topic', 'local_metadata'), $course_topics, '');
		$course_topic_selection->setMultiple(true);
		$mform->addRule('course_topic', get_string('required'), 'required', null, 'client');
		
		// Add multi-selection list for course learning objectives
		// ---------- testing purpose ----------
		$course_objectives = array();
		$course_objectives[] = 'objective 1';
		$course_objectives[] = 'objective 2';
		// -------------------------------------
		$course_objective_selection = $mform->addElement('select', 'course_objective', get_string('course_objective', 'local_metadata'), $course_objectives, '');
		$course_objective_selection->setMultiple(true);
		$mform->addRule('course_objective', get_string('required'), 'required', null, 'client');
		
                // Add number of assessment
                $course_assessment = $mform->addElement('text', 'course_assessment', get_string('assessment_counter', 'local_metadata'), $attributes);
                $mform->addRule('course_assessment', get_string('required'), 'required', null, 'client');
                $mform->addRule('course_assessment', get_string('err_numeric', 'local_metadata'), 'numeric', null, 'client');

                // Add number of session
                $course_assessment = $mform->addElement('text', 'course_session', get_string('session_counter', 'local_metadata'), $attributes);
                $mform->addRule('course_session', get_string('required'), 'required', null, 'client');
                $mform->addRule('course_session', get_string('err_numeric', 'local_metadata'), 'numeric', null, 'client');


/*                
                if ($mform->is_cancelled()) {
                    echo 'IS CANCELLED';                
                }
 */
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
}

?>

