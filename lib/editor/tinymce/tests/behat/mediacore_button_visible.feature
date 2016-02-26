@editor @editor_tinymce @eclass_multi_mediacore
Feature: Check that the mediacore button is active and visible to the users.

  Background:
    Given the following "courses" exists:
      | fullname | shortname | category |
      | Course 1 | C1 | 0 |
    And I log in as "admin"


  @javascript
  Scenario: Check TinyMCE
    Given I am on homepage
    And I follow "Course 1"
    And I turn editing mode on
    When I add a "Database" to section "1"
    And I wait until "#id_introeditor_tbl" "css_element" exists
    And I press "Toolbar Toggle"
    And I wait until ".mce_search" "css_element" exists
    And ".mce_mediacore" "css_element" should exists

