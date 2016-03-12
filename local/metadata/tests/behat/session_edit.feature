@local_metadata @local_metadata_session
Feature: Session tab
	In order to use the session tab
	As an instructor
	I need to be able to enter information about an session
  
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
    And I create the following assessments for course "C_shortname":
      | assessmentname |
      | Assessment 1 |
      | Assessment 2 |
      | Assessment 3 |
    And I log in as "admin"
    And I am on homepage
    And I follow "Course 1"
    And I follow "Instructor Moodle Metadata"
    Then I follow "Session"
  
  @javascript
  Scenario: Ensuring all of the select lists are properly populated
    Given I create 1 sessions
    Then the "learning_objective_Attitude[0][]" select box should contain "A 1"
    And the "learning_objective_Attitude[0][]" select box should contain "A 2"
    And the "learning_objective_Attitude[0][]" select box should contain "A 3"
    And the "learning_objective_Knowledge[0][]" select box should contain "K 1"
    And the "learning_objective_Knowledge[0][]" select box should contain "K 2"
    And the "learning_objective_Skill[0][]" select box should contain "S 1"
    And the "assessments[0][]" select box should contain "Assessment 1"
    And the "assessments[0][]" select box should contain "Assessment 2"
    And the "assessments[0][]" select box should contain "Assessment 3"
  
  @javascript
  Scenario: Filling out regular forms for two sessions, one that already exists in DB, and one that doesn't. Will be almost identical
    Given I create 1 sessions
    And I press "Save changes"
    And I create 1 sessions
    And I set the following fields to these values:
      | sessiontitle[0] | First title |
      | sessiondescription[0] | Will normally be the first long title |
      | sessionguestteacher[0] | Bob the first Builder |
      | learning_objective_Attitude[0][] | A 1, A 3 |
      | learning_objective_Knowledge[0][] | K 1 |
      | learning_objective_Skill[0][] | S 1 |
      | assessments[0][] | Assessment 1, Assessment 3 |
      | sessiontype[0] | lab |
      | sessiontitle[1] | Second title |
      | sessiondescription[1] | Will normally be the second long title |
      | sessionguestteacher[1] | Bob the second Builder|
      | learning_objective_Attitude[1][] | A 2, A 3 |
      | learning_objective_Knowledge[1][] | K 2 |
      | learning_objective_Skill[1][] | S 1 |
      | assessments[1][] | Assessment 2 |
      | sessiontype[1] | seminar |
    When I press "Save changes"
    And I wait to be redirected
    Then the following fields match these values:
      | sessiontitle[0] | First title |
      | sessiondescription[0] | Will normally be the first long title |
      | sessionguestteacher[0] | Bob the first Builder |
      | learning_objective_Attitude[0][] | A 1, A 3 |
      | learning_objective_Knowledge[0][] | K 1 |
      | learning_objective_Skill[0][] | S 1 |
      | assessments[0][] | Assessment 1, Assessment 3 |
      | sessiontype[0] | lab |
      | sessiontitle[1] | Second title |
      | sessiondescription[1] | Will normally be the second long title |
      | sessionguestteacher[1] | Bob the second Builder|
      | learning_objective_Attitude[1][] | A 2, A 3 |
      | learning_objective_Knowledge[1][] | K 2 |
      | learning_objective_Skill[1][] | S 1 |
      | assessments[1][] | Assessment 2 |
      | sessiontype[1] | seminar |
    