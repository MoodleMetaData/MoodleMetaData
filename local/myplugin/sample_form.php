<?php
    require_once '../../config.php';
    require_once $CFG->dirroot.'/lib/formslib.php';
    require_once $CFG->dirroot.'/lib/datalib.php';
    
    class sample_form extends moodleform {
    	
        function definition() {
            global $CFG, $DB, $USER; //Declare our globals for use
            $mform = $this->_form; //Tell this object to initialize with the properties of the Moodle form.
            //Add all your form elements here
            $mform->addElement('text', 'email', get_string('email', 'local_myplugin'), 'maxlength="100" size="25" ');
        }
    }
    
?>