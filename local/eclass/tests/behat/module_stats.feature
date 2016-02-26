@local @landing @module_stats
Feature: We can see module stats based on the given paramters
  In order to find stats about modules in the system
  As an admin
  I need to go to the landing page and supply filter criteria

  Background:
    Given the following "categories" exist:
      | name | category | idnumber |
      | pcat1 | 0 | pcat1 |
      | pcat2 | 0 | pcat2 |
      | scat1 | pcat1 | scat1 |
    And the following "courses" exist:
      | fullname | shortname | idnumber | format | category |
      | Course 1 | C1 | UOFAB-ED-ELEM ED-EDEL.31 | topics | pcat1 |
      | Course 2 | C2 | UOFAB-ED-ELEM ED-EDEL.32 | topics | pcat2 |
      | Course 3 | C3 | UOFAB-ED-ELEM ED-EDEL.33 | topics | scat1 |
    And I log in as "admin"

  Scenario: 1. Go to module_stats landing page with no parameters
    Given I am on module_stats with no parameters
    Then I should see "Require a module type (activity or resource) to generate data for, eg. ?mod=quiz"
    And I should see "Available modules:"

  Scenario: 2a. Go to module_stats landing page with an invalid mod parameter
    Given I am on module_stats with mod "a"
    Then I should see "Module 'a' not found."
    And I should see "Available modules:"

  Scenario: 2b. Go to module_stats landing page with only a valid mod parameter
    Given I am on module_stats with mod "assign"
    Then I should see "Displaying data for module: assign (moduleid="
    And I should see "Displaying data for all terms."
    And I should see "Override with eg. &term=1450,1460"
    And I should see "Displaying data for all credit categories."
    And I should see "Override with eg. &cat=114,184"
    And I should see "(List categories with &cat=-1 )"
    And I should see "Displaying data for category/ies:"

  Scenario: 2c. Go to module_stats landing page with 2 valid mod parameter
    Given I am on module_stats with mod "assign,quiz"
    Then I should see "Module 'assign,quiz' not found."
    And I should see "Available modules:"

  Scenario: 3a. Go to module_stats landing page with invalid cat parameter
    Given I am on module_stats with mod "assign" and cat "-1"
    Then I should see "Displaying data for module: assign (moduleid="
    And I should see "Displaying data for all terms."
    And I should see "Override with eg. &term=1450,1460"
    And I should see "Displaying data for category/ies: -1."
    And I should see "(Courses in subcategories included where noted.)"
    And I should see "ERROR: Could not find any context paths for categories. Check your 'cat' parameter."
    And I should see "Available Categories:"

  Scenario: 3b. Go to module_stats landing page with mod(assign) and cat parameters
    Given I am on module_stats with mod "assign" and cat "1"
    Then I should see "Displaying data for module: assign (moduleid="
    And I should see "Displaying data for all terms."
    And I should see "Override with eg. &term=1450,1460"
    And I should see "Displaying data for category/ies: 1."
    And I should see "(Courses in subcategories included where noted.)"
    And I should see "Category context paths:"
    And I should see "Data generated at"
    And I should see "on server"
    And I should see "Total courses in all terms:"
    And I should see "Number of courses containing module assign (moduleid="
    And I should see "0 assignment submissions found in specified credit courses."

  Scenario: 3c. Go to module_stats landing page with mod(assign) and cat parameters with no subcat
    Given I am on module_stats with mod "assign" and cat "pcat2"
    And I should see "Displaying data for category/ies:"
    And I should see "Total courses in all terms: 3"
    And I should see "Number of courses in category/ies"
    And I should see "and subcategories, for selected term(s): 1"

  Scenario: 3d. Go to module_stats landing page with mod(assign) and cat parameters with subcat
    Given I am on module_stats with mod "assign" and cat "pcat1"
    And I should see "Displaying data for category/ies:"
    And I should see "Total courses in all terms: 3"
    And I should see "Number of courses in category/ies"
    And I should see "and subcategories, for selected term(s): 2"

  Scenario: 3e. Go to module_stats landing page with mod(assign) and 2 cat parameters
    Given I am on module_stats with mod "assign" and cat "scat1,pcat2"
    And I should see "Displaying data for category/ies:"
    And I should see "001, "
    And I should see "002."
    And I should see "Total courses in all terms: 3"
    And I should see "Number of courses in category/ies"
    And I should see "and subcategories, for selected term(s): 2"

  Scenario: 3f. Go to module_stats landing page with mod(assign) and bad alphabetic cat parameter
    Given I am on module_stats with mod "assign" and cat "splat"
    And I should see "Non-numerals found in 'cat' parameter.  Assuming it's a list of names, not ids."
    And I should see "ERROR: Could not find category ids corresponding to name(s) 'splat'."
    And I should see "Displaying data for category/ies: 31."

  Scenario: 3g. Go to module_stats landing page with mod(assign) and cat and term parameters
    Given I am on module_stats with mod "assign" and cat "pcat1" and term "14,1450,abc"
    Then I should see "Displaying data for module: assign (moduleid="
    And I should see "Displaying data for term(s): 1450."

  Scenario: 4a. Go to module_stats landing page with mod(assign) and cat parameters with 0 assignments
    Given I am on module_stats with mod "assign" and cat "pcat1"
    Then I should see "Displaying data for module: assign (moduleid="
    And I should see "Total courses in all terms: 3"
    And I should see "Number of courses containing module assign (moduleid="
    And I should see "and all subcategories, for selected term(s): 0"
    And I should see "Number of instances of module assign (moduleid="
    And I should see "for selected term(s) in credit courses: 0"
    And I should see "0 assignment submissions found in specified credit courses."

  Scenario: 4b. Go to module_stats landing page with mod(assign) and cat parameters with 3 assignments
    Given the following "activities" exist:
      | activity | course | idnumber | name         | intro               |
      | assign   | C1     | assign1  | assign1 name | assign1 description |
      | assign   | C1     | assign2  | assign2 name | assign2 description |
      | assign   | C1     | assign3  | assign3 name | assign3 description |
    And I am on module_stats with mod "assign" and cat "pcat1"
    Then I should see "Displaying data for module: assign (moduleid="
    And I should see "Total courses in all terms: 3"
    And I should see "Number of courses containing module assign (moduleid="
    And I should see "and all subcategories, for selected term(s): 1"
    And I should see "Number of instances of module assign (moduleid="
    And I should see "for selected term(s) in credit courses: 3"
    And I should see "0 assignment submissions found in specified credit courses."

  Scenario: 5a. Go to module_stats landing page with mod(forum) and cat and term parameters with 0 forums
    Given I am on module_stats with mod "forum" and cat "pcat1"
    Then I should see "Displaying data for module: forum (moduleid="
    And I should see "Number of courses containing module forum (moduleid="
    And I should see "and all subcategories, for selected term(s) in credit & non-credit courses: 0"
    And I should see "0 forum topics found in specified credit courses."
    And I should see "0 forum posts found in specified credit courses."

  Scenario: 5b. Go to module_stats landing page with mod(forum) and cat and term parameters with some forums
    Given I am on homepage
    And I follow "Course 1"
    And I turn editing mode on
    And I add a "Forum" to section "1" and I fill the form with:
      | Forum name | Test forum name |
      | Forum type | Standard forum for general use |
      | Description | Test forum description |
    And I add a new discussion to "Test forum name" forum with:
      | Subject | Forum discussion 1 |
      | Message | How awesome is this forum discussion? |
    And I reply "Forum discussion 1" post from "Test forum name" forum with:
      | Message | Actually, I've seen better. |
    And I am on module_stats with mod "forum" and cat "pcat1"
    Then I should see "Displaying data for module: forum (moduleid="
    And I should see "Number of courses containing module forum (moduleid="
    And I should see "and all subcategories, for selected term(s) in credit & non-credit courses: 1"
    And I should see "Number of forum topics in specified credit courses: 1"
    And I should see "Number of forum posts in specified credit courses: 2"

  Scenario: 6a. Go to module_stats landing page with mod(quiz) and cat parameters with 0 quizzes
    Given I am on module_stats with mod "quiz" and cat "pcat1"
    Then I should see "Displaying data for module: quiz (moduleid="
    And I should see "Displaying data for category/ies:"
    And I should see "000."
    And I should see "Number of credit courses containing module quiz (moduleid="
    And I should see "and all subcategories, for selected term(s) in credit & non-credit courses: 0"
    And I should see "0 quiz attempts found in specified credit courses."

  Scenario: 6b. Go to module_stats landing page with mod(quiz) and cat parameters with some quizzes
    Given I am on homepage
    And I follow "Course 1"
    And I turn editing mode on
    And I add a "Quiz" to section "1" and I fill the form with:
      | Name        | Test quiz1 name        |
      | Description | Test quiz1 description |
    And I add a "True/False" question to the "Test quiz1 name" quiz with:
      | Question name                      | First question                          |
      | Question text                      | Answer the first question               |
      | General feedback                   | Thank you, this is the general feedback |
      | Correct answer                     | False                                   |
      | Feedback for the response 'True'.  | So you think it is true                 |
      | Feedback for the response 'False'. | So you think it is false                |
    And I am on homepage
    And I follow "Course 1"
    And I add a "Quiz" to section "2" and I fill the form with:
      | Name        | Test quiz2 name        |
      | Description | Test quiz2 description |
    And I add a "True/False" question to the "Test quiz2 name" quiz with:
      | Question name                      | First question                          |
      | Question text                      | Answer the first question               |
      | General feedback                   | Thank you, this is the general feedback |
      | Correct answer                     | False                                   |
      | Feedback for the response 'True'.  | So you think it is true                 |
      | Feedback for the response 'False'. | So you think it is false                |
    And I am on module_stats with mod "quiz" and cat "pcat1"
    Then I should see "Displaying data for module: quiz (moduleid="
    And I should see "Displaying data for category/ies:"
    And I should see "000."
    And I should see "Number of credit courses containing module quiz (moduleid="
    And I should see "and all subcategories, for selected term(s) in credit & non-credit courses: 2"
    And I should see "0 quiz attempts found in specified credit courses."

