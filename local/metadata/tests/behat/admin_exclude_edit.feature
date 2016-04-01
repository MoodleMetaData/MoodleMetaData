@local_metadata  @local_metadata_admin @local_metadata_admin_exclude
Feature: Administrator exclude tab
	In order to be able to exclude items from the syllabus
	As an administrator
	I need to be able to check items I want to exclude
	
  Background:
    Given I log in as "admin"
    And I am on homepage
    And I expand "Site administration" node
    And I follow "Manage Metadata"
    And I follow "Syllabus Configuration"
    
  @javascript
  Scenario: Checking some items, and ensuring they remain checked
    Given I set the following fields to these values:
        | Course_Description | 1 |
        | Grading | 1 |
        | Policy | 1 |
    When I press "Save changes"
    And I wait to be redirected
    And I press "Cancel"
    Then the following fields match these values:
        | Course_Description | 1 |
        | Course_Readings | 0 |
        | Course_Objectives | 0 |
        | Grading | 1 |
        | Course_Sessions | 0 |
        | Policy | 1 |