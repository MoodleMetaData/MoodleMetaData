@local_metadata
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
        Given I am on "local/metadata/insview.php?id=2#tab=1"
        And I set the following fields to these values:
        |assessment_title | "Testdata"|
        |assessment_prof  | "Norris, Chuck" |
        |isexam | "yes"|
        |assessment_duration | "60" |
        |assessment_description | "TESTTESTTESTTEST" |
        When I press "Save Changes"
        Then I wait to be redirected
        And the following fields will match these values:
        |assessment_title | "Testdata"|
        |assessment_prof  | "Norris, Chuck" |
        |isexam | "yes"|
        |assessment_duration | "60" |
        |assessment_description | "TESTTESTTESTTEST" |
	
	
	
	@javascript:
	Scenario: Adding a Knowledge Objective
        Given I press "knowledge_add_fields"
        And I set the following fields to these values:
        |knowledge_text | "Learn to Use Git" |
        When I press "Save changes"
        Then I wait to be redirected
        And the following fields will match these values:
        |knowledge_text| "Learn to Use Git"|
        
		
	@javascript:
	Scenario: Adding a Skills Objective
        Given I press "skills_add_fields"
        And I set the following fields to these values:
        |skills_text[0] | "Learn to Use Git" |
        When I press "Save changes"
        Then I wait to be redirected
        And the following fields will match these values:
        |knowledge_text[0]| "Learn to Use Git"|
	

	
	@javascript:
	Scenario: Adding an Attitudes Objective
        Given I press "attitudes_add_fields"
        And I set the following fields to these values:
        |attitudes_text[0] | "Learn to Use Git" |
        When I press "Save changes"
        Then I wait to be redirected
        And the following fields will match these values:
        |attitudes_text[0]| "Learn to Use Git"|

	
	
	@javascript:
	Scenario: Filling in a Grading Forms
	
	