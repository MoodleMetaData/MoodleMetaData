@mod @mod_tab @mod_tab_single
Feature: Tab display feature

  @javascript
  Scenario: As a user, I create a tab display activity and add a tab display.
  I should be able to view it in the end.
    Given the following "users" exist:
      | username | firstname | lastname | email   | idnumber |
      | teacher1 | Teacher | 1 | teacher1@asd.com | teacher1|
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1 | 0 |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |

    Given I log in as "teacher1"
    And I am on homepage
    And I follow "Course 1"
    And I turn editing mode on

    And I add a "tab" to section "1" and I fill the form with:
      | Name        | GeorgeCostanzaTab     |

    And I am on homepage
    And I follow "Course 1"
    And I follow "GeorgeCostanzaTab"

    # There should be nothing in here, just the header.
    Then I should see "GeorgeCostanzaTab"
    And "#mod-tab-content #mod-tab-tab-set div.tab-content > .tab-pane" "css_element" should not exist

    # Let's add something.
    Then I press "mod_tab_update_this"
    And I set the field "tabname[0]" to "Many moods, many shades"
    And I set the field "content[0][text]" to "morning mist"
    And I press "Save and display"

    # There should be something here now.
    Then I should see "Many moods, many shades"
    And "#mod-tab-content #mod-tab-tab-set div.tab-content > .tab-pane" "css_element" should exist
    And I should see "morning mist"

    # Let's add another tab so we can play with switching tabs.
    Then I press "mod_tab_update_this"
    And I set the field "tabname[1]" to "Art Vandelay"
    And I set the field "content[1][text]" to "I'm not an architect?"
    And I press "Save and display"

    # We should first see the first tab.
    Then I should see "Many moods, many shades"
    And "#mod-tab-content #mod-tab-tab-set div.tab-content > .tab-pane" "css_element" should exist
    And I should see "morning mist"

    # Let's switch to the next tab.
    And I click on "//a[text()='Art Vandelay']" "xpath_element"
    And I should see "I'm not an architect?"

  @javascript
  Scenario: As a user, two tab sets (tabs from different module) are in a single page when tab menu is enabled.
    Given the following "users" exist:
      | username | firstname | lastname | email   | idnumber |
      | teacher1 | Teacher | 1 | teacher1@asd.com | teacher1|
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1 | 0 |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |

    Given I log in as "teacher1"
    And I am on homepage
    And I follow "Course 1"
    And I turn editing mode on

    # Add two tabs this time. And enable display_menu for both of them.
    And I add a "tab" to section "1" and I fill the form with:
      | Name        | GeorgeCostanzaTab     |
      | id_displaymenu | true |
      | id_displaymenu | true |
      | id_menuname    | menu_name |
      | tabname[0]     | Buck naked |
      | content[0][text] | Georgy's name if he works in some specific industry. |
      | id_taborder      | 0                                                    |
    And I am on homepage
    And I follow "Course 1"
    And I add a "tab" to section "2" and I fill the form with:
      | Name        | LarryDavidTab         |
      | id_displaymenu | true |
      | id_menuname    | menu_name |
      | tabname[0]     | I'm George!! |
      | content[0][text] | Failed at emulating George Scene. |
      | id_taborder      | 1                                 |

    And I am on homepage
    And I follow "Course 1"
    And I follow "GeorgeCostanzaTab"

    # Confirm that the usual content is here, which is the "GeorgeCostanzaTab"
    Then I should see "Buck naked"
    And "#mod-tab-content #mod-tab-tab-set div.tab-content > .tab-pane" "css_element" should exist
    And I should see "Georgy's name if he works in some specific industry."

    # Confirm that tab menu is shown.
    Then "//div[@id='mod-tab-side-menu']//*[text()='GeorgeCostanzaTab']" "xpath_element" should be visible
    Then "//div[@id='mod-tab-side-menu']//*[text()='LarryDavidTab']" "xpath_element" should be visible