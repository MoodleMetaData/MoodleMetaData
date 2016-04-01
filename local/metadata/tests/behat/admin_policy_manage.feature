@local_metadata @local_metadata_admin @local_metadata_admin_policy
Feature: Administrator policy tab
	In order to use the policy tab
	As an administrator
	I need to be able to enter and edit information about faculty policy
	
  Background:
    Given I log in as "admin"
    And I am on homepage
    And I expand "Category administration" node
	And I follow "My categories"
	And I follow "Miscellaneous"
    And I follow "Manage Metadata"
	And I follow "Policy"
    
  @javascript
  Scenario: Filling out the faculty policy.
  	Given I set the following fields to these values:
  	| policy_editor[text] | Some policy |
  	When I press "Submit"
  	And I wait to be redirected
  	Then the following fields match these values:
  	| policy_editor[text] | Some policy |
	
  @javascript
  Scenario: Editing the current faculty policy.
  	Given the faculty and university policy exist
  	And I set the following fields to these values:
  	| policy_editor[text] | New policy |
	When I press "Submit"
  	And I wait to be redirected
  	Then the following fields match these values:
  	| policy_editor[text] | New policy |