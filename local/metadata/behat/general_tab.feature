  @local_metadata
  Feature: Session tab
  Need to be able to add sessions to the list
  And, this coveres X
  Note, need to add tests for linking of learning objectives and assessments

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
        Then I should see "Knowledge 1"
        Given I press "option_add_fields_knowledge"

  @javascript
  Scenario: adding new Skill sub objective
        Then I should see "Skill 1"
        Given I press "option_add_fields_skill"

  @javascript
  Scenario: adding new Attlitude sub objective
        Then I should see "Attitude 1"
        Given I press "option_add_fields_attitude"


  @javascript
  Scenario: filling out all the information, then after click the input will remain on the page
    Given I set the following fields to these values:
      | course_faculty | Some faculty |
      | program_type[0] | program tye 1 |
      | course_category[0] | category 1 |
      | course_email | someone@ualberta.ca |
      | course_phone | 7809999999 |
      | course_office | CAB 411 |
      | course_officeh | 3:00pm-4:00pm |
      | course_desc_header | some description |
      | course_assessment | 5 |
      | course_session | 5 |
      | knowledge_option[0] | knowledge1 |
      | skill_option[0] | skill1 |
      | attitude_option[0] | attitude1 |

    When I press "Save changes"
    Then I wait to be redirected
    And the following fields match these values:
      | course_faculty | Some faculty |
      | program_type[0] | program tye 1 |
      | course_category[0] | category 1 |
      | course_email | someone@ualberta.ca |
      | course_phone | 7809999999 |
      | course_office | CAB 411 |
      | course_officeh | 3:00pm-4:00pm |
      | course_desc_header | some description |
      | course_assessment | 5 |
      | course_session | 5 |
      | knowledge_option[0] | knowledge1 |
      | skill_option[0] | skill1 |
      | attitude_option[0] | attitude1 |




