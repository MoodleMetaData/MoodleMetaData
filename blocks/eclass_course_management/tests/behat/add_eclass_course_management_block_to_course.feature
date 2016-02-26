@block @eclass_course_management @add_eclass_course_management_block_to_course
Feature: Course management block in a course

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C101      | 0        |
    And the following "users" exist:
      | username    | firstname | lastname | email            |
      | student1    | Garry   | TheRandomStudent  | student1@asd.com |
      | teacher1    | Bob     | TheTeacherPC  | teacher1@asd.com |
      | teacher2    | Boby     | TheTeacherPC2  | teacher2@asd.com |
      | manager    | Man     | TheMan  | man@asd.com |
    And the following "course enrolments" exist:
      | user        | course | role    |
      | student1    | C101   | student |
      | teacher1    | C101   | editingteacher |
      | teacher2    | C101   | teacher |
      | manager    | C101   | manager |
    Given the following eclass_course_management values exist:
      | id | startdate   | enddate |
      | 2  | 1414595336  | 1416595336  |
    And I log in as "teacher1"
    And I follow "Course 1"
    And I turn editing mode on
    And I add the "Course Management" block
    And I log out

  Scenario: EditingTeacher can view the course management block
    When I log in as "teacher1"
    And I follow "Course 1"
    Then ".block_eclass_course_management" "css_element" should exist

  Scenario: Teacher can view the course management block
    When I log in as "teacher2"
    And I follow "Course 1"
    Then ".block_eclass_course_management" "css_element" should exist

  Scenario: Manager can view the course management block
    When I log in as "manager"
    And I follow "Course 1"
    Then ".block_eclass_course_management" "css_element" should exist

  Scenario: Student can not view the course management block
    When I log in as "student1"
    And I follow "Course 1"
    Then ".block_eclass_course_management" "css_element" should not exist

  Scenario: EditingTeacher can see course visibility status in course management block
    When I log in as "teacher1"
    And I follow "Course 1"
    When I click on "Edit settings" "link" in the "Administration" "block"
    And I set the following fields to these values:
      | Visible | Show |
    And I press "Save changes"
    And I should see "Course Open/Close Dates"
    Then I should see "Course Status: Open"
    When I click on "Edit settings" "link" in the "Administration" "block"
    And I set the following fields to these values:
      | Visible | Hide |
    And I press "Save changes"
    And I should see "Course Open/Close Dates"
    Then I should see "Course Status: Closed"

  Scenario: EditingTeacher can edit the course management block
    When I log in as "teacher1"
    And I follow "Course 1"
    When I click on "Edit settings" "link" in the "Administration" "block"
    And I set the following fields to these values:
      | Visible | Show |
    And I press "Save changes"
    And I turn editing mode on
    And I open the "Course Open/Close Dates" blocks action menu

    Then I click on "Edit" "link" in the "Course Open/Close Dates" "block"
    Then I should see "Course Status"
    Then I should see "Open:"
    Then I should see "Close:"
    And I press "Save changes"