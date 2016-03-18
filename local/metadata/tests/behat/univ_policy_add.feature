@local_metadata
Feature: University policy tab
	In order to use the policy tab
	As an administrator
	I need to be able to enter information about a university policy
	
  Background:
    I log in as "admin"
    And I am on homepage
    And I follow "Site administration"
    And I follow "Admin Moodle Metadata"
    
  Scenario: Filling out the university policy.
  	Given I set the following fields to these values:
  	| university_editor | Some policy |
  	When I press "Submit"
  	And I wait to be redirected
  	Then the following fields match these values:
  	| university_editor | Some policy |