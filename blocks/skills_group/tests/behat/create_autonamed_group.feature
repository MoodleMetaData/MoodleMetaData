@block @block_skills_group @eclass-blocks-skills_group
Feature: Create an auto-named group
  In order to create an auto-named group
  As a student
  I need to go to the my course page and create a group

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
      | Team 01 | C1 | T1 |
      | Team 02 | C1 | T2 |
      | Team 03 | C1 | T3 |
    And the following "grouping groups" exist:
      | grouping | group |
      | GROUPING1 | T1 |
      | GROUPING1 | T2 |
      | GROUPING1 | T3 |
    And the following "activities" exist:
      | activity | name | intro | course | idnumber | anonymous |
      | feedback | Feedback 1 | Test feedback description | C1 | feedback1 | 2 |
    And the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Test | Teacher | teacher1@ualberta.ca |
      | student1 | Test | Student | student1@ualberta.ca |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
      | student1 | C1 | student |
    And I log out
    And I log in as "teacher1"
    And I follow "Course 1"
    And I turn editing mode on
    And I add the "Group Sign-up" block
    And I click on "Edit skills group settings" "link"
    And I set the following fields to these values:
      | feedbacks | None |
      | groupings | Grouping 1 |
      | allownaming | 0 |
      | maxsize | 6 |
    And I press "Save changes"
    And I log out

  @javascript
  Scenario: Create an auto-named group
    And I log in as "student1"
    And I follow "Course 1"
    And I click on "Create/Edit a group" "link"
    Then I should see "None"
    Given I set the following fields to these values:
      | creategroupcheck | 1 |
    When I click on "#id_submitbutton" "css_element"
    And I follow "Course 1"
    And I click on "Create/Edit a group" "link"
    Then I should see "Team 04"
