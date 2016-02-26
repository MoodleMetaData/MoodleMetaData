@core @core_admin @eclass @disabledisasters
Feature: An administrator can limit dangerous actions through the GUI based on CFG setting
  In order to en/disable dangerous actions through the GUI
  As an admin
  I need to ensure the $CFG->eclassdisabledisasters variable is set to null/non-null

  Background:
    Given I log in as "admin"

  @javascript
  Scenario: Purge caches from the purge cache page with purge enabled
    Given I append the line "$CFG->eclassdisabledisasters = NULL;" to "config.php"
    And I am on "admin/purgecaches.php"
    Then I should see "Moodle can cache themes"
    And I should see "Purge all caches"
    And "CFG->eclassdisabledisasters" is not set
    When I press "Purge all caches"
    Then I should see "All caches were purged"

  Scenario: Purge caches from the page footer with purge enabled
    Given "CFG->eclassdisabledisasters" is not set
    And I am on "my/"
    And I follow "Purge all caches"
    Then I should see "All caches were purged"
    And "CFG->eclassdisabledisasters" is not set

  Scenario: Try to set maintenance mode while enabled
    Given "CFG->eclassdisabledisasters" is not set
    And I am on "admin/settings.php?section=maintenancemode"
    Then I should see "maintenance_enabled"
    And "CFG->eclassdisabledisasters" is not set

  Scenario: Try search for maintenance mode while enabled
    Given "CFG->eclassdisabledisasters" is not set
    And I am on "my/"
    And I fill in "adminsearchquery" with "maintenance"
    And I press "Search"
    Then I should see "maintenance_enabled"
    And "CFG->eclassdisabledisasters" is not set

  Scenario: Purge caches from the purge cache page with purge disabled
    Given I append the line "$CFG->eclassdisabledisasters = 1;" to "config.php"
    And I am on "admin/purgecaches.php"
    Then "CFG->eclassdisabledisasters" is set
    And I should see "Purge all caches"
    And I should see "Purging caches disabled"
    And I should not see "singlebutton"

  Scenario: Purge caches from the page footer with purge disabled
    Given I am on "my/"
    And I follow "Purge all caches"
    Then I should see "Purging caches disabled"
    And "CFG->eclassdisabledisasters" is set

  Scenario: Try to set maintenance mode while disabled
    Given I am on "admin/settings.php?section=maintenancemode"
    Then I should not see "maintenance_enabled"
    And "CFG->eclassdisabledisasters" is set

  Scenario: Try search for maintenance mode while disabled
    Given I am on "my/"
    And I fill in "adminsearchquery" with "maintenance"
    And I press "Search"
    Then I should not see "maintenance_enabled"
    And "CFG->eclassdisabledisasters" is set
