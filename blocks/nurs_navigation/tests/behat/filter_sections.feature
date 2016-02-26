@block @block_nurs_navigation @eclass-blocks-nurs-navigation
Feature: Filter administrative sections from students
  In order to filter administrative sections
  As an instructor
  I need to set one of my sections to be one of the filtered strings

  Background:
    Given the following "courses" exists:
      | fullname | shortname | category |
      | Course 1 | C1 | 0 |
    And the following "users" exists:
      | username | firstname | lastname | email |
      | teacher1 | Test | Teacher | cjamieso@ualberta.ca |
      | student1 | Test | Student | cjamieso@gmx.ualberta.ca |
    And the following "course enrolments" exists:
      | user | course | role |
      | admin    | C1 | editingteacher |
      | teacher1 | C1 | editingteacher |
      | student1 | C1 | student |
    And I log in as "admin"
    And I follow "Course 1"
    And I turn editing mode on
    And I add the "Course Sections" block
    And I rename section "5" to "Tutor Resources"

  Scenario: View administrative section as teacher
    Then I should see "Tutor Resources" in the "Course Sections" "block"

  Scenario: View administrative section as a student
    When I log out
    And I log in as "student1"
    And I follow "Course 1"
    Then I should not see "Tutor Resources" in the "Course Sections" "block"