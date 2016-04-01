@local_metadata @local_metadata_university_gradatt
Feature: Admin Moodle Metadata in Site Administration
	In order to use Admin Moodle Metadata
	As an administrator
	I need to be able to delete graduate attributes
	
  Background:
    Given I create the following graduate attributes:
	  | attribute |
	  | A1 |
	  | A2 |
    And I log in as "admin"
    And I am on homepage
    And I expand "Site administration" node
    And I follow "Admin Moodle Metadata"
    And I follow "Graduate Attributes"

  @javascript
  Scenario: Deleting graduate attribute after uploading
	Given I set the field "course_gradatt[]" to "A1"
	And I press "delete_gradatt"
	Then the "course_gradatt[]" select box should not contain "A1"
	And I set the field "course_gradatt[]" to "Group"
	And I press "delete_gradatt"
	Then the "course_gradatt[]" select box should not contain "Group"
	And the "course_gradatt[]" select box should not contain "A2"
