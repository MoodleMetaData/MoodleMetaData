<?php
require_once '../../config.php';
require_once $CFG->dirroot.'/lib/formslib.php';
require_once $CFG->dirroot.'/lib/datalib.php';

require_once 'lib.php';

class session_form extends moodleform {
    function definition() {
        global $CFG, $USER; //Declare our globals for use
        $mform = $this->_form; //Tell this object to initialize with the properties of the Moodle form.

        // Form elements

        //$context = context_course::instance($csid);
        // Assumes it has the data added

        $sessions = get_table_data_for_course('coursesession');

        $types = array('lecture', 'lab', 'seminar');



        $repeatarray = array();
        $repeatarray[] = $mform->createElement('text', 'topic', get_string('session_topic', 'local_metadata'));
        $repeatarray[] = $mform->createElement('textarea', 'description', get_string('session_description', 'local_metadata'));
        $repeatarray[] = $mform->createElement('select', 'type', get_string('session_type', 'local_metadata'), $types);
        $repeatarray[] = $mform->createElement('date_selector', 'date', get_string('session_date', 'local_metadata'));
        $repeatarray[] = $mform->createElement('hidden', 'sessionid', 0);

        // Add some separation between different courses
        $repeatarray[] = $mform->createElement('html', '<hr>');

        $repeateloptions = array();
        $repeateloptions['sessionid']['default'] = -1;

        $this->repeat_elements($repeatarray, count($sessions),
            $repeateloptions, 'session_repeats', 'option_add_fields_session', 1, get_string('add_session', 'local_metadata'), true);


        $key = 0;
        foreach ($sessions as $session)
        {
            $index = '['.$key.']';
            $mform->setDefault('topic'.$index, $session->sessiontopic);
            $mform->setDefault('description'.$index, $session->sessiondescription);
            $mform->setDefault('type'.$index, $session->sessiontype);
            $mform->setDefault('date'.$index, $session->sessiondate);
            $mform->setDefault('sessionid'.$index, $session->sessionid);
            $key += 1;
        }


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
