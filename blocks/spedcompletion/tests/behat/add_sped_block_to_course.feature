@block @spedcompletion
Feature: SPED completion block used in a course
  In order to help particpants know when they are
  Done the SPED course
  As a teacher
  I can add the course summary block to a course page

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C101      | 0        |
    And the following "users" exist:
      | username    | firstname | lastname | email            |
      | student1    | Garry   | TheRandomStudent  | student1@asd.com |
      | teacher1    | Bob     | TheTeacherPC  | teacher1@asd.com |
    And the following "course enrolments" exist:
      | user        | course | role    |
      | student1    | C101   | student |
      | teacher1    | C101   | editingteacher |
    And I log in as "admin"
    And I set the following administration settings values:
      | Enable completion tracking | 1 |
    And I log out
    And I log in as "teacher1"
    And I follow "Course 1"
    And I turn editing mode on
    And I click on "Edit settings" "link" in the "Administration" "block"
    And I fill the moodle form with:
      | Enable completion tracking | Yes |
    And I press "Save changes"
    And I add a "Assignment" to section "1" and I fill the form with:
      | Assignment name | test assignment |
      | Description | test description |
      | Completion tracking | Show activity as complete when conditions are met |
      | Require view | 1 |
    And I click on "Course completion" "link" in the "Administration" "block"
    And I fill the moodle form with:
      | Assign - test assignment | 1 |
    And I press "Save changes"
    And I add the "SPED course completion status" block
    And I log out

  Scenario: Student can view the SPED course completion block
    When I log in as "student1"
    And I follow "Course 1"
    Then "SPED course completion status" "block" should exist

  Scenario: Teacher can view the SPED course completion block
    When I log in as "teacher1"
    And I follow "Course 1"
    Then "SPED course completion status" "block" should exist
