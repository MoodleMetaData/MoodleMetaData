@block @block_skills_group @eclass-blocks-skills_group
Feature: Create a group
  In order to create group
  As a admin
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
      | allownaming | 1 |
      | maxsize | 6 |
    And I press "Save changes"
    And I log out

  @javascript
  Scenario: Create a group
    And I log in as "student1"
    And I follow "Course 1"
    And I click on "Create/Edit a group" "link"
    Then I should see "None"
    Given I set the following fields to these values:
      | creategroupcheck | 1 |
      | creategroup | Test Group |
    When I click on "#id_submitbutton" "css_element"
    Then I should see "Add Users to Group"
    And the field "allowuserstojoin" matches value ""

  @javascript
  Scenario: Create a group with blank name
    And I log in as "student1"
    And I follow "Course 1"
    And I click on "Create/Edit a group" "link"
    Then I should see "None"
    Given I set the following fields to these values:
      | creategroupcheck | 1 |
    When I click on "#id_submitbutton" "css_element"
    And I follow "Course 1"
    And I click on "Create/Edit a group" "link"
    Then I should see "Team 01"

  @javascript
  Scenario: Allow others to join a group
    And I log in as "student1"
    And I follow "Course 1"
    And I click on "Create/Edit a group" "link"
    Then I should see "None"
    Given I set the following fields to these values:
      | creategroupcheck | 1 |
      | creategroup | Test Group |
      | allowjoincheck | 1 |
    When I click on "#id_submitbutton" "css_element"
    Then I should see "Add Users to Group"
    And the field "allowuserstojoin" matches value "1"
