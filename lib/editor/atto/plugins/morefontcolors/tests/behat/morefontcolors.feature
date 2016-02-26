@editor @editor_atto @atto @atto_morefontcolors
Feature: Atto morefontcolors
  As a user, I should be able to have a compact drop down color palette.

  Background: Login, open editor, and show more buttons.
    Given I log in as "admin"
    And I Add "morefontcolors = morefontcolors" to atto configuration
    And I navigate to "Edit profile" node in "My profile settings"
    And I click on "Show more buttons" "button"


  @javascript
  Scenario: Default color pallete should show up.
    When I click on "More font colours" "button"

    # Check if the color palette is visible.
    And There should be a visible "div.open.atto_morefontcolors_button.atto_menu div a" with background-color "#FFFFFF"
    And There should be a visible "div.open.atto_morefontcolors_button.atto_menu div a" with background-color "#EF4540"
    And There should be a visible "div.open.atto_morefontcolors_button.atto_menu div a" with background-color "#FFCF35"
    And There should be a visible "div.open.atto_morefontcolors_button.atto_menu div a" with background-color "#98CA3E"
    And There should be a visible "div.open.atto_morefontcolors_button.atto_menu div a" with background-color "#7D9FD3"
    And There should be a visible "div.open.atto_morefontcolors_button.atto_menu div a" with background-color "#333333"

    # Try Hide the more font colours.
    Then I click on "More font colours" "button"

    # Check if the color palette is invisible. This should fail when other plugins (including this) have a bug.
    And There should NOT be a visible "div.open.atto_morefontcolors_button.atto_menu div a" with background-color "#FFFFFF"
    And There should NOT be a visible "div.open.atto_morefontcolors_button.atto_menu div a" with background-color "#EF4540"
    And There should NOT be a visible "div.open.atto_morefontcolors_button.atto_menu div a" with background-color "#FFCF35"
    And There should NOT be a visible "div.open.atto_morefontcolors_button.atto_menu div a" with background-color "#98CA3E"
    And There should NOT be a visible "div.open.atto_morefontcolors_button.atto_menu div a" with background-color "#7D9FD3"
    And There should NOT be a visible "div.open.atto_morefontcolors_button.atto_menu div a" with background-color "#333333"

  @javascript
  Scenario: Basic function
    When I set the field "Description" to "Such test. Very best practice, Wow. Much reading Code Complete."
    And I select the text in the "Description" Atto editor
    And I click on "More font colours" "button"
    And I click on "div.open.atto_morefontcolors_button.atto_menu div a[style*='#EF4540']" "css_element"
    And I click on "div.editor_atto_content" "css_element"
    And There should be a visible "div.editor_atto_content span" with color "#EF4540" containing text:
    """
    Such test. Very best practice, Wow. Much reading Code Complete.
    """

  @javascript
  Scenario: Test to bug-fix LMS-586. Before I lay out the test, I will first elaborate the bug:
  Without going to further details, the column_count is acquired. Given a set of colors, we take a column_count
  for each row. The bug was I forgot to account for the remaining colors, specifically #colors % column_count.
  To test this bug, we simply need #colors and column_count such that #colors % column_count !== 1, and ensure
  that all of our colors are present.

    # These 7 colors should suggest ceiling(sqrt(7)) = 3 column, and maximum of 3 rows. The last row was not being
    # shown prior to the bug-fix.
    When I set the configuration of atto_morefontcolors to:
    """
    #FFFFFF
    #EF4540
    #FFCF35
    #98CA3E
    #7D9FD3
    #333333
    #D06E11
    """
    And I navigate to "Edit profile" node in "My profile settings"
    And I click on "Show more buttons" "button"

    # Try Hide the more font colours.
    Then I click on "More font colours" "button"

    # Check if the color palette is invisible. This should fail when other plugins (including this) have a bug.
    And There should be a visible "div.open.atto_morefontcolors_button.atto_menu div a" with background-color "#FFFFFF"
    And There should be a visible "div.open.atto_morefontcolors_button.atto_menu div a" with background-color "#EF4540"
    And There should be a visible "div.open.atto_morefontcolors_button.atto_menu div a" with background-color "#FFCF35"
    And There should be a visible "div.open.atto_morefontcolors_button.atto_menu div a" with background-color "#98CA3E"
    And There should be a visible "div.open.atto_morefontcolors_button.atto_menu div a" with background-color "#7D9FD3"
    And There should be a visible "div.open.atto_morefontcolors_button.atto_menu div a" with background-color "#333333"
    And There should be a visible "div.open.atto_morefontcolors_button.atto_menu div a" with background-color "#D06E11"