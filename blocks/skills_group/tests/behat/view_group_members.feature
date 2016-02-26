@block @block_skills_group @eclass-blocks-skills_group
Feature: View group members
  In order to view my group members
  As a student
  I need to go join a group and then go to the members page

  Background:
    Given the following "courses" exist:
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
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
      | student1 | C1 | student |
      | student2 | C1 | student |
      | student3 | C1 | student |
    And the following "grouping groups" exist:
      | grouping | group |
      | GROUPING1 | G1 |
    And the following "group members" exist:
      | user | group |
      | student1 | G1 |
      | student2 | G1 |
    And I log in as "teacher1"
    And I follow "Course 1"
    And I turn editing mode on
    And I add the "Group Sign-up" block
    And I click on "Edit skills group settings" "link"
    And I set the following fields to these values:
      | feedbacks | None |
      | groupings | Grouping 1 |
      | allownaming | 1 |
      | maxsize | 6 |
    And I press "Save changes"
    And I log out
    And I log in as "student1"
    And I follow "Course 1"
    And I click on "Create/Edit a group" "link"
    And I set the following fields to these values:
      | allowjoincheck | 1 |
    And I click on "#id_submitbutton" "css_element"
    And I log out

  @javascript
  Scenario: View group members
    And I log in as "student3"
    And I follow "Course 1"
    And I click on "Join existing group" "link"
    When I click on "//td[text()='Team Awesome']" "xpath_element"
    And I click on "#id_submitbutton" "css_element"
    And I wait "3" seconds
    And I follow "Course 1"
    And I click on "View group" "link"
    Then I should see "Test Student1"
    And I should see "Test Student2"
    And I should see "Test Student3"