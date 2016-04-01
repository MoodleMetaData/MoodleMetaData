@local_metadata @local_metadata_syllabus
Feature: Syllabus tab
	In order to use the Syllabus tab
	As an instructor
	I need to be able to click the download and preview button in the syllabus page
  
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
    Then I follow "Syllabus"
  
  @javascript
  Scenario: Download the generated pdf format of syllabus
  	When I press "syllubusdownload"
  	And I wait to be redirected