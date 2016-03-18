@local_metadata
Feature: Administrator program objectvies tab
	In order to use the objectives tab
	As an administrator
	I need to be able to enter and delete information about program objectives
	
  Background:
    I log in as "admin"
    And I am on homepage
    And I follow "Site administration"
    And I follow "Manage Metadata"
    
  Scenario: Filling out knowledge objective
  	I expand "Program Objective: Knowledge" node
  	And I set the field "new_knowledge" to "K1"
  	And I press "Add"
  	And I wait to be redirected
  	Then the following fields match these values:
  	| manage_knowledge[0] | K1 |
  	Then I press "manage_knowledge[0]"
  	And I press "Delete"
  	And I wait to be redirected
  	Then "manage_knowledge[0]" should not exist
  	
  Scenario: Filling out skils objective
  	I expand "Program Objective: Skills" node
  	And I set the field "new_skills" to "S1"
  	And I press "Add"
  	And I wait to be redirected
  	Then the following fields match these values:
  	| manage_skills[0] | S1 |
  	Then I press "manage_skills[0]"
  	And I press "Delete"
  	And I wait to be redirected
  	Then "manage_skills[0]" should not exist
  	
  Scenario: Filling out attitudes objective
  	I expand "Program Objective: Attitudes" node
  	And I set the field "new_attitudes" to "A1"
  	And I press "Add"
  	And I wait to be redirected
  	Then the following fields match these values:
  	| manage_attitudes[0] | K1 |
  	Then I press "manage_attitudes[0]"
  	And I press "Delete"
  	And I wait to be redirected
  	Then "manage_attitudes[0]" should not exist