@mod @mod_tab @mod_tab_menu
Feature: Tab menu in tab display

  @javascript
  Scenario: Two tabs with same menu name will show the tab menu if displaymenu is enabled in one of them.
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
      | id_menuname    | menu_name |
      | tabname[0]     | Buck naked |
      | content[0][text] | name if he works in some specific industry. |
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
    And I wait until the page is ready
    Then I should see "Buck naked"
    And "//*[@id='mod-tab-content']//*[@id='mod-tab-tab-set']//div[@class='tab-content']//*[text()='name if he works in some specific industry.']" "xpath_element" should exist

      # Confirm that tab menu is shown.
    Then "//div[@id='mod-tab-side-menu']//*[text()='GeorgeCostanzaTab']" "xpath_element" should be visible
    And "//div[@id='mod-tab-side-menu']//*[text()='LarryDavidTab']" "xpath_element" should be visible

    # Let us go to the second tab (LarryDavidTab) and confirm things are working their too!!.
    Then I click on "//div[@id='mod-tab-side-menu']//*[text()='LarryDavidTab']" "xpath_element"

    # Confirm that the usual content is here, which is the "LarryDavidTab"
    Then I should see "I'm George!!"
    And "//*[@id='mod-tab-content']//*[@id='mod-tab-tab-set']//div[@class='tab-content']//*[text()='Failed at emulating George Scene.']" "xpath_element" should exist

  @javascript
  Scenario: Two tabs with same menu name will not show the tab menu in the tab module with tabmenu disabled.
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

    # Add two tabs this time. And enable display_menu for only larry david's.
    And I add a "tab" to section "1" and I fill the form with:
      | Name        | GeorgeCostanzaTab     |
      | id_displaymenu | false |
      | id_menuname    | menu_name |
      | tabname[0]     | Buck naked |
      | content[0][text] | name if he works in some specific industry. |
      | id_taborder      | 0                                           |
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
    And "//*[@id='mod-tab-content']//*[@id='mod-tab-tab-set']//div[@class='tab-content']//*[text()='name if he works in some specific industry.']" "xpath_element" should exist

    # Confirm that tab menu is NOT shown.
    Then "//div[@id='mod-tab-side-menu']//*[text()='GeorgeCostanzaTab']" "xpath_element" should not be visible
    And "//div[@id='mod-tab-side-menu']//*[text()='LarryDavidTab']" "xpath_element" should not be visible
    And "//div[@id='mod-tab-side-menu']" "xpath_element" should not be visible