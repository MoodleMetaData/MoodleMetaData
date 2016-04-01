@local_metadata @local_metadata_assessment
Feature: Assessment tab
	In order to use the assessment tab
	As an instructor
	I need to be able to enter information about an assessment
  
  Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C_shortname | 0 |
    And I create the following learning objectives for course "C_shortname":
      | objectivename | objectivetype |
      | A 1 | Attitude |
      | A 2 | Attitude |
      | A 3 | Attitude |
      | K 1 | Knowledge |
      | K 2 | Knowledge |
      | S 1 | Skill |
    And I log in as "admin"
    And I am on homepage
    And I follow "Course 1"
    And I follow "Instructor Moodle Metadata"
    Then I follow "Assessment"
  
  Scenario: Ensuring all of the select lists are properly populated
    Given I create 1 assessments
    Then the "learning_objective_Attitude[0][]" select box should contain "A 1"
    And the "learning_objective_Attitude[0][]" select box should contain "A 2"
    And the "learning_objective_Attitude[0][]" select box should contain "A 3"
    And the "learning_objective_Knowledge[0][]" select box should contain "K 1"
    And the "learning_objective_Knowledge[0][]" select box should contain "K 2"
    And the "learning_objective_Skill[0][]" select box should contain "S 1"
  
  Scenario: Filling out regular forms for two assessments, one that already exists in DB, and one that doesn't. Will be almost identical
    Given I create 1 assessments
    And I press "Save changes"
    And I create 1 assessments
    And I set the following fields to these values:
      | assessmentname[0] | Title 1 |
      | type[0] | Exam |
      | assessmentprof[0] | Norris, Chuck |
      | assessmentexamtype[0] | Written |
      | description[0] | Short desc 1 |
      | gdescription[0] | Grading desc 1 |
      | assessmentweight[0] | 10 |
      | learning_objective_Attitude[0][] | A 1, A 3 |
      | learning_objective_Knowledge[0][] | K 1 |
      | learning_objective_Skill[0][] | S 1 |
      | assessmentname[1] | Title 2 |
      | type[1] | Assignment |
      | description[1] | Short desc 2 |
      | gdescription[1] | Grading desc 1|
      | assessmentweight[1] | 15 |
      | learning_objective_Attitude[1][] | A 2, A 3 |
      | learning_objective_Knowledge[1][] | K 2 |
      | learning_objective_Skill[1][] | S 1 |
    When I press "Save changes"
    And I wait to be redirected
    Then the following fields match these values:
      | assessmentname[0] | Title 1 |
      | type[0] | Exam |
      | assessmentprof[0] | Norris, Chuck |
      | assessmentexamtype[0] | Written |
      | description[0] | Short desc 1 |
      | gdescription[0] | Grading desc 1 |
      | assessmentweight[0] | 10 |
      | learning_objective_Attitude[0][] | A 1, A 3 |
      | learning_objective_Knowledge[0][] | K 1 |
      | learning_objective_Skill[0][] | S 1 |
      | assessmentname[1] | Title 2 |
      | type[1] | Assignment |
      | description[1] | Short desc 2 |
      | gdescription[1] | Grading desc 1|
      | assessmentweight[1] | 15 |
      | learning_objective_Attitude[1][] | A 2, A 3 |
      | learning_objective_Knowledge[1][] | K 2 |
      | learning_objective_Skill[1][] | S 1 |
    
    @javascript
    Scenario: Should only allow writing in assessmentprof/assessmentexamtype when type is Exam. Behat doesn't work with assessmentprof being disabled
      Given I create 1 assessments
      And I set the field "type[0]" to "Exam"
      When I take focus off "type[0]" "select"
      Then the "assessmentprof[0]" "text" should be enabled
      And the "assessmentexamtype[0]" "select" should be enabled
      Given I set the field "type[0]" to "Assignment"
      When I take focus off "type[0]" "select"
      And the "assessmentexamtype[0]" "select" should be disabled
      Given I set the field "type[0]" to "Participation"
      When I take focus off "type[0]" "select"
      And the "assessmentexamtype[0]" "select" should be disabled
      Given I set the field "type[0]" to "Other"
      When I take focus off "type[0]" "select"
      And the "assessmentexamtype[0]" "select" should be disabled