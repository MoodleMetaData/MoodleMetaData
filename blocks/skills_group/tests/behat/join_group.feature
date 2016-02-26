@block @block_skills_group @eclass-blocks-skills_group
Feature: Join a group
  In order to join a group
  As a student
  I need to go select a valid group on the join page

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
      | Team Genius | C1 | G2 |
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
      | GROUPING1 | G2 |
    And the following "group members" exist:
      | user | group |
      | student1 | G1 |
      | student2 | G1 |
      | student3 | G2 |
      | student4 | G2 |
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
    And I log in as "student1"
    And I follow "Course 1"
    And I click on "Create/Edit a group" "link"
    And I set the following fields to these values:
      | allowjoincheck | 1 |
    And I click on "#id_submitbutton" "css_element"
    And I log out

  @javascript
  Scenario: Join a group
    And I log in as "student5"
    And I follow "Course 1"
    And I click on "Join existing group" "link"
    Then I should see "Team Awesome"
    And I should not see "Team Genius"
    When I click on "//td[text()='Team Awesome']" "xpath_element"
    And I click on "#id_submitbutton" "css_element"
    And I wait "3" seconds
    Then I should see "Successfully joined group." in the "#statustext" "css_element"
    And the "disabled" attribute of "#id_submitbutton" "css_element" should contain "true"

  @javascript
  Scenario: Multiple groups to choose
    And I log in as "student3"
    And I follow "Course 1"
    And I click on "Create/Edit a group" "link"
    And I set the following fields to these values:
      | allowjoincheck | 1 |
    And I click on "#id_submitbutton" "css_element"
    And I log out
    And I log in as "student5"
    And I follow "Course 1"
    And I click on "Join existing group" "link"
    Then I should see "Team Awesome"
    And I should see "Team Genius"
