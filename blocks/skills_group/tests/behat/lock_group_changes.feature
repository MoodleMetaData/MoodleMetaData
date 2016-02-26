@block @block_skills_group @eclass-blocks-skills_group
Feature: Lock group changes after a specified date
  In order to lock group changes
  As a instructor
  I need to enter a lock date on the settings form

  Background:
    Given the following "courses" exist:
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
    And I log in as "teacher1"
    And I follow "Course 1"
    And I turn editing mode on
    And I add the "Group Sign-up" block
    And I click on "Edit skills group settings" "link"

  Scenario: Expired date on create/join group
    And I set the following fields to these values:
      | feedbacks | None |
      | groupings | Grouping 1 |
      | allownaming | 1 |
      | maxsize | 6 |
      | datecheck | 1 |
      | date[day] | 10 |
      | date[month] | 11 |
      | date[year] | 2015 |
    And I press "Save changes"
    And I log out
    And I log in as "student1"
    And I follow "Course 1"
    Then I should see "Group creation expired"

  Scenario: Expired date after joining a group
    And I set the following fields to these values:
      | feedbacks | None |
      | groupings | Grouping 1 |
      | allownaming | 1 |
      | maxsize | 6 |
      | datecheck | 0 |
    And I press "Save changes"
    And I log out
    And I log in as "student1"
    And I follow "Course 1"
    And I click on "Create/Edit a group" "link"
    Given I set the following fields to these values:
      | creategroupcheck | 1 |
      | creategroup | Test Group |
    When I click on "#id_submitbutton" "css_element"
    Then I should see "Add Users to Group"
    When I log out
    And I log in as "teacher1"
    And I follow "Course 1"
    And I click on "Edit skills group settings" "link"
    And I set the following fields to these values:
      | feedbacks | None |
      | groupings | Grouping 1 |
      | allownaming | 1 |
      | maxsize | 6 |
      | datecheck | 1 |
      | date[day] | 10 |
      | date[month] | 11 |
      | date[year] | 2015 |
    And I press "Save changes"
    And I log out
    And I log in as "student1"
    And I follow "Course 1"
    Then I should see "Group creation expired"