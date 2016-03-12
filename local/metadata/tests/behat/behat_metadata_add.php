<?php

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use Behat\Behat\Context\Step\Given as Given;
use Behat\Gherkin\Node\TableNode as TableNode;
    
class behat_metadata_add extends behat_base {
    
    
    /**
     * Creates N course sessions. Nothing is filled in for them
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
     * Creates N course sessions. Nothing is filled in for them
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