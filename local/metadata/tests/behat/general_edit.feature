  @local_metadata
  Feature: Session tab
  Need to be able to add general information to the list
  And, this coveres X
  Note, need to add tests for linking of learning objectives and general tab

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C_shortname | 0 |
    #And the following
    And I log in as "admin"
    And I am on homepage
    And I follow "Course 1"
    And I follow "Instructor Moodle Metadata"
    And I follow "General"


  @javascript
  Scenario: adding new Knowledge sub objective
		Given I press "Course objective: Knowledge"
		And I press "option_add_fields_knowledge"
        Then I should see "Knowledge 1"
        

  @javascript
  Scenario: adding new Skill sub objective
		Given I press "Course objective: Skill"
		And I press "option_add_fields_skill"
        Then I should see "Skill 1"
        

  @javascript
  Scenario: adding new Attlitude sub objective
		Given I press "Course objective: Attitude"
		And I press "option_add_fields_attitude"
        Then I should see "Attitude 1"
        


  @javascript @cu
  Scenario: filling out all the information, then after click the input will remain on the page
    Given I press "Course objective: Knowledge"
	And I press "option_add_fields_knowledge"
	And I press "Course objective: Skill"
	And I press "option_add_fields_skill"
	And I press "Course objective: Attitude"
	And I press "option_add_fields_attitude"
	And I set the following fields to these values:
      | course_faculty | Some faculty |
      | program_type | program type 1 |
      | course_category | category 1 |
      | course_email | someone@ualberta.ca |
      | course_phone | 7809999999 |
      | course_office | CAB 411 |
      | course_officeh | 3:00pm-4:00pm |
      | course_assessment | 5 |
      | course_session | 5 |
      | knowledge_option[0] | knowledge1 |
      | skill_option[0] | skill1 |
      | attitude_option[0] | attitude1 |

    When I press "Save general information"
    Then I wait to be redirected
    And the following fields match these values:
      | course_faculty | Some faculty |
      | program_type | program type 1 |
      | course_category | category 1 |
      | course_email | someone@ualberta.ca |
      | course_phone | 7809999999 |
      | course_office | CAB 411 |
      | course_officeh | 3:00pm-4:00pm |
      | course_assessment | 5 |
      | course_session | 5 |
      | knowledge_option[0] | knowledge1 |
      | skill_option[0] | skill1 |
      | attitude_option[0] | attitude1 |




