@local_metadata  @local_metadata_admin
Feature: Administrator program objectvies tab
	In order to use the objectives tab
	As an administrator
	I need to be able to enter and delete information about program objectives
	
  Background:
    Given I log in as "admin"
    And I am on homepage
    And I expand "Site administration" node
    And I follow "Manage Metadata"
    And I expand all fieldsets
    
  @javascript
  Scenario: Filling out knowledge objective
  	And I set the field "new_knowledge" to "K1"
  	And I press "create_knowledge"
  	And I wait to be redirected
    And I expand all fieldsets
    Then the "manage_knowledge[]" select box should contain "K1"
    Given I set the field "manage_knowledge[]" to "K1"
  	And I press "delete_knowledge"
  	And I wait to be redirected
    And I expand all fieldsets
    Then the "manage_knowledge[]" select box should not contain "K1"
  	
  @javascript
  Scenario: Filling out skils objective
  	And I set the field "new_skills" to "S1"
  	And I press "create_skills"
  	And I wait to be redirected
    And I expand all fieldsets
    Then the "manage_skills[]" select box should contain "S1"
    Given I set the field "manage_skills[]" to "S1"
  	And I press "delete_skills"
  	And I wait to be redirected
    And I expand all fieldsets
    Then the "manage_skills[]" select box should not contain "S1"
  	
  @javascript
  Scenario: Filling out attitudes objective
  	And I set the field "new_attitudes" to "A1"
  	And I press "create_attitudes"
  	And I wait to be redirected
    And I expand all fieldsets
    Then the "manage_attitudes[]" select box should contain "A1"
    Given I set the field "manage_attitudes[]" to "A1"
  	And I press "delete_attitudes"
  	And I wait to be redirected
    And I expand all fieldsets
    Then the "manage_attitudes[]" select box should not contain "A1"