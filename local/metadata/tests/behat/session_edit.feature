@local_metadata
Feature: Session tab
  Need to be able to add sessions to the list
  And, this coveres X
  Note, need to add tests for linking of learning objectives and assessments
  
  Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C_shortname | 0 |
    #** Should add some learning objectives and link most to the course.
    And I log in as "admin"
    And I am on homepage
    And I follow "Course 1"
    And I follow "Instructor Moodle Metadata"
    And I follow "Session"
  
  @javascript
  Scenario: Adding one session ensuring that all of the select lists are properly populated
    Then I should not see "Title"
    Given I press "sessions_list_add_element"
    # ***Will need to add more here.....***
    # Test: Displays in right. One doesn't have any items to select from. 
  
  @javascript
  Scenario: Adding one session and filling out all the information
    Given I press "sessions_list_add_element"
    And I set the following fields to these values:
      | sessiontitle[0] | Some title |
      | sessiondescription[0] | Will normally be a long title |
      | sessionguestteacher[0] | Bob the Builder|
      | sessiontype[0] | lab |
    When I press "Save changes session"
    Then I wait to be redirected
    Then the following fields match these values:
      | sessiontitle[0] | Some title |
      | sessiondescription[0] | Will normally be a long title |
      | sessionguestteacher[0] | Bob the Builder|
      | sessiontype[0] | lab |
    
    

  @javascript
  Scenario: Adding two sessions, filling out both. Then delete first before saving. Should have second unchanged, and in [0]
    Given I press "sessions_list_add_element"
    And I set the following fields to these values:
      | sessiontitle[0] | Some title |
      | sessiondescription[0] | Will normally be a long title |
      | sessionguestteacher[0] | Bob the Builder|
      | sessiontype[0] | lab |
    And I press "sessions_list_add_element"
    And I set the following fields to these values:
      | sessiontitle[1] | Other title |
      | sessiondescription[1] | Will be a different description |
      | sessionguestteacher[1] | Bob the Builder2 |
    When I press "deleteSession[0]"
    And I wait to be redirected
    And I press "Save changes session"
    Then I wait to be redirected
    And the following fields match these values:
      | sessiontitle[0] | Second title |
      | sessiondescription[0] | Will be a different description |
      | sessionguestteacher[0] | Bob the Builder2 |
    
  
  @javascript @current
  Scenario: Adding two sessions, filling out both. Save both. Then delete first. Should have second unchanged, and first removed
    Given I press "sessions_list_add_element"
    And I set the following fields to these values:
      | sessiontitle[0] | Some title |
      | sessiondescription[0] | Will normally be a long title |
      | sessionguestteacher[0] | Bob the Builder|
      | sessiontype[0] | lab |
    And I press "sessions_list_add_element"
    And I set the following fields to these values:
      | sessiontitle[1] | Second title |
      | sessiondescription[1] | Will be a different description |
      | sessionguestteacher[1] | Bob the Builder2 |
    And I press "Save changes session"
    And I wait to be redirected
    When I press "deleteSession[0]"
    And I wait to be redirected
    Then the following fields match these values:
      | sessiontitle[0] | Second title |
      | sessiondescription[0] | Will be a different description |
      | sessionguestteacher[0] | Bob the Builder2 |
    