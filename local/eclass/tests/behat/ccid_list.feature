@local @landing @ccid @eclass_local_ctl
Feature: We can see a list of the courses to be archived
  In order to check the expected results are displayed
  As an admin
  I need to go to the landing page and see a list of archive course names, emails, and ccid's

  Background:
    Given the following "courses" exists:
      | fullname | shortname | idnumber | format |
      | Course 1 | C1 | UOFAB-ED-ELEM ED-EDEL.31 | topics |
      | Course 2 | C2 | UOFAB-ED-ELEM ED-EDEL.32 | topics |
      | Course 3 | C3 | UOFAB-ED-ELEM ED-EDEL.33 | topics |
      | Course 4 | C4 | UOFAB-ED-ELEM ED-EDEL.34 | topics |
      | Course 5 | C5 | UOFAB-ED-ELEM ED-EDEL.35 | topics |
    And the following "cohorts" exists:
      | name | idnumber |  contextlevel |
      | Cohort1 | 1400.99999 | System |
      | Cohort2 | 1401.99999 | System |
      | Cohort3 | 1402.99999 | System |
      | Cohort4 | 1403.99999 | System |
      | Cohort5 | 1404.99999 | System |
    And the following "users" exists:
      | username | firstname | lastname | email | idnumber |
      | student1 | Izzy | Aziz | student1@asd.com | t1 |
      | teacher1 | Maddie | Sun | teacher1@asd.com | t2 |
      | teacher2 | Silas | Goetz | teacher2@asd.com | t3 |
      | teacher3 | Violet | Laurie | teacher3@asd.com | t4 |
      | teacher4 | Kenzi | Jones | teacher4@asd.com | t5 |
    And the following "course enrolments" exists:
      | user | course | role |
      | student1 | C2 | student |
      | student1 | C3 | student |
      | student1 | C4 | student |
      | student1 | C5 | student |
      | teacher1 | C3 | editingteacher |
      | teacher1 | C4 | editingteacher |
      | teacher2 | C4 | editingteacher |
      | teacher2 | C4 | editingteacher |
      | teacher1 | C5 | editingteacher |
      | teacher2 | C5 | editingteacher |
      | teacher3 | C5 | teacher |
      | teacher4 | C5 | editingteacher |

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

  @javascript
  Scenario: Go to a landing page and view a list of courses to be archived
    Given I am on archive with term 1400
    And I should see "Course 1"
    And I should not see "Course 2"
    And I should not see "Course 3"
    And I should not see "Course 4"
    And I should not see "Course 5"
    And I should not see "Izzy"
    And I should not see "Maddie"
    And I should not see "Silas"
    And I should not see "Violet"
    And I should not see "Kenzi"
    Given I am on archive with term 1401
    And I should see "Course 1"
    And I should see "Course 2"
    And I should not see "Course 3"
    And I should not see "Course 4"
    And I should not see "Course 5"
    And I should not see "Izzy"
    And I should not see "Maddie"
    And I should not see "Silas"
    And I should not see "Violet"
    And I should not see "Kenzi"
    Given I am on archive with term 1402
    And I should see "Course 1"
    And I should see "Course 2"
    And I should see "Course 3"
    And I should not see "Course 4"
    And I should not see "Course 5"
    And I should not see "Izzy"
    And I should see "Maddie"
    And I should not see "Silas"
    And I should not see "Violet"
    And I should not see "Kenzi"
    Given I am on archive with term 1403
    And I should see "Course 1"
    And I should see "Course 2"
    And I should see "Course 3"
    And I should see "Course 4"
    And I should not see "Course 5"
    And I should not see "Izzy"
    And I should see "Maddie"
    And I should see "Silas"
    And I should not see "Violet"
    And I should not see "Kenzi"
    Given I am on archive with term 1404
    And I should see "Course 1"
    And I should see "Course 2"
    And I should see "Course 3"
    And I should see "Course 4"
    And I should see "Course 5"
    And I should not see "Izzy"
    And I should see "Maddie"
    And I should see "Silas"
    And I should not see "Violet"
    And I should see "Kenzi"
