@block @block_skills_group @eclass-blocks-skills_group
Feature: Finalize group choice
  In order to finalize my group choice
  As a student
  I need to go be in a group and fill out the consent

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
      | student1 | Test | Student | student1@ualberta.ca |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
      | student1 | C1 | student |
    And the following "group members" exist:
      | user | group |
      | student1 | G1 |
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
  Scenario: Finalize group choice
    And I log in as "student1"
    And I follow "Course 1"
    And I click on "Lock my group choice" "link"
    Then I should see "Team Awesome"
    And I set the following fields to these values:
      | lockchoice | 1 |
    When I click on "#id_submitbutton" "css_element"
    And I click on "Lock my group choice" "link"
    Then I should see "You have already locked in your group selection."