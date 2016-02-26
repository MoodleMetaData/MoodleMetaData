@local @landing @usage_stats
Feature: We can see usage stats based on the given paramters
  In order to find stats about usage of the system
  As an admin
  I need to go to the landing page and supply filter criteria

  Background:
#    Given the following "role" exist:
#      | name    | shortname | description     | archetype      |
#      | Student | student   | Regular student | student        |
#      | Teacher | teacher   | Regular teacher | teacher        |
    And the following "users" exist:
      | username | firstname | lastname | email |
      | user1 | User | 1 | user1@moodlemoodle.com |
      | user2 | User | 2 | user2@moodlemoodle.com |
      | user3 | User | 3 | user3@moodlemoodle.com |
      | user4 | User | 4 | user4@moodlemoodle.com |
      | user5 | User | 5 | user5@moodlemoodle.com |
    And the following "categories" exist:
      | name | category | idnumber | description |
      | Cat1 | 0        | UOFAB    | UOFAB       |
      | Cat2 | UOFAB    | UOFAB-A  | UOFAB-A     |
      | Cat3 | UOFAB    | UOFAB-X  | UOFAB-X     |
      | Cat4 | UOFAB-A  | UOFAB-AB | UOFAB-AB    |
      | Cat5 | UOFAB-A  | UOFAB-AC | UOFAB-AC    |
      | Cat6 | UOFAB-X  | UOFAB-XA | UOFAB-XA    |
      | Cat7 | UOFAB-X  | UOFAB-XB | UOFAB-XB    |
    And the following "courses" exist:
      | fullname | shortname | idnumber |
      | Course 0 | C0        | UOFBC-1  |
      | Course 1 | C1        | UOFAB-1  |
    And the following "courses" exist:
      | fullname | shortname | idnumber | category |
      | Course 2 | C2        | UOFAB-2  | UOFAB    |
      | Course 3 | C3        | UOFAB-3  | UOFAB-A  |
      | Course 4 | C4        | UofAB-4  | UOFAB-AB |
      | Course 5 | C5        | UOFAB-5  | UOFAB-AB |
      | Course 6 | C6        | UOFAB-6  | UOFAB-AC |
      | Course 7 | C7        | UOFAB-7  | UOFAB-AC |
      | Course 8 | C8        | UOFAB-8  | UOFAB-AC |
      | Course 9 | C9        | UOFAB-9  | UOFAB-XB |
    And the following "course enrolments" exist:
      | user  | course | role    |
      | user1 | C1     | student |
      | user5 | C1     | teacher |
      | user4 | C1     | teacher |
      | user1 | C2     | student |
      | user2 | C2     | student |
      | user1 | C3     | student |
      | user2 | C3     | student |
      | user3 | C3     | student |
      | user1 | C4     | student |
      | user2 | C4     | student |
      | user3 | C4     | student |
      | user4 | C4     | student |
      | user1 | C5     | student |
      | user2 | C5     | student |
      | user3 | C5     | student |
      | user4 | C5     | student |
      | user5 | C5     | student |
      | user5 | C6     | teacher |
    And I log in as "admin"

  Scenario: 1. Go to usage_stats landing page with no parameters
    Given I am on usage_stats with no parameters
    Then I should see "Displaying data for all terms.  Override with eg. ?term=1450,1460"
    And I should see "Data generated at"
    And I should see "on server"
    And I should see "Total courses in all terms: 10"
    And I should see "Course idnumbers beginning with UOFAB in all terms: 8"
    And I should see "(roleid=5)"
    And I should see "Number of Students Enrolled (n)"
    And I should see "Number of UOFAB Courses with n Students"
    And I should see "Percent of Total UOFAB Courses"
    And I should see "Number of UOFAB Courses with Up To n Students"
    And I should see "Cumulative Percent"
    And I should see "1 1 12.5 1 12.5"
    And I should see "2 1 12.5 2 25"
    And I should see "3 2 25 4 50"
    And I should see "5 1 12.5 5 62.5"
    And I should see "Mean number of students enrolled per credit course: 1.75"
    And I should see "Variance: 1.7890625"
    And I should see "faculty name num_courses"
    And I should see "UOFAB-A Cat2 5"
    And I should see "UOFAB-X Cat3 1"
    And I should see "dept name num_courses"
    And I should see "UOFAB-AB Cat4 1"
    And I should see "UOFAB-AC Cat5 3"
    And I should see "UOFAB-XB Cat7 1"
    And I should see "To see queries used, suffix url with ?verbose=1"

  Scenario: 2. Go to usage_stats landing page with invalid term parameter
    Given I am on usage_stats with term "fourteensixty"
    Then I should see "Displaying data for all terms."
    And I should see "To see queries used, suffix url with &verbose=1"

  Scenario: 3. Go to usage_stats landing page with valid term parameter containing no courses
    Given I am on usage_stats with term "1450"
    Then I should see "Displaying data for term(s): 1450."
    And I should see "Total courses in term(s) 1450: 0."
    And I should see "Course idnumbers beginning with UOFAB in term(s) 1450: 0."
    And I should see "ERROR: No enrolment data found."
    And I should see "ERROR: No data to print."

  Scenario: 4. Go to usage_stats landing page with verbose and term parameters
    Given I am on usage_stats with verbose and term "1450"
    Then I should not see "To see queries used, suffix url with"
    And I should see "Queries generated:"
