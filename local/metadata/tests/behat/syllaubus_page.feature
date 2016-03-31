@local_metadata @local_metadata_syllabus @aaa
Feature: Syllabus tab
	In order to use the Syllabus tab
	As an instructor
	I need to be able to click the download and preview button in the syllabus page
  
  Background:
    Given I log in as "admin"
    And I am on homepage
    And I follow "Course 1"
    And I follow "Instructor Moodle Metadata"
    Then I follow "Session"
    

  Scenario: Download the generated pdf format of syllabus
  	When I press "syllubusdownload"
  	And I wait to be redirected
 	Then I should see "syllubusdisplay"
 	Then I should see "syllubusdownload"
 
  Scenario: Display the previes of the generated pdf format of syllabus
  	When I press "syllubusdisplay"
  	And I wait to be redirected
  	Then I should see "syllubusdisplay"
  	Then I should see "syllubusdownload"