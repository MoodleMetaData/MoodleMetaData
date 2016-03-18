@local_metadata @local_metadata_session
Feature: Session tab pages
	In order to use the session tab
	As an instructor
	I do not want to need to see all sessions at once
  
  Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C_shortname | 0 |
    And I log in as "admin"
    And I am on homepage
    And I follow "Course 1"
    And I follow "Instructor Moodle Metadata"
    And I follow "Session"
  
  @javascript
  Scenario: Ensuring the page buttons are disabled if there are 10 or fewer sessions
    Given I create 10 sessions
    And I press "Save changes"
    And I wait to be redirected
    Then the "previousPage" "button" should be disabled
    And the "nextPage" "button" should be disabled
      
  @javascript
  Scenario: Ensuring the page buttons are disabled no matter how many are added, until is saved
    Given I create 11 sessions
    Then the "previousPage" "button" should be disabled
    And the "nextPage" "button" should be disabled
  
  
  @javascript
  Scenario: Ensuring the order for sessions is kept, and it displays the correct number
    Given I create 21 sessions with title "Title %s"
    And I press "Save changes"
    And I wait to be redirected
    Then the "previousPage" "button" should be disabled
    And the "nextPage" "button" should be enabled
    Then "sessiontitle[10]" "text" should not exist
    And the following fields match these values:
      | sessiontitle[0] | Title 0 |
      | sessiontitle[9] | Title 9 |
    Given I press "Next Page"
    And I wait to be redirected
    Then "sessiontitle[10]" "text" should not exist
    And the following fields match these values:
      | sessiontitle[0] | Title 10 |
      | sessiontitle[9] | Title 19 |
    And the "previousPage" "button" should be enabled
    And the "nextPage" "button" should be enabled
    Given I press "Next Page"
    And I wait to be redirected
    Then "sessiontitle[1]" "text" should not exist
    And the following fields match these values:
      | sessiontitle[0] | Title 20 |
    And the "previousPage" "button" should be enabled
    And the "nextPage" "button" should be disabled
    