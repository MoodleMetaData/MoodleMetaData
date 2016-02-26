@local @landing @archive_list @eclass_local_ctl
Feature: We can see a list of the courses to be archived
  In order to check the expected results are displayed
  As an admin
  I need to go to the landing page and see a list of archive course id's

  Background:
    Given the following "courses" exists:
      | fullname | shortname | idnumber | format |
      | Course 1 | C1 | UOFAB-ED-ELEM ED-EDEL.32 | topics |
      | Course 2 | C2 | Moved | topics |
      | Course 3 | C3 | UOFAB-ED-ELEM ED-EDEL.33 | topics |
      | Course 4 | C4 | UOFAB-ED-ELEM ED-EDEL.34 | topics |
      | Course 5 | C5 | UOFAB-ED-ELEM ED-EDEL.35 | topics |
      | Course 6 | C6 | UOFAB-ED-ELEM ED-EDEL.36 | topics |
    And the following "cohorts" exists:
      | name | idnumber |  contextlevel |
      | Cohort1 | 1400.99999 | System |
      | Cohort2 | 1400.12345 | System |
      | Cohort3 | 1450.12345 | System |
      | Cohort4 | 1401.00000 | System |
      | Cohort5 | 12345.123 | System |
      | Cohort6 | sandbox1 | System |
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
    And I am on homepage
    And I follow "Course 4"
    And I add "Cohort sync" enrolment method with:
      | name | Cohort4 |
      | customint1 | Cohort4 |
    And I am on homepage
    And I follow "Course 5"
    And I add "Cohort sync" enrolment method with:
      | name | Cohort5 |
      | customint1 | Cohort5 |
    And I am on homepage
    And I follow "Course 6"
    And I add "Cohort sync" enrolment method with:
      | name | Cohort6 |
      | customint1 | Cohort6 |

  @javascript
  Scenario: Go to a landing page and view a list of courses to be archived
    Given I am on archive with term 1200
    And I should see "No records found"
    Given I am on archive with term 1400
    And I should see "Course 1"
    And I should not see "Course 2"
    And I should not see "Course 3"
    And I should not see "Course 4"
    And I should not see "Course 5"
    And I should not see "Course 6"
