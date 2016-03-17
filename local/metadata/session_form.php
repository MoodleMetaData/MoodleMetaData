<?php
require_once '../../config.php';
require_once $CFG->dirroot.'/lib/formslib.php';
require_once $CFG->dirroot.'/lib/datalib.php';

require_once 'lib.php';
require_once 'metadata_form.php';
require_once 'recurring_element_parser.php';


/**
 * The form to display the tab for sessions
 *
 * Requires the argument 'sessions', which should be the array of sessions
 *   for the current course loaded from the database
 *
 * For an example, see how it is instantiated in insview.php
 *
 * To look at how deleting a recurring element is done, see definition_after_data and save_data.
 *   As well, see the elements was_deleted and delete_session (the delete button) in add_session_repeat_template
 *
 *
 */
class session_form extends metadata_form {
    const NUM_PER_PAGE = 10;
    const TOPIC_SEPARATOR = '&|&|';
    const DATE_FROM_FROM_FILE = 'Y-m-d';

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
     * Will return all of the length options
     *   May eventually load them from the configuration for the plugin
     *
     * @return array containing string of all types
     */
    public static function get_session_lengths() {
        // TODO: Will probably need to change this
        $types = array('50 minutes', '80 minutes', '110 minutes', '140 minutes', '170 minutes');

        return $types;
    }
    
    public function get_page_change() {
        if ($this->_form->getSubmitValue('previousPage') !== null) {
            return -1;
        } else if ($this->_form->getSubmitValue('nextPage') !== null) {
            return 1;
        } else {
            return 0;
        }
    }
    
    /*
     * Will determine if the sessions were uploaded
     *
     * @return boolean for if use wanted to upload file
     */
    public function sessions_were_uploaded() {
        return $this->_form->getSubmitValue('upload_sessions') !== null;
    }

    public function upload_sessions() {
		global $course, $DB;
		
        $files = $this->get_draft_files('uploaded_sessions');
        
        if (!empty($files)) {
            $file = reset($files); 
            $content = $file->get_content();
            $all_rows = explode("\n", $content);
            
            $courseid = get_course_id();
            // Need to delete everything related to course sessions, and each session
            foreach ($this->_customdata['sessions'] as $session) {
                $this->delete_all_relations_to_session($session->id);
            }
            
            $DB->delete_records('coursesession', array('courseid'=>$courseid));
            
            
            foreach ($all_rows as $row) {
                if ($row != "") {
                    $this->parse_and_save_session_row($row, $courseid);
                }
            }
        }
    }
    
    private function parse_and_save_session_row($row, $courseid) {
        global $DB;
        // Parse the row
        $row = str_getcsv($row);
        print_object($row);
        
        $data = array();
        $data['courseid'] = $courseid;
        $data['sessiontitle'] = $row[0];
        $data['sessiondescription'] = $row[1];
        $data['sessionguestteacher'] = $row[2];
        $data['sessiontype'] = $row[3];
        $data['sessionlength'] = $row[4];
        
        $date = DateTime::createFromFormat(session_form::DATE_FROM_FROM_FILE, $row[5]);
        $data['sessiondate'] = $date->getTimestamp();
        
        // Then, save the session and get ids
        $id = $DB->insert_record('coursesession', $data);
        
        // Then, parse all remaining topics
        $topics = array_slice($row, 6);
        foreach ($topics as $topicname) {
            if ($topicname == "") {
                continue;
            }
            
            
            $newLink = new stdClass();
            $newLink->sessionid = $id;
            $newLink->topicname = $topicname;
            $DB->insert_record('sessiontopics', $newLink, false);
        }
    }
    
    /**
     * Will save the given data, that should be from calling the get_data function. Data will be all of the sessions in the course
     *
     * Also handles removing elements that should be deleted from the form.
     *
     * @param object $data value from calling get_data on this form
     *
     */
    public function save_data($data) {
        global $DB;
        
        // Set up the recurring element parser
        $allChangedAttributes = array('sessiontitle', 'sessiondescription', 'sessionguestteacher', 'sessiontype', 'sessionlength', 'sessiondate', 'assessments', 'was_deleted', 'all_topics_text_array');
        
        
        
        $learningObjectiveTypes = get_learning_objective_types();
        foreach ($learningObjectiveTypes as $learningObjectiveType) {
            $allChangedAttributes[] = 'learning_objective_'.$learningObjectiveType;
        }
        
        $types = session_form::get_session_types();
        $lengths = session_form::get_session_lengths();
        $convertedAttributes = array('sessiontype' => function($value) use ($types) { return $types[$value]; },
                                     'sessionlength' => function($value) use ($lengths) { return $lengths[$value]; }
                                     );

        $session_recurring_parser = new recurring_element_parser('coursesession', 'sessions_list', $allChangedAttributes, $convertedAttributes);
        
        

        // Get the tuples (one for each session) from the parser
        $tuples = $session_recurring_parser->getTuplesFromData($data);
        
        // Handles deleting a session
        foreach ($tuples as $tupleKey => $tuple) {
            // Clean out the sessionobjectives and session_related_assessment for this session
            $this->delete_all_relations_to_session($tuple['id']);
            
            // If the tuple has been deleted, then remove it from the database
            if ($tuple['was_deleted'] == true) {
                $session_recurring_parser->deleteTupleFromDB($tuple);
                
                // Finally, remove it from the tuples that will be saved, because otherwise will just be resaved anyway
                unset($tuples[$tupleKey]);
            }
        }
        
        // Save the remaining data for the sessions/tuples
            // Will also update the id for elements that are new
        $session_recurring_parser->saveTuplesToDB($tuples);
        
        // Handles updating the objectives and related assessments
        foreach ($tuples as $tupleKey => $tuple) {
            
            // Save the learning_objective
            // Template for this was found in \mod\glossary\edit.php
            $learningObjectiveTypes = get_learning_objective_types();
            foreach ($learningObjectiveTypes as $learningObjectiveType) {
                $key = 'learning_objective_'.$learningObjectiveType;
                if (array_key_exists($key, $tuple) and is_array($tuple[$key])) {
                    foreach ($tuple[$key] as $objectiveId) {
                        $newLink = new stdClass();
                        $newLink->sessionid = $tuple['id'];
                        $newLink->objectiveid = $objectiveId;
                        $DB->insert_record('sessionobjectives', $newLink, false);
                    }
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
            
            // Save the topics
            // Template for this was found in \mod\glossary\edit.php
            if (array_key_exists('all_topics_text_array', $tuple) and !is_null($tuple['all_topics_text_array'])) {
                $topic_array = explode(session_form::TOPIC_SEPARATOR, $tuple['all_topics_text_array']);
                
                foreach ($topic_array as $topicname) {
                    $newLink = new stdClass();
                    $newLink->sessionid = $tuple['id'];
                    $newLink->topicname = $topicname;
                    $DB->insert_record('sessiontopics', $newLink, false);
                }
            }
        }
    }
    
    /**
     * Will delete from all tables information that is related to the sessions,
     *   and that is set within this form
     *   So topics, objectives, and related assessments
     */
    private function delete_all_relations_to_session($sessionid) {
        global $DB;
        
        $DB->delete_records('sessionobjectives', array('sessionid'=>$sessionid));
        $DB->delete_records('session_related_assessment', array('sessionid'=>$sessionid));
        $DB->delete_records('sessiontopics', array('sessionid'=>$sessionid));
    }
    
    /**
     * Will set up the form elements
     * @see lib/moodleform#definition()
     */
    function definition() {
        parent::definition();
        
        $sessions = $this->_customdata['sessions'];
        
        $page_num = optional_param('page', 0, PARAM_INT);
        $subset_included = array_slice($sessions, $page_num * self::NUM_PER_PAGE, self::NUM_PER_PAGE);
        $count = min(count($subset_included), self::NUM_PER_PAGE);
        
        $this->setup_upload_sessions();
        $this->add_session_repeat_template($count);
        

        $this->setup_data_for_repeat($subset_included);
        
        $this->add_page_buttons($page_num, count($sessions));
        

        $this->add_action_buttons();
    }
    
    /**
	 * Add form elements for uploading all sessions
	 */
	private function setup_upload_sessions(){
        $mform = $this->_form;
        
		$mform->addElement('header', 'upload_sessions_header', get_string('upload_sessions_header', 'local_metadata'));
        
		$mform->addHelpButton('upload_sessions_header', 'upload_sessions_header', 'local_metadata');
		$mform->addElement('filepicker', 'uploaded_sessions', get_string('file'), null, array('maxbytes' => 0, 'accepted_types' => '.csv'));
		$mform->addElement('submit', 'upload_sessions', get_string('upload_sessions', 'local_metadata'));
		$mform->closeHeaderBefore('sessions_list_add_element');
	}

    /**
     *  Will set up a repeate template, with elements for each piece of required data
     *
     *  @param int $numSessions number of Sessions that have been created for the course
     */
    private function add_session_repeat_template($numSessions) {
        global $DB;
        $mform = $this->_form;

        $repeatarray = array();
	    $repeatarray[] = $mform->createElement('header', 'sessionheader');
        
        $repeatarray[] = $mform->createElement('text', 'sessiontitle', get_string('session_title', 'local_metadata'));
        
        $repeatarray[] = $mform->createElement('textarea', 'sessiondescription', get_string('session_description', 'local_metadata'));
        
        $repeatarray[] = $mform->createElement('text', 'sessionguestteacher', get_string('session_guest_teacher', 'local_metadata'));
        

        $repeatarray[] = $mform->createElement('select', 'sessiontype', get_string('session_type', 'local_metadata'), session_form::get_session_types());
        
        $repeatarray[] = $mform->createElement('select', 'sessionlength', get_string('session_length', 'local_metadata'), session_form::get_session_lengths());

        $repeatarray[] = $mform->createElement('date_selector', 'sessiondate', get_string('session_date', 'local_metadata'));


        // Set up the select for learning objectives
            // Will separate them based on type
            // Then, everytime need to deal with them, will also deal with them separated by type
        $learningObjectives = get_course_learning_objectives();
        $learningObjectivesList = array();
        foreach ($learningObjectives as $learningObjective) {
            $learningObjectivesList[$learningObjective->objectivetype][$learningObjective->id] = $learningObjective->objectivename;
        }
        
        $learningObjectiveTypes = get_learning_objective_types();
        foreach ($learningObjectiveTypes as $learningObjectiveType) {
            $options = array();
            if (array_key_exists($learningObjectiveType, $learningObjectivesList)) {
                $options = $learningObjectivesList[$learningObjectiveType];
            }
            
            $learningObjectivesEl = $mform->createElement('select', 'learning_objective_'.$learningObjectiveType, get_string('learning_objective_'.$learningObjectiveType, 'local_metadata'), $options);
            $learningObjectivesEl->setMultiple(true);
            $repeatarray[] = $learningObjectivesEl;
        }
        


        // Set up the select for assessments
        $assessments = get_table_data_for_course('courseassessment');
        $assessmentsList = array();
        foreach ($assessments as $assessment) {
            $assessmentsList[$assessment->id] = $assessment->assessmentname;
        }
        $assessmentsEl = $mform->createElement('select', 'assessments', get_string('related_assessments', 'local_metadata'), $assessmentsList);
        $assessmentsEl->setMultiple(true);
        $repeatarray[] = $assessmentsEl;

        
        $repeatoptions = array();
        $this->setup_topic_options($mform, $repeatarray, $repeatoptions);
        
        
        
        $repeatarray[] = $mform->createElement('submit', 'delete_session', get_string('deletesession', 'local_metadata'));
        $mform->registerNoSubmitButton('delete_topics');
        $this->_recurring_nosubmit_buttons[] = 'delete_topics';
        
        
        // Add needed hidden elements
        // Stores the id for each element
        $repeatarray[] = $mform->createElement('hidden', 'coursesession_id', -1);
        $repeatarray[] = $mform->createElement('hidden', 'was_deleted', false);
        
        $repeatoptions['sessionheader']['default'] = get_string('new_session_header', 'local_metadata');
        
        // Moodle complains if some elements aren't given a type
        $repeatoptions['sessiontitle']['type'] = PARAM_TEXT;
        $repeatoptions['sessionguestteacher']['type'] = PARAM_TEXT;
        $repeatoptions['all_topics_text_array']['type'] = PARAM_TEXT;
        $repeatoptions['new_topic']['type'] = PARAM_TEXT;
        $repeatoptions['coursesession_id']['type'] = PARAM_INT;
        $repeatoptions['was_deleted']['type'] = PARAM_RAW;
        

        // Add the repeating elements to the form
        $this->repeat_elements($repeatarray, $numSessions,
            $repeatoptions, 'sessions_list', 'sessions_list_add_element', 1, get_string('add_session', 'local_metadata'));
    }

    /**
     *  Will set up the data for each of the elements in the repeat_elements
     *  
     *
     */
    private function setup_data_for_repeat($sessions) {
        $mform = $this->_form;
        $key = 0;
        

        foreach ($sessions as $session)
        {
            $index = '['.$key.']';
            
            // Add the help button for sessionguestteacher
            $mform->addHelpButton('sessionguestteacher'.$index, 'session_guest_teacher', 'local_metadata');
            
            if ($session->sessiontitle == '') {
                $mform->setDefault('sessionheader'.$index, get_string('unnamed_session', 'local_metadata'));
            } else {
                $mform->setDefault('sessionheader'.$index, $session->sessiontitle);
            }
            
            // Easiest way to set the initial data is to set the default for each session in sessions
            $mform->setDefault('coursesession_id'.$index, $session->id);
            $mform->setDefault('sessiontitle'.$index, $session->sessiontitle);
            $mform->setDefault('sessionguestteacher'.$index, $session->sessionguestteacher);
            $mform->setDefault('sessiondescription'.$index, $session->sessiondescription);
            $mform->setDefault('sessiondate'.$index, $session->sessiondate);
            $mform->setDefault('sessiondate'.$index, $session->sessiondate);

            // Handled specially, because the default must be an int, which needs to be translated from string in database
            $types = session_form::get_session_types();
            $mform->setDefault('sessiontype'.$index, array_search($session->sessiontype, $types));
            
            // Handled specially, because the default must be an int, which needs to be translated from string in database
            $lengths = session_form::get_session_lengths();
            $mform->setDefault('sessionlength'.$index, array_search($session->sessionlength, $lengths));

            $this->setup_data_from_database_for_session($mform, $index, $session);
            $key += 1;
        }
    }
    
    /**
     *  Will add the buttons on the bottom
     *  
     *
     */
    private function add_page_buttons($page_num, $num_sessions) {
        $mform = $this->_form;
        
        $page_change_links=array();
        
        // Back page button
        $page_change_links[] = $mform->createElement('submit', 'previousPage', get_string('previous_page', 'local_metadata'));
        
        // If is on the first page
        if ($page_num === 0) {
            $mform->disabledIf('previousPage', null);
        }
    
        // Next page button
        $page_change_links[] = $mform->createElement('submit', 'nextPage', get_string('next_page', 'local_metadata'));
        
        // If the next page would be empty
        if (($page_num + 1) * self::NUM_PER_PAGE >= $num_sessions) {
            $mform->disabledIf('nextPage', null);
        }
        
        $mform->addGroup($page_change_links, 'buttonarray', '', array(' '), false);
    }
    
    function setup_data_from_database_for_session($mform, $index, $session) {
        global $DB;
        // Load the learning objectives for the session
        // Template for this was found in \mod\glossary\edit.php
        if ($learningObjectivesArr = $DB->get_records_menu("sessionobjectives", array('sessionid'=>$session->id), '', 'id, objectiveid')) {
            $learningObjectiveTypes = get_learning_objective_types();
            foreach ($learningObjectiveTypes as $learningObjectiveType) {
                $mform->setDefault('learning_objective_'.$learningObjectiveType.$index, array_values($learningObjectivesArr));
            }
            
        }

        // Load the assessments for the session
        // Template for this was found in \mod\glossary\edit.php
        if ($assessmentsArr = $DB->get_records_menu("session_related_assessment", array('sessionid'=>$session->id), '', 'id, assessmentid')) {
            $mform->setDefault('assessments'.$index, array_values($assessmentsArr));
        }
        
        
        // Will actually add them into select in the definition_after_data function
        if ($topics_array = $DB->get_records_menu("sessiontopics", array('sessionid'=>$session->id), '', 'id, topicname')) {
            // Create a string as array to store in form
            $topics_as_string = implode(session_form::TOPIC_SEPARATOR, $topics_array);
            
            // Add to the hidden array
            // Need to update the default, incase it is has already been changed
            $mform->setDefault('all_topics_text_array'.$index, $topics_as_string);
        }
    }
    
    
    /**
     *  This function is used for deleteing a session, and interacting with topics.
     *      Both displaying and editing the topic list
     *  
     *
     */
    function definition_after_data() {
        parent::definition_after_data();
        $mform = $this->_form;
        
        $numRepeated = $mform->getElementValue('sessions_list');
        
        // Go through each session, and delete elements for ones that should be deleted
        for ($key = 0; $key < $numRepeated; ++$key) {
            $index = '['.$key.']';
            $deleted = $mform->getSubmitValue('delete_session'.$index);
            
            // If a button is pressed, then doing $mform->getSubmitValue(buttonId) will return a non-null vaue
                // However, if other buttons are subsequently pressed, then $mform->getSubmitValue(buttonId) will return null
                // So use the element 'was_deleted' for that repeated element to store if has been deleted
            if ($deleted or $mform->getElementValue('was_deleted'.$index) == true) {
                // If deleted, just remove the visual elements
                    // Will not save to the database until the user presses submit
                $mform->removeElement('sessionheader'.$index);
                $mform->removeElement('sessiontitle'.$index);
                $mform->removeElement('sessiondescription'.$index);
                $mform->removeElement('sessionguestteacher'.$index);

                $mform->removeElement('sessiontype'.$index);
                $mform->removeElement('sessionlength'.$index);

                $mform->removeElement('sessiondate'.$index);
                
                
                $learningObjectiveTypes = get_learning_objective_types();
                foreach ($learningObjectiveTypes as $learningObjectiveType) {
                    $mform->removeElement('learning_objective_'.$learningObjectiveType.$index);
                }

                $mform->removeElement('assessments'.$index);

                $mform->removeElement('manage_topics_group'.$index);
                $mform->removeElement('add_topic_group'.$index);
                
                $mform->removeElement('delete_session'.$index);
                
                $mform->getElement('was_deleted'.$index)->setValue(true);
            } else {
                $this->update_topics($mform, $index);
                
                // New element, so expand header by default
                if ($mform->getElement('coursesession_id'.$index)->getValue() == -1) {
                    $mform->setExpanded('sessionheader'.$index);
                }
                
            }
        }
    }

    
    
    
    // Topic related functions
    
    private function setup_topic_options($mform, &$repeatarray, &$repeatoptions) {
        // Viewing already added topics
        $groupitems = array();
        
        // Load the options in the select for each session individually, where set the defaults
        // Need to save them in a hidden array, since submitting would reset the select
        $groupitems[] = $mform->createElement('hidden', 'all_topics_text_array', '');
        
        $course_topic_selection = $mform->createElement('select', 'all_topics');
        $course_topic_selection->setMultiple(true);
        $groupitems[] = $course_topic_selection;
        
        
		// Delete Button
		$groupitems[] = $mform->createElement('submit', 'delete_topics', get_string('delete'));
        $this->_recurring_nosubmit_buttons[] = 'create_topic';
        $mform->registerNoSubmitButton('create_topic');
        
		$repeatarray[] = $mform->createElement('group', 'manage_topics_group', get_string('manage_topics', 'local_metadata'), $groupitems, null, false);
        
        $repeatoptions['delete_topics']['disabledif'] = array('all_topics', 'noitemselected');
        
        // Adding a new topics
        $groupitems = array();
		$groupitems[] = $mform->createElement('text', 'new_topic');
		$groupitems[] = $mform->createElement('submit', 'create_topic', get_string('add_topic', 'local_metadata'));
        $this->_recurring_nosubmit_buttons[] = 'delete_session';
        $mform->registerNoSubmitButton('delete_session');
                
        $repeatarray[] = $mform->createElement('group', 'add_topic_group', '', $groupitems, null, false);
    }
    
    private function get_topic_text_array($index) {
        $manage_topics_group = $this->_form->getElement('manage_topics_group'.$index);
        
        // The select is the first item in the elements
        return $manage_topics_group->getElements()[0];
    }
    
    private function get_all_topics($index) {
        $manage_topics_group = $this->_form->getElement('manage_topics_group'.$index);
        
        // The select is the second item in the elements
        return $manage_topics_group->getElements()[1];
    }
    
    private function get_new_topic_value($index) {
        $add_topic_group = $this->_form->getElement('add_topic_group'.$index);
        
        // Is always in position 0
        $new_topic = $add_topic_group->getElements()[0];
        
        return $new_topic->getValue();
    }
    
    
    private function update_topics($mform, $index) {
        $topic_was_added = $mform->getSubmitValue('create_topic'.$index);
        $topic_was_deleted = $mform->getSubmitValue('delete_topics'.$index);
        // Add to the all_topics select
        // The select is the second item in the elements
        $all_topics = $this->get_all_topics($index);
        
        $topics_text_array = $this->get_topic_text_array($index);
        $topics_array = explode(session_form::TOPIC_SEPARATOR, $topics_text_array->getValue());
        
        if ($topic_was_deleted) {
            $selected = $all_topics->getValue();
            if (count($selected) > 0 and is_numeric($selected[0])) {
                foreach ($selected as $key=> $indexOfDeleted) {
                    unset($topics_array[$indexOfDeleted]);
                }
            }
        }
        
        if ($topic_was_added) {
            $new_topic = $this->get_new_topic_value($index);
            $index_added_to = count($topics_array);
            
            $topics_array[] = $new_topic;
        }
        
        // Need to load all of the existing topics, including ones added. The $topics_array will not be updated with new ones
        $topics_text_array->setValue(implode(session_form::TOPIC_SEPARATOR, $topics_array));
        foreach ($topics_array as $id=>$topic) {
            $all_topics->addOption($topic, $id);
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
}

?>
