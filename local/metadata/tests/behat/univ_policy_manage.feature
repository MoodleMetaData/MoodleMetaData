@local_metadata @local_metadata_university @local_metadata_university_policy
Feature: Admin Moodle Metadata in Site Administration
	In order to use Admin Moodle Metadata
	As an administrator
	I need to be able to enter and edit university policy
	
  Background:
    Given I log in as "admin"
    And I am on homepage
	And I expand "Site administration" node
    Then I follow "Moodle Metadata - University Policy"

  @javascript
  Scenario: Filling out university policy.
	Given I set the field "university_editor[text]" to "This is a new university policy"
	When I press "Submit"
	And I wait to be redirected
	Then I should see "This is a new university policy"

  @javascript
  Scenario: Editing university policy.
	Given the faculty and university policy exist
	And I set the field "university_editor[text]" to "I edit this"
	When I press "Submit"
	And I wait to be redirected
	Then I should see "I edit this"