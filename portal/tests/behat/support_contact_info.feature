@eclass_portal
Feature: Display up to date contact information on
  the portal page.

  Scenario: Unauthenticated access to portal page
    Given I am on portal
    Then I should see "Contact eClass Support" in the "#support-contact" "css_element"
    Then I should see "3-104 Education North" in the "#support-contact" "css_element"
    Then I should see "11210 - 87 Ave" in the "#support-contact" "css_element"
    Then I should see "Edmonton AB T6G 2G5" in the "#support-contact" "css_element"
    And I should not see "Contact CTL" in the "#support-contact" "css_element"
    And I should not see "TELUS Centre, Rooms 133 &amp; 140" in the "#support-contact" "css_element"
    And I should not see "University of Alberta  T6G 2R1" in the "#support-contact" "css_element"

