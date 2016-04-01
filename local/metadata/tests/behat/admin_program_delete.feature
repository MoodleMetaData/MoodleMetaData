@local_metadata  @local_metadata_admin @local_metadata_admin_program
Feature: Administrator program objectvies tab
	In order to use the objectives tab
	As an administrator
	I need to be able to delete information about program objectives
	
  Background:
    Given the default program objectives exist
	And I log in as "admin"
    And I am on homepage
    And I expand "Site administration" node
    And I follow "Manage Metadata"
	
  @javascript
  Scenario: Deleting a group of program objectives.
  Given the "manage_groups[]" select box should contain "Group"
  And I set the field "manage_groups[]" to "Group"
  And I press "delete_groups"
  Then the "manage_groups[]" select box should not contain "Group"