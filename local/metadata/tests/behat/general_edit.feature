@local_metadata @local_metadata_general @local_metadata_general_edit
Feature: General tab
	In order to use the general tab
	As an instructor
	I need to be able to enter general course information

	Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C_shortname | 0 |
	And I create the following general info for course "C_shortname":
	  | categoryid | coursedescription | teachingassumption | coursefaculty | assessmentnumber | sessionnumber |
	  | 1 | Description 1 | Teaching assumption 1 | Faculty 1 | 1 | 1 |
	And I create the following instructor info for course "C_shortname" and user "admin":
	  | name | officelocation | officehours | email | phonenumber |
	  | Instructor 1 | Office 1 | W 1:00 pm | instructor@i.com | 111-111-1111 |
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
	
	Scenario: Modifying general course information
	Given I set the following fields to these values:
	  | course_instructor | New instructor |
	  | course_faculty | New faculty |
	When I press "Save general information"
	And I wait to be redirected
	Then the following fields match these values:  
	  | course_instructor | New instructor |
	  | course_faculty | New faculty |

	Scenario: Modifying contact information
	Given I set the following fields to these values:
	  | course_email | newemail@a.com |
	  | course_phone | 777-777-7777 |
	  | course_office | New office |
	  | course_officeh | New hours |
	When I press "Save general information"
	And I wait to be redirected
	Then the following fields match these values:  
	  | course_email | newemail@a.com |
	  | course_phone | 777-777-7777 |
	  | course_office | New office |
	  | course_officeh | New hours |

	Scenario: Modifying course description
	Given I set the field "course_description[text]" to "This is a new description"
	When I press "Save general information"
	And I wait to be redirected
	Then I should see "This is a new description"

	Scenario: Modifying a required reading
	Given I set the field "readingname_option[0]" to "New reading title"
	And I set the field "readingurl_option[0]" to "New reading url"
	When I press "Save general information"
	And I wait to be redirected
	Then the following fields match these values:
	  | readingname_option[0] | New reading title |
	  | readingurl_option[0] | New reading url |
	
	Scenario: Modifying course objectives
	Given I set the following fields to these values:
	  | knowledge_option[0] | New K |
	  | skill_option[0] | New S |
	  | attitude_option[0] | New A |
	When I press "Save general information"
	And I wait to be redirected
	Then the following fields match these values:
	  | knowledge_option[0] | New K |
	  | skill_option[0] | New S |
	  | attitude_option[0] | New A |	
	
	Scenario: Modifying teaching assumption
	Given I set the field "teaching_assumption[text]" to "New teaching assumption"
	When I press "Save general information"
	And I wait to be redirected
	Then I should see "New teaching assumption"	