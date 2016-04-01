@local_metadata @local_metadata_assessment @local_metadata_assessment_edit
Feature: Assessment Tab
	In order to use the assessment tab
	As an instructor
	I need to be able to enter information about an assessment
	
	Background:
        Given the following "courses" exist:
          | fullname | shortname | category |
          | Course 1 | C_shortname | 0 |
        #** Should add some learning objectives and link most to the course.
		And I log in as "admin"
		And I am on homepage
		And I follow "Course 1"
		And I follow "Instructor Moodle Metadata"
		And I follow "Assessment"
		
	
	@javascript
	Scenario: Filling in General Assessment Info
        Given I create 1 assessments with name "Title %s"
        And I set the following fields to these values:
        |assessmentname[0] | "Testdata"|
		|type[0] |Assignment|
        |assessmentprof[0]| "Norris, Chuck" |
        |assessmentweight[0] | 10|
		|description[0]|"TEST"|
		|gdescription[0]|"TESTGDESC"|
        When I press "Save changes"
        Then I wait to be redirected
        Then the following fields match these values:
        |assessmentname[0] | "Testdata"|
		|type[0] |Assignment|
        |assessmentprof[0]| "Norris, Chuck" |
        |assessmentweight[0] | 10|
		|description[0]|"TEST"|
		|gdescription[0]|"TESTGDESC"|