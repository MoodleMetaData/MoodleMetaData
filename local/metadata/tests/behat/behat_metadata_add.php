<?php

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use Behat\Behat\Context\Step\Given as Given;
use Behat\Gherkin\Node\TableNode as TableNode;
    
class behat_metadata_add extends behat_base {
    
    /**
     * Creates general course information.
     *
     * @Given /^I create the following general info for course "([^"]*)":$/
     *
     * @param TableNode $data Each row will be a course information. Write out:
	 * 		| coursetopic | coursedescription | teachingassumption | coursefaculty | assessmentnumber | sessionnumber |
	 * 		| Topic name | Description | Teaching assumption | Faculty name | number | number |
     * @param string $course_short The short name of the course
     *
     * @return Given[]
     */
	public function i_create_general_info_for_course($course_short, TableNode $table){
		global $DB;
		
		if (!$course_id = $DB->get_field('course', 'id', array('shortname' => $course_short))) {
            throw new Exception('The specified course with shortname "' . $course_short . '" does not exist');
        }
        
        foreach ($table->getHash() as $course_info) {
            $record = array();
            $record['courseid'] = $course_id;
            $record['coursename'] = $course_short;
			$record['coursetopic'] = $course_info['coursetopic'];
			$record['coursedescription'] = $course_info['coursedescription'];
			$record['teachingassumption'] = $course_info['teachingassumption'];
            $record['coursefaculty'] = $course_info['coursefaculty'];
			$record['assessmentnumber'] = $course_info['assessmentnumber'];
			$record['sessionnumber'] = $course_info['sessionnumber'];
			
            // Add to courseinfo
            $id = $DB->insert_record('courseinfo', $record);
        }
        
        return array();
	}
	
	/**
     * Creates instructor information.
     *
     * @Given /^I create the following instructor info for course "([^"]*)" and user "([^"]*)":$/
     *
     * @param TableNode $data Each row will be a instructor information. Write out:
	 * 		| name | officelocation | officehours | email | phonenumber |
	 * 		| Instructor name | Office location | Office hours | email | phone number |
     * @param string $course_short The short name of the course
	 * @param string $user_name	   The user name
     *
     * @return Given[]
     */
	public function i_create_instructor_info_for_course($course_short, $user_name, TableNode $table){
		global $DB;
		
		if (!$course_id = $DB->get_field('course', 'id', array('shortname' => $course_short))) {
            throw new Exception('The specified course with shortname "' . $course_short . '" does not exist');
        }
        
		if (!$user_id = $DB->get_field('user', 'id', array('username' => $user_name))) {
            throw new Exception('The specified user with username "' . $user_name . '" does not exist');
        }
		
        foreach ($table->getHash() as $course_instructor) {
            $record = array();
			$record['courseid'] = $course_id;
            $record['userid'] = $user_id;
            $record['name'] = $course_instructor['name'];
			$record['officelocation'] = $course_instructor['officelocation'];
			$record['officehours'] = $course_instructor['officehours'];
			$record['email'] = $course_instructor['email'];
			$record['phonenumber'] = $course_instructor['phonenumber'];
			
            // Add to courseinfo
            $id = $DB->insert_record('courseinstructors', $record);
        }
        
        return array();
	}
	
	/**
     * Creates N course readings.
     *
     * @Given /^I create the following required readings for course "([^"]*)":$/
     *
     * @param TableNode $data Each row will be a learning objective. Write out:
	 * 		| readingname | readingurl |
	 * 		| Title | URL |
     * @param string $course_short The short name of the course
     *
     * @return Given[]
     */
	public function i_create_required_readings_for_course($course_short, TableNode $table){
		global $DB;
		
		if (!$course_id = $DB->get_field('course', 'id', array('shortname' => $course_short))) {
            throw new Exception('The specified course with shortname "' . $course_short . '" does not exist');
        }
		
        foreach ($table->getHash() as $coursereadings) {
            $record = array();
			$record['courseid'] = $course_id;
            $record['readingname'] = $coursereadings['readingname'];
			$record['readingurl'] = $coursereadings['readingurl'];

            // Add to courseinfo
            $id = $DB->insert_record('coursereadings', $record);
        }
        
        return array();
	}
	
    /**
     * Creates N learning objectives. Nothing is filled in for them
     *
     * @Given /^I create the following learning objectives for course "([^"]*)":$/
     *
     * @param TableNode $data Each row will be a learning objective. Write out:
     *        | objectivename | objectivetype |
     *        | First Name | First Type |
     * @param string $course_short The short name of the course
     *
     * @return Given[]
     */
    public function i_create_learning_objectives_for_course($course_short, TableNode $table) {
        global $DB;
        
        if (!$course_id = $DB->get_field('course', 'id', array('shortname' => $course_short))) {
            throw new Exception('The specified course with shortname "' . $course_short . '" does not exist');
        }
        
        foreach ($table->getHash() as $learning_objective) {
            $record = array();
            $record['objectivename'] = $learning_objective['objectivename'];
            $record['objectivetype'] = $learning_objective['objectivetype'];
            
            // Add to learningobjectives
            $id = $DB->insert_record('learningobjectives', $record);
            
            // Link to the wanted course
            $courseobjective = array();
            $courseobjective['objectiveid'] = $id;
            $courseobjective['courseid'] = $course_id;
            $id = $DB->insert_record('courseobjectives', $courseobjective);
        }
        
        return array();
    }
    
    
    /**
     * Creates N course assessments. Nothing is filled in for them
     *
     * @Given /^I create the following assessments for course "([^"]*)":$/
     *
     * @param TableNode $data Each row will be a assessments. Should have at least the title. Everything else is optional
     * @param string $course_short The short name of the course
     *
     * @return Given[]
     */
    public function i_create_assessments_for_course($course_short, TableNode $table) {
        global $DB;
        
        if (!$course_id = $DB->get_field('course', 'id', array('shortname' => $course_short))) {
            throw new Exception('The specified course with shortname "' . $course_short . '" does not exist');
        }
        
        foreach ($table->getHash() as $assessment) {
            $assessment['courseid'] = $course_id;
            $DB->insert_record('courseassessment', $assessment);
        }
        
        return array();
    }
    
    /**
     * Creates N course sessions. Nothing is filled in for them
     *
     * @Given /^I create (\d+) sessions$/
     *
     * @param int $N The number of sessions to create
     *
     * @return Given[]
     */
    public function i_create_sessions($N) {
        $steps = array();
        for ($i = 0; $i < $N; $i++) {
            $steps[] = new Given('I press "sessions_list_add_element"');
        }
        
        return $steps;
    }
    
    /**
     * Creates N course sessions. Each will have a title with the title formatted with the session number
     *
     * @Given /^I create (\d+) sessions with title "([^"]*)"$/
     *
     * @param int $N The number of sessions to create
     * @param string $title The value of the title
     *
     * @return Given[]
     */
    public function i_create_sessions_with_title($N, $title) {
        $steps = array();
        for ($i = 0; $i < $N; $i++) {
            $steps[] = new Given('I press "sessions_list_add_element"');
            $sessionfield = 'sessiontitle['.$i.']';
            $formattedTitle = sprintf($title, $i);
            $steps[] = new Given('I set the field "'.$sessionfield.'" to "'.$formattedTitle.'"');
        }
        
        return $steps;
    }
    
    /**
     * Adds a new topic to the session indexed with [$session_num]
     *
     * @Given /^I add to session (\d+) topic "([^"]*)"$/
     *
     * @param int $session_num The session indexed with [$session_num] 
     * @param string $topic The value of the topic to add
     *
     * @return Given[]
     */
    public function i_add_to_session_topic($session_num, $topic) {
        $steps = array(new Given('I set the field "new_topic['.$session_num.']" to "'.$topic.'"'),
                       new Given('I press "create_topic['.$session_num.']"'));
        
        return $steps;
    }
}
?>