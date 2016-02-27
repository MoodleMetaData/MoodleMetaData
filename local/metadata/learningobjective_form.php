<?php
require_once '../../config.php';
require_once $CFG->dirroot.'/lib/formslib.php';
require_once $CFG->dirroot.'/lib/datalib.php';

require_once 'lib.php';

class learningobjective_form extends moodleform {
    function definition() {
        global $CFG, $USER; //Declare our globals for use
        $mform = $this->_form; //Tell this object to initialize with the properties of the Moodle form.

        // Form elements

        //$context = context_course::instance($csid);
        // Assumes it has the data added
        $lobjectives = get_table_data_for_course('courseobjectives');

        $repeatarray = array();
        $repeatarray[] = $mform->createElement('text', 'objectivename', get_string('lobjective_name', 'local_metadata'));
        $repeatarray[] = $mform->createElement('hidden', 'objectiveid', 0);
	$repeatarray[] = $mform->createElement('text', 'subobj_amt', get_string('subobjective_amt', 'local_metadata'));
	$mform->setType('subobj_amt',PARAM_INT);
	$repeatarray[] = $mform->createElement('button', 'subobjbtn', get_string('subobjective', 'local_metadata'));

        // Add some separation between different courses
        $repeatarray[] = $mform->createElement('html', '<hr>');

        $repeateloptions = array();
        $repeateloptions['objectiveid']['default'] = -1;

        $this->repeat_elements($repeatarray, count($lobjectives),
            $repeateloptions, 'option_repeats', 'option_add_fields', 1, get_string('add_objective', 'local_metadata'), true);


        $key = 0;
        foreach ($lobjectives as $lobjective)
        {
            $index = '['.$key.']';
            $mform->setDefault('objectivename'.$index, $lobjective->objectivename);
            $mform->setDefault('sessionid'.$index, $lobjective->sessionid);
            $key += 1;
        }

	$PAGE->requires->js('/mod/data/data.js');

	addsubobjective();


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
