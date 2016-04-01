@local_metadata @local_metadata_assessment
Feature: Assessment tab header
	In order to use the assessment tab
	As an instructor
	I want a useful header to be displayed for each assessment
  
  Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C_shortname | 0 |
    And I log in as "admin"
    And I am on homepage
    And I follow "Course 1"
    And I follow "Instructor Moodle Metadata"
    And I follow "Assessment"
  
  @javascript
  Scenario: Ensure that a new assessment has New Assessment, unnamned has Unnamed Assessment, and named displays its own name
    Given I create 1 assessments
    Then I should see "New Assessment"
    Given I set the field "assessmentname[0]" to ""
    When I press "Save changes"
    And I wait to be redirected
    Then I should see "Unnamed Assessment"
    And I should not see "New Assessment"
    Given I set the field "assessmentname[0]" to "Assessment title"
    When I press "Save changes"
    And I wait to be redirected
    Then I should see "Assessment title"
    And I should not see "New Assessment"
    And I should not see "Unnamed Assessment"