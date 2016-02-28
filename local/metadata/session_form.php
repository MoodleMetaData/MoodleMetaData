<?php
require_once '../../config.php';
require_once $CFG->dirroot.'/lib/formslib.php';
require_once $CFG->dirroot.'/lib/datalib.php';

require_once 'lib.php';
require_once 'recurring_element_parser.php';


/**
 * The form to display the tab for sessions
 *
 * Requires the argument 'sessions', which should be the array of sessions
 *   for the current course loaded from the database
 *
 * For an example, see how it is instantiated in insview.php
 *
 */
class session_form extends moodleform {

    /**
     * Will set up the form elements
     * @see lib/moodleform#definition()
     */
    function definition() {
        global $CFG, $USER;

        $sessions = $this->_customdata['sessions'];

        $this->add_session_repeat_template(count($sessions));

        $this->setup_data_for_repeat($sessions);

        $this->add_action_buttons();
    }

    /**
     *  Will set up a repeate template, with elements for each piece of required data
     *
     *  @param int $numSessions number of Sessions that have been created for the course
     */
    function add_session_repeat_template($numSessions) {
        global $DB;
        $mform = $this->_form;

        $repeatarray = array();
        $repeatarray[] = $mform->createElement('text', 'sessiontopic', get_string('session_topic', 'local_metadata'));
        $repeatarray[] = $mform->createElement('textarea', 'sessiondescription', get_string('session_description', 'local_metadata'));

        $repeatarray[] = $mform->createElement('select', 'sessiontype', get_string('session_type', 'local_metadata'), session_form::get_session_types());

        $repeatarray[] = $mform->createElement('date_selector', 'sessiondate', get_string('session_date', 'local_metadata'));


        
        $learningObjectives = get_course_learning_objectives();
        $learningObjectivesList = array();
        foreach ($learningObjectives as $learningObjective) {
            $learningObjectivesList[$learningObjective->id] = $learningObjective->objectivename;
        }
        $learningObjectivesEl = $mform->createElement('select', 'learning_objectives', get_string('learning_objectives', 'local_metadata'), $learningObjectivesList);
        $learningObjectivesEl->setMultiple(true);
        $repeatarray[] = $learningObjectivesEl;



        $assessments = get_table_data_for_course('courseassessment');
        $assessmentsList = array();
        foreach ($assessments as $assessment) {
            $assessmentsList[$assessment->id] = $assessment->assessmentname;
        }


        $assessmentsEl = $mform->createElement('select', 'assessments', get_string('related_assessments', 'local_metadata'), $assessmentsList);
        $assessmentsEl->setMultiple(true);
        $repeatarray[] = $assessmentsEl;




        $repeatarray[] = $mform->createElement('hidden', 'coursesession_id', 0);

        // Add some separation between different courses
        $repeatarray[] = $mform->createElement('html', '<hr>');

        $repeateloptions = array();
        $repeateloptions['coursesession_id']['default'] = null;

        $this->repeat_elements($repeatarray, $numSessions,
            $repeateloptions, 'sessions_list', 'sessions_list_add_element', 1, get_string('add_session', 'local_metadata'), true);
    }


    /**
     *  Will set up the data for each of the elements in the repeat_elements
     *
     */
    function setup_data_for_repeat($sessions) {
        $mform = $this->_form;
        $key = 0;

        foreach ($sessions as $session)
        {
            $index = '['.$key.']';
            $mform->setDefault('coursesession_id'.$index, $session->id);
            
            $mform->setDefault('sessiontopic'.$index, $session->sessiontopic);
            $mform->setDefault('sessiondescription'.$index, $session->sessiondescription);
            $mform->setDefault('sessiondate'.$index, $session->sessiondate);

            $mform->setDefault('sessiondate'.$index, $session->sessiondate);

            // Handled specially, because the default must be an int, which needs to be translated from string in database
            $types = session_form::get_session_types();
            $mform->setDefault('sessiontype'.$index, array_search($session->sessiontype, $types));

            
            global $DB;
            // Load the learning objectives
            // Template for this was found in \mod\glossary\edit.php
            if ($learningObjectivesArr = $DB->get_records_menu("sessionobjectives", array('sessionid'=>$session->id), '', 'id, objectiveid')) {
                $mform->setDefault('learning_objectives'.$index, array_values($learningObjectivesArr));
            }
            
            // Load the assessments
            // Template for this was found in \mod\glossary\edit.php
            if ($assessmentsArr = $DB->get_records_menu("session_related_assessment", array('sessionid'=>$session->id), '', 'id, assessmentid')) {
                $mform->setDefault('assessments'.$index, array_values($assessmentsArr));
            }

            $key += 1;
        }
    }

    /**
     * Ensure that the data the user entered is valid
     *
     * @see lib/moodleform#validation()
     */
    function validation($data, $files) {
        $errors = parent::validation($data, $files);

        return $errors;
    }


    /**
     * Will return all of the type options
     *   Will eventually load them from the configuration for the plugin
     *
     * @return array containing string of all types
     */
    public static function get_session_types() {
        $types = array('lecture', 'lab', 'seminar');

        return $types;
    }

    /**
     * Will save the given data.
     */
    public static function save_data($data) {
        $allChangedAttributes = array('sessiontopic', 'sessiondescription', 'sessiontype', 'sessiondate', 'learning_objectives', 'assessments');
        $types = session_form::get_session_types();
        $convertedAttributes = array('sessiontype' => function($value) use ($types) { return $types[$value]; });
        
        $session_recurring_parser = new recurring_element_parser('coursesession', 'sessions_list', $allChangedAttributes, $convertedAttributes);

        $tuples = $session_recurring_parser->getTuplesFromData($data);
        
        global $DB;
        
        foreach ($tuples as $tupleKey => $tuple) {
            // Clean out the sessionobjectives and 
            $DB->delete_records('sessionobjectives', array('sessionid'=>$tuple['id']));
            $DB->delete_records('session_related_assessment', array('sessionid'=>$tuple['id']));
            
            // Save the learning_objective
            // Template for this was found in \mod\glossary\edit.php
            if (array_key_exists('learning_objectives', $tuple) and is_array($tuple['learning_objectives'])) {
                foreach ($tuple['learning_objectives'] as $objectiveId) {
                    $newLink = new stdClass();
                    $newLink->sessionid = $tuple['id'];
                    $newLink->objectiveid = $objectiveId;
                    $DB->insert_record('sessionobjectives', $newLink, false);
                }
            }
            
            // Save the assessments
            // Template for this was found in \mod\glossary\edit.php
            
            if (array_key_exists('assessments', $tuple) and is_array($tuple['assessments'])) {
                foreach ($tuple['assessments'] as $assessmentId) {
                    $newLink = new stdClass();
                    $newLink->sessionid = $tuple['id'];
                    $newLink->assessmentid = $assessmentId;
                    $DB->insert_record('session_related_assessment', $newLink, false);
                }
            }
            
        }
        
        $session_recurring_parser->saveTuplesToDB($tuples);
    }

}

?>
