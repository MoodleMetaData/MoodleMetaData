@block @block_skills_group @eclass-blocks-skills_group
Feature: Create skills_group settings
  In order to create the skills_group settings
  As a admin
  I need to go to the settings page and select a feedback and grouping

  Background:
    Given the following config values are set as admin:
      | enableavailability | 1 |
    And I log in as "admin"
    And I expand "Site administration" node
    # The wait is important to let the site admin node expand: seems like a bug, moodle code should do it.
    And I wait "10" seconds
    And I navigate to "Manage activities" node in "Site administration > Plugins > Activity modules"
    And I click on "//a[@title=\"Show\"]" "xpath_element" in the "Feedback" "table_row"
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1 | 0 |
    And the following "groupings" exist:
      | name       | course  | idnumber  |
      | Grouping 1 | C1 | GROUPING1 |
    And the following "activities" exist:
      | activity | name | intro | course | idnumber | anonymous |
      | feedback | Feedback 1 | Test feedback description | C1 | feedback1 | 2 |
    And the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Test | Teacher | teacher1@ualberta.ca |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
    And I log out

  @javascript
  Scenario: Initialize skills_group settings
    And I log in as "teacher1"
    And I follow "Course 1"
    And I turn editing mode on
    And I add the "Group Sign-up" block
    And I click on "Edit skills group settings" "link"
    And I set the following fields to these values:
      | feedbacks | Feedback 1 |
      | groupings | Grouping 1 |
      | allownaming | 1 |
      | maxsize | 6 |
      | threshold | 1 |
      | datecheck | 0 |
    And I press "Save changes"
    Then I should see "Edit skills group settings"

  @javascript
  Scenario: Initaliaze settings without a feedback
    And I log in as "teacher1"
    And I follow "Course 1"
    And I turn editing mode on
    And I add the "Group Sign-up" block
    And I click on "Edit skills group settings" "link"
    And I set the following fields to these values:
      | feedbacks | None |
      | groupings | Grouping 1 |
      | allownaming | 1 |
      | maxsize | 6 |
      | threshold | 1 |
      | datecheck | 0 |
    And I press "Save changes"
    Then I should see "Edit skills group settings"

