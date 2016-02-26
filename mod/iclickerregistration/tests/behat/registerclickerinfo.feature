@mod @mod_iclickerregistration @mod_iclickerregistration_registericlickerinfo
Feature: RegisteriClickerInfo page feature.
   Background: Create students, courses, and register iclicker id.
      Given the following "users" exist:
         | username | firstname | lastname | email   | idnumber |
         | student1 | Student | 1 | student1@asd.com | student1|
         | student3 | Student | 1 | student1@asd.com | student3|
         | teacher1 | Teacher | 1 | teacher1@asd.com | teacher1|
    # User without idnumber.
      Given the following "users" exist:
         | username | firstname | lastname | email   |
         | student2 | Student | 2 | student1@asd.com |
      And the following "courses" exist:
         | fullname | shortname | category | summary |
         | Course 1 | C1 | 0 | short desc            |
         | Course 2 | C2 | 0 | short desc            |
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
   Scenario: student1, knowing, he/she is the only one who registered
      an iclicker id, don't have to worry about duplicate error.
      Given I log in as "student1"
      And I follow "Course 1"
      And I follow "iClicker Registration"

      # We get redirected to edit.
      And I wait to be redirected

      # Now, lets type a correct iclicker id, there should be no error.
      And I set the field "clicker-id" to "11A4C277"
      Then I should not see "iclickerregistration/invalidiclickerid" lang string
      And  "#mod-iclickerregistration-validation-info-box li.list-group-item-danger" "css_element" should not exist

   @javascript
   Scenario: Two student should be able to register same iclicker id if they are in different courses.
      Given I log in as "student1"
      And I as student register iclicker id "11A4C277" at course "Course 1" and activity "iClicker Registration"
      And I log out
      And I am on homepage

      And I log in as "student3"
      And I as student register iclicker id "11A4C277" at course "Course 2" and activity "iClicker Registration"

      # Now, lets type a correct iclicker id, that is the same as the student1 in
      # course 1, but we are in course 2, so there should be no problem.
      Then I should not see "iclickerregistration/invalidiclickerid" lang string
      And  "#mod-iclickerregistration-validation-info-box li.list-group-item-danger" "css_element" should not exist

   @javascript
   Scenario: Invalid iclicker id (checksum failed) should throw an error.
      Given I log in as "student1"

      Given I follow "Course 1"
      Given I follow "iClicker Registration"
      Given I set the field "clicker-id" to "11A4C278"
      Given I press "iclickerregistration/registrationbuttontext" lang string
      Then I should not see "iclickerregistration/registrationsuccess" lang string
      Then I should see "iclickerregistration/invalidiclickerid" lang string