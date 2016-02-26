@local @landing @criteria_emails @eclass_local_ctl
Feature: We can see a list emails based on criteria
  In order to check the expected results are displayed
  As an admin
  I need to go to the landing page and see a list of emails

  # Test criteria_emails_list landing page
  #
  # @author Anthony Radziszewski radzisze@ualberta.ca
  # @package    local
  # @category   eclass/tests/behat
  # @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

  Background:
    Given the following "categories" exists:
      | name | idnumber |
      | firstcat  | 1   |
      | secondcat  |  2 |
      | thirdcat  |  3  |
    And the following "courses" exists:
      | fullname | shortname | idnumber | format |  category  |
      | Course 1 | C1 | UOFAB-ED-ELEM ED-EDEL.31 | topics | 0 |
      | Course 2 | C2 | UOFAB-ED-ELEM ED-EDEL.32 | topics | 1 |
      | Course 3 | C3 | UOFAB-ED-ELEM ED-EDEL.33 | topics | 2 |
    And the following "cohorts" exists:
      | name | idnumber |  contextlevel |
      | Cohort1 | 1400.99999 | System |
      | Cohort2 | 1401.01400 | System |
      | Cohort3 | 1402.99999 | System |
    And the following "users" exists:
      | username | firstname | lastname | email | idnumber |  lastaccess  |
      | student1 | wynottbuymour | royko | student1@asd.com | s1 |  1417630889 |
      | teacher1 | andrea | jones | teacher1@asd.com | t1 | 123456789         |
      | teacher2 | spider | gibeau | teacher2@asd.com | t2 |  1417630663       |
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
  Scenario: Go to a landing page as admin and view a list of emails filtered by last access timestamp
    Given I am on criteria emails with accessdate ""
    And I should see "Missing a criteria code"
    And I should not see "teacher1@asd.com"
    And I should not see "teacher2@asd.com"
    And I should not see "student1@asd.com"
    And I am on homepage
    Given I am on criteria emails with accessdate "0"
    And I should see "Missing a criteria code"
    And I should not see "teacher1@asd.com"
    And I should not see "teacher2@asd.com"
    And I should not see "student1@asd.com"
    And I am on homepage
    Given I am on criteria emails with accessdate "1"
    And I should see "teacher1@asd.com"
    And I should see "teacher2@asd.com"
    And I should see "student1@asd.com"
    And I am on homepage
    Given I am on criteria emails with accessdate "1417630663"
    And I should not see "teacher1@asd.com"
    And I should see "teacher2@asd.com"
    And I should see "student1@asd.com"

  @javascript
  Scenario: Go to a landing page as admin and view a list of emails filtered by course
    Given I am on criteria emails with course ""
    And I should see "Missing a criteria code"
    And I should not see "teacher1@asd.com"
    And I should not see "teacher2@asd.com"
    And I should not see "student1@asd.com"
    And I am on homepage
    Given I am on criteria emails with course "99"
    And I should see "No records found."
    And I should not see "teacher1@asd.com"
    And I should not see "teacher2@asd.com"
    And I should not see "student1@asd.com"
    And I am on homepage
    Given I am on criteria emails with course "2"
    And I should see "student1@asd.com"
    And I should not see "teacher1@asd.com"
    And I should not see "teacher2@asd.com"
    And I am on homepage
    Given I am on criteria emails with course "3"
    And I should see "teacher1@asd.com"
    And I should not see "teacher2@asd.com"
    And I should see "student1@asd.com"
    And I am on homepage
    Given I am on criteria emails with course "4"
    And I should not see "teacher1@asd.com"
    And I should see "teacher2@asd.com"
    And I should not see "student1@asd.com"
    And I am on homepage
    Given I am on criteria emails with course "2,4"
    And I should not see "teacher1@asd.com"
    And I should see "teacher2@asd.com"
    And I should see "student1@asd.com"
    And I am on homepage
    Given I am on criteria emails with course "2,3,4"
    And I should see "teacher1@asd.com"
    And I should see "teacher2@asd.com"
    And I should see "student1@asd.com"

  @javascript
  Scenario: Go to a landing page as admin and view a list of emails filtered by role
    Given I am on criteria emails with role ""
    And I should see "Missing a criteria code"
    And I should not see "teacher1@asd.com"
    And I should not see "teacher2@asd.com"
    And I should not see "student1@asd.com"
    And I am on homepage
    Given I am on criteria emails with role "99"
    And I should see "No records found."
    And I should not see "teacher1@asd.com"
    And I should not see "teacher2@asd.com"
    And I should not see "student1@asd.com"
    And I am on homepage
    Given I am on criteria emails with role "5"
    And I should see "student1@asd.com"
    And I should not see "teacher1@asd.com"
    And I should not see "teacher2@asd.com"
    And I am on homepage
    Given I am on criteria emails with role "3"
    And I should see "teacher1@asd.com"
    And I should see "teacher2@asd.com"
    And I should not see "student1@asd.com"
    And I am on homepage
    Given I am on criteria emails with role "3,5"
    And I should see "teacher1@asd.com"
    And I should see "teacher2@asd.com"
    And I should see "student1@asd.com"

  @javascript
  Scenario: Go to a landing page as admin and view a list of emails filtered by category
    Given I am on criteria emails with category ""
    And I should see "Missing a criteria code"
    And I should not see "teacher1@asd.com"
    And I should not see "teacher2@asd.com"
    And I should not see "student1@asd.com"
    And I am on homepage
    Given I am on criteria emails with category "99"
    And I should see "No records found."
    And I should not see "teacher1@asd.com"
    And I should not see "teacher2@asd.com"
    And I should not see "student1@asd.com"
    And I am on homepage
    Given I am on criteria emails with category "1"
    And I should see "student1@asd.com"
    And I should not see "teacher1@asd.com"
    And I should not see "teacher2@asd.com"
    And I am on homepage
    Given I am on criteria emails with category "2"
    And I should see "teacher1@asd.com"
    And I should not see "teacher2@asd.com"
    And I should see "student1@asd.com"
    And I am on homepage
    Given I am on criteria emails with category "1,2"
    And I should see "teacher1@asd.com"
    And I should not see "teacher2@asd.com"
    And I should see "student1@asd.com"
    And I am on homepage
    Given I am on criteria emails with category "1,2,3"
    And I should see "teacher1@asd.com"
    And I should see "teacher2@asd.com"
    And I should see "student1@asd.com"

  @javascript
  Scenario: Go to a landing page as admin and view a list of emails filtered by term
    Given I am on criteria emails with term ""
    And I should see "Missing a criteria code"
    And I should not see "teacher1@asd.com"
    And I should not see "teacher2@asd.com"
    And I should not see "student1@asd.com"
    And I am on homepage
    Given I am on criteria emails with term "9999"
    And I should see "No records found."
    And I should not see "teacher1@asd.com"
    And I should not see "teacher2@asd.com"
    And I should not see "student1@asd.com"
    And I am on homepage
    Given I am on criteria emails with term "1400"
    And I should see "student1@asd.com"
    And I should not see "teacher1@asd.com"
    And I should not see "teacher2@asd.com"
    And I am on homepage
    Given I am on criteria emails with term "1401"
    And I should see "teacher1@asd.com"
    And I should see "student1@asd.com"
    And I should not see "teacher2@asd.com"
    And I am on homepage
    Given I am on criteria emails with term "1400,1401"
    And I should see "teacher1@asd.com"
    And I should see "student1@asd.com"
    And I should not see "teacher2@asd.com"
    And I am on homepage
    Given I am on criteria emails with term "1400,1401,1402"
    And I should see "teacher1@asd.com"
    And I should see "teacher2@asd.com"
    And I should see "student1@asd.com"
