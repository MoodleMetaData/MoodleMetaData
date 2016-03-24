@local_metadata  @local_metadata_admin
Feature: Required entries tab
	In order to set items as required
	As an instructor
	I need to be able to enter information about an session
  
  Background:
    Given I log in as "admin"
    And I am on homepage
    And I expand "Site administration" node
    And I follow "Manage Metadata"
    And I follow "Required"
  
  @javascript
  Scenario: Ensuring that I can select items, and have the selections be saved
    Given I expand all fieldsets
    Then I set the following fields to these values:
      | general_course_email | 1 |
      | general_course_description | 1 |
      | assessment_assessmentname | 1 |
      | session_sessionlength | 1 |
    When I press "Save changes"
    And I wait to be redirected
    And I press "Cancel"
    Then the following fields match these values:
      | general_course_email | 1 |
      | general_course_description | 1 |
      | assessment_assessmentname | 1 |
      | session_sessionlength | 1 |
    