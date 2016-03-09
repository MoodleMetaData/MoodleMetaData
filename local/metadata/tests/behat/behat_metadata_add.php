<?php

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use Behat\Behat\Context\Step\Given as Given;
use Behat\Gherkin\Node\TableNode as TableNode;
    
class behat_metadata_add extends behat_base {
    
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
}
?>