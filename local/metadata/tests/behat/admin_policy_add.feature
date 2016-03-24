@local_metadata @local_metadata_admin
Feature: Administrator policy tab
	In order to use the policy tab
	As an administrator
	I need to be able to enter information about faculty policy
	
  Background:
    Given I log in as "admin"
    And I am on homepage
    And I expand "Site administration" node
    And I follow "Manage Metadata"
    
  @javascript
  Scenario: Filling out the university policy.
  	Given I set the following fields to these values:
  	| policy_editor | Some policy |
  	When I press "Submit"
  	And I wait to be redirected
  	Then the following fields match these values:
  	| policy_editor | Some policy |