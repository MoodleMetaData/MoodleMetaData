@local @landing @instructor_emails @eclass_local_ctl
Feature: We can see a list of the termcode requested emails
  In order to check the expected results are displayed
  As an admin
  I need to go to the landing page and see a list of instructors emails

  Background:
    Given the following "courses" exists:
      | fullname | shortname | idnumber | format |
      | Course 1 | C1 | UOFAB-ED-ELEM ED-EDEL.31 | topics |
      | Course 2 | C2 | UOFAB-ED-ELEM ED-EDEL.32 | topics |
      | Course 3 | C3 | UOFAB-ED-ELEM ED-EDEL.33 | topics |
    And the following "cohorts" exists:
      | name | idnumber |  contextlevel |
      | Cohort1 | 1400.99999 | System |
      | Cohort2 | 1401.01400 | System |
      | Cohort3 | 1402.99999 | System |
    And the following "users" exists:
      | username | firstname | lastname | email | idnumber |
      | student1 | wynottbuymour | royko | student1@asd.com | s1 |
      | teacher1 | andrea | jones | teacher1@asd.com | t1 |
      | teacher2 | spider | gibeau | teacher2@asd.com | t2 |
    And the following "course enrolments" exists:
      | user | course | role |
      | student1 | C1 | student |
      | student1 | C2 | student |
      | teacher1 | C2 | editingteacher |
      | teacher2 | C3 | editingteacher |

    And I log in as "admin"
    And I follow "Course 1"
    And I add "Cohort sync" enrolment method with:
      | name | Cohort1 |
      | customint1 | Cohort1 |
    And I am on homepage
    And I follow "Course 2"
    And I add "Cohort sync" enrolment method with:
      | name | Cohort2 |
      | customint1 | Cohort2 |
    And I am on homepage
    And I follow "Course 3"
    And I add "Cohort sync" enrolment method with:
      | name | Cohort3 |
      | customint1 | Cohort3 |

  @javascript
  Scenario: Go to a landing page and view a list of instructor emails
    Given I am on instructor emails with term "1400"
    And I should not see "student1@asd.com"
    And I should not see "teacher1@asd.com"
    And I should not see "teacher2@asd.com"
    And I am on homepage
    Given I am on instructor emails with term "1401"
    And I should see "teacher1@asd.com"
    And I should not see "student1@asd.com"
    And I should not see "teacher2@asd.com"
    And I am on homepage
    Given I am on instructor emails with term "1400,1401,1402"
    And I should see "teacher1@asd.com"
    And I should see "teacher2@asd.com"
    And I should not see "student1@asd.com"
