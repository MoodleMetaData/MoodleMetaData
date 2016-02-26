@mod @mod_iclickerregistration @mod_iclickerregistration_teacherviewiclickerinfo
Feature: teacher-view-iclicker-info page feature.
  Almost all user (except guests) can view this page.

  Background: Login, open editor, and show more buttons
    Given the following "users" exist:
      | username | firstname | lastname | email   | idnumber |
      | teacher1 | Teacher | 1 | teacher1@asd.com | teacher1|
    And the following "courses" exist:
      | fullname | shortname | category | summary |
      | Course 1 | C1 | 0 | short desc            |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
    And the following "activities" exist:
      | activity             | name                  | intro | course | idnumber |
      | iclickerregistration | iClicker Registration | n     | C1     | data1    |

  @javascript
  Scenario: Login as teacher and ensure that we get to teacher-view-iclicker-info
    Given I log in as "teacher1"
    And I as non-student register iclicker id "11A4C277" at course "Course 1" and activity "iClicker Registration"

    # I should be in teacher-view-iclicker-info page right now.
    And I should see "11A4C277"