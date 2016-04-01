@local_metadata  @local_metadata_admin @local_metadata_admin_categories
Feature: Administrator exclude tab
	In order to remove course categories
	As an administrator
	I need to be able to delete a group of course categories
	
  Background:
    Given I create the following course categories for faculty "Miscellaneous":
	  | categoryname |
	  | C1 |
	  | C2 |
    And I log in as "admin"
    And I am on homepage
    And I expand "Category administration" node
	And I follow "My categories"
	And I follow "Miscellaneous"
    And I follow "Manage Metadata"
    And I follow "Categories"

  @javascript
  Scenario: Adding and Deleting a group of course categories after uploading
	Given I set the field "course_category[]" to "Group"
	And I press "delete_category"
	Then the "course_category[]" select box should not contain "Group"
