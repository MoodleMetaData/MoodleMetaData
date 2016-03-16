@local_metadata @local_metadata_session
Feature: Session tab
	In order to use the session tab
	As an instructor
	I need to be able to add and delete sessions
  
  Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C_shortname | 0 |
    And I log in as "admin"
    And I am on homepage
    And I follow "Course 1"
    And I follow "Instructor Moodle Metadata"
    Then I follow "Session"
    
  @javascript
  Scenario: Deleting session before saving
    Given I create 2 sessions
    And I set the following fields to these values:
      | sessiontitle[0] | First title |
      | sessiontitle[1] | Second title |
    When I press "deleteSession[0]"
    And I press "Save changes"
    Then the following fields match these values:
      | sessiontitle[0] | Second title |
    And "sessiontitle[1]" "text" should not exist
    
  
  @javascript @current
  Scenario: Deleting session after saving
    Given I create 2 sessions
    And I set the following fields to these values:
      | sessiontitle[0] | First title |
      | sessiontitle[1] | Second title |
    And I press "Save changes"
    And I press "Second title"
    When I press "deleteSession[1]"
    And I press "Save changes"
    Then the following fields match these values:
      | sessiontitle[0] | First title |
    And "sessiontitle[1]" "text" should not exist