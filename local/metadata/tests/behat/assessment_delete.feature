@local_metadata @local_metadata_assessment
Feature: Assessment tab
	In order to use the assessment tab
	As an instructor
	I need to be able to add and delete assessment
  
  Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C_shortname | 0 |
    And I log in as "admin"
    And I am on homepage
    And I follow "Course 1"
    And I follow "Instructor Moodle Metadata"
    Then I follow "Assessment"
    
  @javascript
  Scenario: Deleting session before saving
    Given I create 2 assessments
    And I set the following fields to these values:
      | assessmentname[0] | First name |
      | assessmentname[1] | Second name |
    When I press "delete_assessment[0]"
    And I press "Save changes"
    Then the following fields match these values:
      | assessmentname[0] | Second name |
    And "assessmentname[1]" "text" should not exist
    
  
  @javascript
  Scenario: Deleting session after saving
    Given I create 2 assessments
    And I set the following fields to these values:
      | assessmentname[0] | First name |
      | assessmentname[1] | Second name |
    And I press "Save changes"
    And I expand all fieldsets
    When I press "delete_assessment[1]"
    And I press "Save changes"
    Then the following fields match these values:
      | assessmentname[0] | First name |
    And "assessmentname[1]" "text" should not exist