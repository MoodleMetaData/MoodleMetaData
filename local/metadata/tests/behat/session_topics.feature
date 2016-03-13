@local_metadata @local_metadata @topic
Feature: Session tab
	In order to use the session tab
	As an instructor
	I need to be able to enter information about an session
  
  Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C_shortname | 0 |    
    And I log in as "admin"
    And I am on homepage
    And I follow "Course 1"
    And I follow "Instructor Moodle Metadata"
    Then I follow "Session"
    
  
  @javascript
  Scenario: Adding and deleting topics for an existing session, with saved and unsaved topic deleted topics
    Given I create 1 sessions
    And I set the field "sessiontitle[0]" to "Title"
    And I add to session 0 topic "Undeleted"
    And I add to session 0 topic "Saved Deleted Topic"
    And I press "Save changes"
    And I add to session 0 topic "Unsaved Deleted Topic"
    Then the "all_topics[0][]" select box should contain "Undeleted"
    And the "all_topics[0][]" select box should contain "Saved Deleted Topic"
    And the "all_topics[0][]" select box should contain "Unsaved Deleted Topic"
    When I set the field "all_topics[0][]" to "Saved Deleted Topic, Unsaved Deleted Topic"
    And I press "delete_topics[0]"
    Then the "all_topics[0][]" select box should contain "Undeleted"
    And the "all_topics[0][]" select box should not contain "Saved Deleted Topic"
    And the "all_topics[0][]" select box should not contain "Unsaved Deleted Topic"
    And I press "Save changes"
    And the "all_topics[0][]" select box should contain "Undeleted"
    And the "all_topics[0][]" select box should not contain "Saved Deleted Topic"
    And the "all_topics[0][]" select box should not contain "Unsaved Deleted Topic"
    And the "all_topics[0][]" select box should not contain "Saved Deleted Topic"
    And the field "sessiontitle[0]" matches value "Title"
  
  @javascript
  Scenario: Adding and deleting topics for a new session. Then save and ensure stays
    Given I create 1 sessions
    And I set the field "sessiontitle[0]" to "Title"
    And I add to session 0 topic "Undeleted"
    And I add to session 0 topic "Unsaved Deleted Topic"
    Then the "all_topics[0][]" select box should contain "Undeleted"
    And the "all_topics[0][]" select box should contain "Unsaved Deleted Topic"
    When I set the field "all_topics[0][]" to "Unsaved Deleted Topic"
    And I press "delete_topics[0]"
    Then the "all_topics[0][]" select box should contain "Undeleted"
    And the "all_topics[0][]" select box should not contain "Unsaved Deleted Topic"
    When I press "Save changes"
    Then the "all_topics[0][]" select box should contain "Undeleted"
    And the "all_topics[0][]" select box should not contain "Unsaved Deleted Topic"
    And the field "sessiontitle[0]" matches value "Title"
  
  @javascript
  Scenario: Pressing delete when nothing was selected for session, or pressing add when nothing was entered for topic
    Given I create 1 sessions
    And I set the field "sessiontitle[0]" to "Title"
    And I add to session 0 topic "Undeleted"
    And I press "delete_topics[0]"
    And I press "Save changes"
    Then the "all_topics[0][]" select box should contain "Undeleted"
    And the field "sessiontitle[0]" matches value "Title"