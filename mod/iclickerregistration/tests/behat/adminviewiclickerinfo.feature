@mod @mod_iclickerregistration @mod_iclickerregistration_adminviewiclickerinfo
Feature: teacher-view-iclicker-info page feature.
  Almost all user (except guests) can view this page.

  Background: Login, open editor, and show more buttons
    Given the following "users" exist:
      | username | firstname | lastname | email   | idnumber |
      | teacher1 | Teacher | 1 | teacher1@asd.com | teacher1 |
    And the following "courses" exist:
      | fullname | shortname | category | summary     |
      | Course 1 | C1        | 0        | Short desc. |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
    And the following "activities" exist:
      | activity             | name                  | intro | course | idnumber | id |
      | iclickerregistration | iClicker Registration | n     | C1     | data1    | 666666  |

  @javascript
  Scenario: Login as admin and ensure that we get to admin-view-iclicker-info
    Given I log in as "admin"
    Given I add idnumber "adminjoe" to current user

    And I follow "Course 1"
    And I follow "iClicker Registration"

    # Not registered, and admin-view-iclicker-info won't redirect.
    # The thinking is, admin's primary purpose is not to register iclicker,
    # unlike the site's user.
    And I should see "No iClicker ID Registered"
    And I as non-student register iclicker id "11A4C277" at course "Course 1" and activity "iClicker Registration"

    # I should be in admin-view-iclicker-info page right now.
    And I should see "11A4C277"

  @javascript
  Scenario: Login as teacher and ensure that we get access-denied.
    Given I log in as "teacher1"
    And I am on "mod/iclickerregistration/id=666666#/admin-view-iclicker-info"