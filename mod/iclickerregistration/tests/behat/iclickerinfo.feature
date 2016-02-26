@mod @mod_iclickerregistration @mod_iclickerregistration_iclickerinfo
Feature: iClickerInfo page feature.
  Almost all user (except guests) can view this page.
  Note: This view, also an angular js directive, is aggregated by two other views/controllers.
        Thus testing this, tests a significate component of admin-view and teacher-view.

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email   | idnumber |
      | student1 | Student | 1 | student1@asd.com | student1 |
      | student3 | Student | 1 | student1@asd.com | student3 |
      | teacher1 | Teacher | 1 | teacher1@asd.com | teacher1 |
    # User without idnumber.
    Given the following "users" exist:
      | username | firstname | lastname | email   |
      | student2 | Student | 2 | student1@asd.com |
    And the following "courses" exist:
      | fullname | shortname | category | summary     |
      | Course 1 | C1        | 0        | Short desc. |
      | Course 2 | C2        | 0        | Short desc. |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
      | student1 | C1 | student |
      | student2 | C1 | student |
      | student3 | C2 | student |
    And the following "activities" exist:
      | activity             | name                  | intro | course | idnumber |
      | iclickerregistration | iClicker Registration | n     | C1     | data1    |
      | iclickerregistration | iClicker Registration | n     | C2     | data2    |

  @javascript
  Scenario: Login as student and ensure that we get iclickerinfo.
    Given I log in as "student1"
    And I as student register iclicker id "11A4C277" at course "Course 1" and activity "iClicker Registration"

    # I should be in iclicker-info page right now.
    And I should see "11A4C277"

    # Since the situation is normal, there should be nothing funny
    # going on. The module's main div should be white, no alerts of any sort.
    And "div#mod-iclickerregistration-iclicker-info" "css_element" should be visible
    And "div#mod-iclickerregistration-iclicker-info.alert.alert-danger" "css_element" should not exist

  @javascript
  Scenario: Login as student without an idnumber should should display a message
    to the user.
    And I log in as "student2"
    And I follow "Course 1"
    And I follow "iClicker Registration"

    Then I should see "iclickerregistration/youraccountismanuallyenrolled" lang string

    # This is an urgent situation since the student currently viewing might not
    # have an idnumber/ccid, thus might not be enrolled for some reason. Thus
    # to convey the danger, alert.alert-danger classes should be placed for
    # bootstrap's red background.
    And "div#mod-iclickerregistration-iclicker-info.alert.alert-danger" "css_element" should be visible

  @javascript
  Scenario: Have same iclicker registered in same course1. Ensure that we
    get an warning alert (not a danger alert like above).
    # Register first user.
    Given I log in as "student1"
    And I as student register iclicker id "11A4C277" at course "Course 1" and activity "iClicker Registration"
    And I should see "11A4C277"
    And I log out
    And I am on homepage

    # To register second user, he/she can't just be in same course
    # since this plugin won't allow such registration. A way around
    # to test this is for the second user to be not registered in this
    # course, but registered on the other course C2. Then after registering
    # his/her iclicker in C2, the user then registers to C1.
    Given I log in as "student3"
    And I as student register iclicker id "11A4C277" at course "Course 2" and activity "iClicker Registration"
    # I should be in iclicker-info page right now.
    And I should see "11A4C277"

    And the following "course enrolments" exist:
      | user | course | role |
      | student3 | C1 | student |
    And I am on homepage
    And I follow "Course 1"
    And I follow "iClicker Registration"

    # I should see a warning both in text and color.
    And I should see "iclickerregistration/duplicateiclickeridinsamecourse" lang string
    And "div#mod-iclickerregistration-iclicker-info.alert.alert-danger" "css_element" should be visible