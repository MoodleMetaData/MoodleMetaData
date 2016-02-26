@block @block_skills_group @eclass-blocks-skills_group
Feature: Edit a group
  In order to edit a group's members
  As a student
  I need to go add/remove members on the edit page

  Background:
    Given the following config values are set as admin:
      | enableavailability | 1 |
    And I log in as "admin"
    And I expand "Site administration" node
    # The wait is important to let the site admin node expand: seems like a bug, moodle code should do it.
    And I wait "10" seconds
    And I navigate to "Manage activities" node in "Site administration > Plugins > Activity modules"
    And I click on "//a[@title=\"Show\"]" "xpath_element" in the "Feedback" "table_row"
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1 | 0 |
    And the following "groupings" exist:
      | name       | course  | idnumber  |
      | Grouping 1 | C1 | GROUPING1 |
    And the following "groups" exist:
      | name | course | idnumber |
      | Team Awesome | C1 | G1 |
    And the following "activities" exist:
      | activity | name | intro | course | idnumber | anonymous |
      | feedback | Feedback 1 | Test feedback description | C1 | feedback1 | 2 |
    And the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Test | Teacher | teacher1@ualberta.ca |
      | student1 | Test | Student1 | student1@ualberta.ca |
      | student2 | Test | Student2 | student2@ualberta.ca |
      | student3 | Test | Student3 | student3@ualberta.ca |
      | student4 | Test | Student4 | student4@ualberta.ca |
      | student5 | Test | Student5 | student5@ualberta.ca |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
      | student1 | C1 | student |
      | student2 | C1 | student |
      | student3 | C1 | student |
      | student4 | C1 | student |
      | student5 | C1 | student |
    And the following "grouping groups" exist:
      | grouping | group |
      | GROUPING1 | G1 |
    And I log out
    And I log in as "teacher1"
    And I follow "Course 1"
    And I turn editing mode on
    And I add the "Group Sign-up" block
    And I click on "Edit skills group settings" "link"
    And I set the following fields to these values:
      | feedbacks | Feedback 1 |
      | groupings | Grouping 1 |
      | allownaming | 1 |
      | maxsize | 6 |
    And I press "Save changes"
    And I log out

  @javascript
  Scenario: Add members to group
    And the following "group members" exist:
      | user | group |
      | student1 | G1 |
    And I log in as "student1"
    And I follow "Course 1"
    And I click on "Create/Edit a group" "link"
    And I set the following fields to these values:
      | editmembers | 1 |
    When I click on "#id_submitbutton" "css_element"
    # This tries empty submit (valid) -> adds only user to group.
    And I click on "#id_submitbutton" "css_element"
    And I wait "3" seconds
    Then I should see "Group successfully updated."
    And "student1" should be in group "Team Awesome"
    When I add "Test Student2" to my group
    And I click on "li.yui3-multivalueinput-listitem" "css_element"
    And I add "Test Student3" to my group
    And I click on "#id_submitbutton" "css_element"
    And I wait "3" seconds
    Then I should see "Group successfully updated."
    And "student1" should be in group "Team Awesome"
    And "student2" should be in group "Team Awesome"
    And "student3" should be in group "Team Awesome"

  @javascript
  Scenario: Remove members from group
    And the following "group members" exist:
      | user | group |
      | student1 | G1 |
      | student2 | G1 |
      | student3 | G1 |
      | student4 | G1 |
    And I log in as "student1"
    And I follow "Course 1"
    And I click on "Create/Edit a group" "link"
    And I set the following fields to these values:
      | editmembers | 1 |
    When I click on "#id_submitbutton" "css_element"
    And I remove "Test Student2" from my group
    And I click on "li.yui3-multivalueinput-listitem" "css_element"
    And I remove "Test Student3" from my group
    And I click on "#id_submitbutton" "css_element"
    And I wait "3" seconds
    Then I should see "Group successfully updated."
    And "student2" should not be in group "Team Awesome"
    And "student3" should not be in group "Team Awesome"

  @javascript
  Scenario: See locked students
    And the following "group members" exist:
      | user | group |
      | student1 | G1 |
      | student2 | G1 |
    And I log in as "student2"
    And I follow "Course 1"
    And I click on "Lock my group choice" "link"
    Then I should see "Team Awesome"
    When I set the following fields to these values:
      | lockchoice | 1 |
    When I click on "#id_submitbutton" "css_element"
    And I log out
    And I log in as "student1"
    And I follow "Course 1"
    And I click on "Create/Edit a group" "link"
    And I set the following fields to these values:
      | editmembers | 1 |
    And I click on "#id_submitbutton" "css_element"
    Then I should see "Locked members:" in the ".locked_members_bar" "css_element"
    Then I should see "Test Student2" in the ".locked_members_bar" "css_element"