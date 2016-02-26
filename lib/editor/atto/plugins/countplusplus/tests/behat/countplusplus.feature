@editor @editor_atto @atto @atto_countplusplus
Feature: Atto countplusplus
  To view and count words and or letter in Atto, I need to click word count
  button

  Background: Login, open editor, and show more buttons
    Given I log in as "admin"
    And I Add "statusbar = statusbar, countplusplus" to atto configuration
    And I navigate to "Edit profile" node in "My profile settings"

  @javascript
  Scenario: Basic sentence
    When I set the field "Description" to "<p>Joe Shmoe</p>"
    And I press "Update profile"
    And I navigate to "Edit profile" node in "My profile settings"
    Then I should read "Word count: 2"

  @javascript
  Scenario: Edge case 1, line breaks
    When I set the field "Description" to "<p>Joe Shmoe</p><p>Shmoest guy in town</p>"
    And I press "Update profile"
    And I navigate to "Edit profile" node in "My profile settings"
    Then I should read "Word count: 6"

  @javascript
  Scenario: Edge case 2, line break with dash
    When I set the field "Description" to "<p>These pretzels are mak-</p><p>ing me thirsty</p>"
    And I press "Update profile"
    And I navigate to "Edit profile" node in "My profile settings"
    Then I should read "Word count: 6"

  @javascript
  Scenario: Edge case 3, html entities
    When I set the field "Description" to "<p>I love &#36;&nbsp;more than anything</p>"
    And I press "Update profile"
    And I navigate to "Edit profile" node in "My profile settings"
    Then I should read "Word count: 6"

  @javascript
  Scenario: Edge case 4, this wrt page with multiple atto instance (e.g. quiz editing).
    When I create a course with:
      | Course full name | JoeyShmoey |
      | Course short name | js |
    And I follow "JoeyShmoey"
    And I navigate to "Turn editing on" node in "Course administration"
    And I add a "Database" to section "3" and I fill the form with:
      | Name | Archering |
      | Description | Sterling Malory Archer teaches the art of mastering. |
    And I follow "Archering"
    And I follow "Edit settings"
    Then I should read "Word count: 8"
