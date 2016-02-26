@block @eclass_course_management @change_dates_eclass_course_management
Feature: Changing dates in eclass course management block should display proper prompt
  and proper course status outcome.

  Background:
    # Note: Due to some scenario directly manipulating database, do not create another course for future behat test.
    #       If really needed, create a new feature file.
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
    When I log in as "teacher1"
    And I follow "Course 1"
    And I turn editing mode on
    And I add the "Course Management" block

  @javascript
  Scenario: date > end date should display an error prompt
    And I click on "Edit" "link" in the "Course Open/Close Dates" "block"
    And I set start date fields "+3" days from today
    And I set end date fields "+2" days from today
    And I press "Save changes"
    Then I should see "Error: Start date must be before end date."

  @javascript
  Scenario: Course status closed and then start date <= current date and end date >= current_date should open course
  As a side effect of trying to close the course first, we also test the scenario in which we set the start
  and end dates for the future of an already open course, which should display: "By selecting a start date that is
  in the future your course will be automatically closed. Course will open on the selected date."

    # Ensure that both start/end day are ahead of today, thus closing the course. Note this also test
    # the scenario in which we set start and end dates for the future of an already open course. This
    # have its own distinct promt.
    And I click on "Edit" "link" in the "Course Open/Close Dates" "block"
    And I set start date fields "+2" days from today
    And I set end date fields "+4" days from today
    And I press "Save changes"
    Then I should see:
    """
    By selecting a start date that is in the future your course will be automatically closed.
    Course will open on the selected date.
    """
    Then I press "Yes"
    Then I follow "Course 1"
    Then I should see "Course Status: Closed"

    # Change start day to emulate opening course.
    Then I click on "Edit" "link" in the "Course Open/Close Dates" "block"
    And I set start date fields "-1" days from today
    Then I press "Save changes"
    Then I should see "By selecting a start date that has already passed your course will be automatically opened."
    Then I press "Yes"
    Then I follow "Course 1"
    Then I should see "Course Status: Open"

  @javascript
  Scenario: Course status close and then start day and end day is greater than today. This should display a distinct
  prompt "By selecting a start date that is in the future your course will open on the selected date."

    # Close the course first.
    And I click on "Edit" "link" in the "Course Open/Close Dates" "block"
    And I set start date fields "+2" days from today
    And I set end date fields "+4" days from today
    Then I press "Save changes"
    Then I should see:
    """
    By selecting a start date that is in the future your course will be automatically closed.
    Course will open on the selected date.
    """
    Then I press "Yes"
    Then I follow "Course 1"
    Then I should see "Course Status: Closed"

    Then I click on "Edit" "link" in the "Course Open/Close Dates" "block"
    Then I press "Save changes"
    Then I should see "By selecting a start date that is in the future your course will open on the selected date."
    Then I press "Yes"
    Then I follow "Course 1"
    Then I should see "Course Status: Closed"


  @javascript
  Scenario: Course status open and new end date <= current date should close the course.
    # Ensure that start < today < end.
    And I click on "Edit" "link" in the "Course Open/Close Dates" "block"
    And I set start date fields "-2" days from today
    And I set end date fields "+1" days from today
    Then I press "Save changes"
    Then I follow "Course 1"
    Then I should see "Course Status: Open"

    # Changing end date to be before today should trigger close.
    Then I click on "Edit" "link" in the "Course Open/Close Dates" "block"
    And I set end date fields "-1" days from today
    Then I press "Save changes"
    Then I should see "By selecting an end date that has already passed your course will be automatically closed."
    Then I press "Yes"
    Then I follow "Course 1"
    Then I should see "Course Status: Closed"

  @javascript
  Scenario: Course status closed and start date > current date makes course remain close, but open for the future.
    # Close the course.
    And I click on "Edit" "link" in the "Course Open/Close Dates" "block"
    And I set start date fields "-4" days from today
    And I set end date fields "-2" days from today
    And I press "Save changes"
    Then I should see "By selecting an end date that has already passed your course will be automatically closed."
    Then I press "Yes"
    And I follow "Course 1"
    Then I should see "Course Status: Closed"

    # Changing start date to be after today should trigger close.
    Given I click on "Edit" "link" in the "Course Open/Close Dates" "block"
    And I set start date fields "+1" days from today
    And I set end date fields "+2" days from today
    And I press "Save changes"
    Then I should see "By selecting a start date that is in the future your course will open on the selected date."
    Then I press "Yes"
    Then I follow "Course 1"
    And I should see "Course Status: Closed"

  @javascript
  Scenario: A course that is already open will stay open with no prompt when date changes are within
  start date < today < end date.
    And I click on "Edit" "link" in the "Course Open/Close Dates" "block"
    And I set start date fields "-1" days from today
    And I set end date fields "+1" days from today
    Then I press "Save changes"
    Then I follow "Course 1"
    Then I should see "Course Status: Open"

    # Should remain open without any prompt.
    Given I click on "Edit" "link" in the "Course Open/Close Dates" "block"
    And I set start date fields "-2" days from today
    And I press "Save changes"
    Then I follow "Course 1"
    And I should see "Course Status: Open"