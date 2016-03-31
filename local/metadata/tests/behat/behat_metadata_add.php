<?php

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use Behat\Behat\Context\Step\Given as Given;
use Behat\Gherkin\Node\TableNode as TableNode;
    
class behat_metadata_add extends behat_base {
    
	/**
     * Creates course categories.
     *
     * @Given /^I create the following course categories for faculty "([^"]*)":$/
     *
     * @param TableNode $data Each row will be a course category information. Write out:
	 * 		| categoryname |
	 * 		| Course category name |
     * @param string $faculty_name The name from course_categories table
     *
     * @return Given[]
     */
	public function i_create_course_categories_for_faculty($faculty_name, TableNode $table){
		global $DB;
		
		if (!$faculty_id = $DB->get_field('course_categories', 'id', array('name' => $faculty_name))) {
            throw new Exception('The specified course category with name "' .$faculty_name. '" does not exist');
        }
        
		$initrecord = new stdClass();
		$initrecord->categoryid = $faculty_id;
		$initrecord->categoryname = 'Group';
		$parent_id = $DB->insert_record('coursecategories', $initrecord, true, false);
		
        foreach ($table->getHash() as $category_info) {
            $record = array();
			$record['categoryid'] = $faculty_id;
			$record['categoryname'] = $category_info['categoryname'];
			$record['node'] = $parent_id;
			
            // Add to coursecategories
            $id = $DB->insert_record('coursecategories', $record);
        }
        
        return array();
	}

	/**
     * Creates graduate attributes for specific course info.
     *
     * @Given /^I create the following graduate attributes:$/
     *
     * @param TableNode $data Each row will be a graduate attribute information. Write out:
	 * 		| attribute |
	 * 		| Graduate attribute name |
     *
     * @return Given[]
     */
	public function i_create_graduate_attributes(TableNode $table){
		global $DB;
        
		$initrecord = new stdClass();
		$initrecord->attribute = 'Group';
		$parent_id = $DB->insert_record('graduateattributes', $initrecord, true, false);
		
        foreach ($table->getHash() as $gradatt_info) {
            $record = array();
			$record['attribute'] = $gradatt_info['attribute'];
			$record['node'] = $parent_id;
			
            // Add to graduateattributes
            $id = $DB->insert_record('graduateattributes', $record);
        }
        
        return array();
	}
	
    /**
     * Creates general course information.
     *
     * @Given /^I create the following general info for course "([^"]*)":$/
     *
     * @param TableNode $data Each row will be a course information. Write out:
	 * 		| teachingassumption | courseterm | courseyear | assessmentnumber | sessionnumber | coursedescription |
	 * 		| Teaching Assumption | Course Term | Course Year | Number of assessment | Number of session | Course Description |
     * @param string $course_short The short name of the course
     *
     * @return Given[]
     */
	public function i_create_general_info_for_course($course_short, TableNode $table){
		global $DB;
		
		if (!$course = $DB->get_record('course', array('shortname' => $course_short))) {
            throw new Exception('The specified course with shortname "' . $course_short . '" does not exist');
        }
		
		if (!$categories = $DB->get_records('coursecategories')) {
            throw new Exception('No record in coursecategories table.');
        }
        
		$cat = 0;
		foreach($categories as $value){
			$cat = $value->id;
		}
		
        foreach ($table->getHash() as $course_info) {
            $record = array();
            $record['courseid'] = $course->id;
            $record['coursename'] = $course_short;
			$record['coursecategory'] = $cat;
			$record['teachingassumption'] = $course_info['teachingassumption'];
			$record['courseterm'] = $course_info['courseterm'];
			$record['courseyear'] = $course_info['courseyear'];
			$record['assessmentnumber'] = $course_info['assessmentnumber'];
			$record['sessionnumber'] = $course_info['sessionnumber'];
			$record['coursedescription'] = $course_info['coursedescription'];
            $record['facultyid'] = $course->category;

			
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
     * Creates N course assessments through the database
     *
     * @Given /^the following assessments for course "([^"]*)" exist:$/
     *
     * @param TableNode $data Each row will be a assessments. Should have at least the title. Everything else is optional
     * @param string $course_short The short name of the course
     *
     * @return Given[]
     */
    public function the_following_assessments_for_course_exist($course_short, TableNode $table) {
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
     * Creates N course assessments. Nothing is filled in for them
     *
     * @Given /^I create (\d+) assessments$/
     *
     * @param int $N The number of assessments to create
     *
     * @return Given[]
     */
    public function i_create_assessments($N) {
        $steps = array();
        for ($i = 0; $i < $N; $i++) {
            $steps[] = new Given('I press "Add Assessment"');
        }
        
        return $steps;
    }
    
    /**
     * Creates N course assessments. Each will have a title with the title formatted with the session number
     *
     * @Given /^I create (\d+) assessments with name "([^"]*)"$/
     *
     * @param int $N The number of assessments to create
     * @param string $title The value of the title
     *
     * @return Given[]
     */
    public function i_create_assessments_with_name($N, $title) {
        $steps = array();
        for ($i = 0; $i < $N; $i++) {
            $steps[] = new Given('I press "Add Assessment"');
            $assessmentfield = 'assessmentname['.$i.']';
            $formattedTitle = sprintf($title, $i);
            $steps[] = new Given('I set the field "'.$assessmentfield.'" to "'.$formattedTitle.'"');
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