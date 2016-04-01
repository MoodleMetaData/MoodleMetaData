@local_metadata @local_metadata_general @local_metadata_general_delete
Feature: General tab
	In order to use the general tab
	As an instructor
	I need to be able to delete optional general course information.

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
	And I create the following general info for course "C_shortname":
	  | teachingassumption | courseterm | courseyear | assessmentnumber | sessionnumber | coursedescription |
	  | Teaching assumption here | Spring | 2018 | 1 | 1 | Description here |
	And I create the following instructor info for course "C_shortname" and user "admin":
	  | name | officelocation | officehours | email | phonenumber |
	  | Instructor 1 | Office 1 | By appointment | instructor@i.com | 111-111-1111 |
	And I create the following required readings for course "C_shortname":
	  | readingname | readingurl |
	  | Reading A | http://a.com |
	  | Reading B | http://b.com |
	And I create the following learning objectives for course "C_shortname":
      | objectivename | objectivetype |
      | A 1 | Attitude |
      | K 1 | Knowledge |
      | S 1 | Skill |
    And I log in as "admin"
    And I am on homepage
    And I follow "Course 1"
    And I follow "Instructor Moodle Metadata"
    Then I follow "General"
	
	Scenario: Deleting a required reading before saving
	Given I press "delete_req_reading[0]"
	Then "readingname_option[0]" "text" should not exist
	
	Scenario: Deleting the phone number after saving
	Given I set the field "course_phone" to ""
	When I press "Save general information"
	And I wait to be redirected
	Then I should see ""
	
	Scenario: Deleting a required reading after saving
	Given I set the field "readingname_option[0]" to ""
	When I press "Save general information"
	And I wait to be redirected
	Then "readingname_option[0]" "text" should not exist
	
	Scenario: Deleting a course objective: knowledge after saving
	Given I set the field "knowledge_option[0]" to ""
	When I press "Save general information"
	And I wait to be redirected
	Then "knowledge_option[0]" "text" should not exist
	
	Scenario: Deleting a course objective: skill after saving
	Given I set the field "skill_option[0]" to ""
	When I press "Save general information"
	And I wait to be redirected
	Then "skill_option[0]" "text" should not exist
	
	Scenario: Deleting a course objective: attitude after saving
	Given I set the field "attitude_option[0]" to ""
	When I press "Save general information"
	And I wait to be redirected
	Then "attitude_option[0]" "text" should not exist
	
	Scenario: Deleting the teaching assumption after saving
	Given I set the field "teaching_assumption[text]" to ""
	When I press "Save general information"
	And I wait to be redirected
	Then I should see ""
