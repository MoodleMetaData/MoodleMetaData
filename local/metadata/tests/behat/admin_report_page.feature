@local_metadata  @local_metadata_admin @local_metadata_admin_report
Feature: Administrator Reporting tab
	In order to be able to download Reports of course and program objectives
	As an administrator
	I need to be able to download and preview the pdf format of reports
	and download the csv format of reports
	
  Background:
    Given I log in as "admin"
    And I am on homepage
    And I expand "Category administration" node
	And I follow "My categories"
	And I follow "Miscellaneous"
    And I follow "Manage Metadata"
    And I follow "Reporting"
    
  @javascript
  Scenario: display the preview of program objective report in pdf format
    When I press "poreportdisplay"
  	And I wait to be redirected
  	
  @javascript
  Scenario: download the pdf format report of program objectives
    When I press "poreportdownload"
  	And I wait to be redirected
  	
  @javascript
  Scenario: download the csv format report of program objectives
    When I press "poreportcsv"
  	And I wait to be redirected
  		
  @javascript
  Scenario: download the csv format report of course
    When I press "coursereportcsv"
  	And I wait to be redirected