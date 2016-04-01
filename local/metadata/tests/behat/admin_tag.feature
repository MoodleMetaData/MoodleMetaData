@local_metadata @local_metadata_admin @local_metadata_admin_tag
Feature: Administrator policy tab
	In order to use the tag tab
	As an administrator
	I need to be able to tag program objectives to course objectives

  Background:
  	Given the following "courses" exist:
      | fullname | shortname | category | summary |
      | Course 1 | C_shortname | 0 | test |
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
	And the default program objectives exist
    And I log in as "admin"
    And I am on homepage
    And I expand "Site administration" node
    And I follow "Manage Metadata"
	And I follow "Tags"

	@javascript
	Scenario: Tagging program objective to course objective
	Given I press "admselect_course"
	Then I press "admselcourse"
	And I press "groupsel"
	And I set the field "admpro_select[]" to "Parent"
	And I press "admaddobjective"
	Then the "admpro_current[]" select box should contain "Parent"

	@javascript
	Scenario: Removing program objective tag
	Given I press "admselect_course"
	Then I press "admselcourse"
	And I press "groupsel"
	And I set the field "admpro_select[]" to "Parent"
	And I press "admaddobjective"
	And I set the field "admpro_current[]" to "Parent"
	And I press "admdelobjective"
	Then the "admpro_current[]" select box should not contain "Parent"
	