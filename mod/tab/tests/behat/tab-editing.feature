@mod @mod_tab @mod_tab_editing
Feature: Tab editing, as a editing teacher, I should be able to edit. As a student, no.
  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email   | idnumber |
      | teacher1 | Teacher | 1 | teacher1@asd.com | teacher1 |
      | teacher2 | Teacher | 2 | teacher2@asd.com | teacher2 |
      | student1 | Student | 1 | student1@asd.com | student1 |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1 | 0 |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
      | teacher2 | C1 | teacher        |
      | student1 | C1 | student        |

    Given I log in as "admin"
    And I am on homepage
    And I follow "Course 1"
    And I turn editing mode on
    And I add a "tab" to section "1" and I fill the form with:
      | Name        | SingleTabGroupTab    |
    Then I log out

  @javascript
  Scenario: Student access in single tab group.
    Given I log in as "student1"

    And I am on homepage
    And I follow "Course 1"
    And I follow "SingleTabGroupTab"
    And I wait until the page is ready

    Then "//*[@id='mod_tab_update_this']" "xpath_element" should not be visible

  @javascript
  Scenario: Editing Teacher accessing single tab group.
    Given I log in as "teacher1"

    And I am on homepage
    And I follow "Course 1"
    And I follow "SingleTabGroupTab"
    And I wait until the page is ready

    Then "//*[@id='mod_tab_update_this']" "xpath_element" should be visible