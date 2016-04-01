@local_metadata @local_metadata_general @local_metadata_general_add
Feature: General tab
	In order to use the general tab
	As an instructor
	I need to be able to enter general course information
	
  Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C_shortname | 0 |
	And I create the following course categories for faculty "Miscellaneous":
	  | categoryname |
	  | Category 1 |
	  | Category 2 |
	And I create the following graduate attributes:
	  | attribute |
	  | At1 |
	  | At2 |
	And I log in as "admin"
    And I am on homepage
    And I follow "Course 1"
    And I follow "Instructor Moodle Metadata"
    And I follow "General"
	
  Scenario: Filling out all required and optional fields.
	Given I set the following fields to these values:
	  | course_category | Category 1 |
	  | course_year | 2018 |
	  | course_term | Fall |
	  | course_email | someone@ualberta.ca |
	  | course_phone | 7809999999 |
	  | course_office | CAB 411 |
	  | default_officeh | "" |
	  | course_description[text] | This is a new description |
	  | course_assessment | 1 |
	  | course_session | 2 |
	  | teaching_assumption[text] | This is teaching assumption |
	And I expand "Required readings" node
	And I press "option_add_fields_reading"
	And I set the field "readingname_option[0]" to "Title1"
	And I set the field "readingurl_option[0]" to "Url1"
	And I expand "Course objective: Knowledge" node
	And I press "option_add_fields_knowledge"
	And I set the field "knowledge_option[0]" to "K1"
	And I expand "Course objective: Skill" node
	And I press "option_add_fields_skill"
	And I set the field "skill_option[0]" to "S1"
	And I expand "Course objective: Atittude" node
	And I press "option_add_fields_attitude"
	And I set the field "attitude_option[0]" to "A1"
	When I press "Save general information"
	And I wait to be redirected
	Then the following fields match these values:
	  | course_category | Category 1 |
	  | course_year | 2018 |
	  | course_term | Fall |
	  | course_email | someone@ualberta.ca |
	  | course_phone | 7809999999 |
	  | course_office | CAB 411 |
	  | default_officeh |  |
	  | course_description[text] | This is a new description |
	  | course_assessment | 1 |
	  | course_session | 2 |
	  | readingname_option[0] | Title1 |
	  | readingurl_option[0] | Url1 |
	  | knowledge_option[0] | K1 |
	  | skill_option[0] | S1 |
	  | attitude_option[0] | A1 |
	  | teaching_assumption[text] | This is teaching assumption |

