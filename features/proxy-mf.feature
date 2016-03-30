Feature: Proxy multiple features

	Background:
		Given a file named "behat.yml" with:
			"""
			default:
			  suites:
			    default:
			      contexts:
			        - rdx\behatvars\BehatVariablesContext
			        - FeatureContext
			  extensions:
			      rdx\behatvars\BehatVariablesExtension: ~
			"""
		And a file named "features/bootstrap/FeatureContext.php" with:
			"""
			<?php

			use Behat\Behat\Context\Context;
			use Behat\Behat\Context\SnippetAcceptingContext;

			class FeatureContext implements Context, SnippetAcceptingContext {
			  /** @Given a value :value */
			  public function aValue($value) {
			    return $value;
			  }

			  /** @Then :value1 should equal :value2 */
			  public function shouldEqual($value1, $value2) {
			    if ($value1 != $value2) {
			      throw new \Exception("value1 ($value1) does not equal value2 ($value2)");
			    }
			  }
			}
			"""
		And   a file named "features/a.feature" with:
			"""
			Feature: Remember a variable

			  Scenario: Remember a variable
			    Given a value "50"
			    And I save it into "X"
			    Then "<<X>>" should equal "50"

			  Scenario: Still remember a variable
			    Then "<<X>>" should equal "50"
			"""
		And   a file named "features/b.feature" with:
			"""
			Feature: Don't remember a variable

			  Scenario: Don't remember a variable
			    Then "<<X>>" should equal "50"

			  Scenario: Still CAN remember a variable
			    Given a value "50"
			    And I save it into "X"
			    Then "<<X>>" should equal "50"
			"""

	Scenario: Don't remember a variable
		When I run behat
		Then it should fail with:
			"""
			....F...

			--- Failed steps:

			  Then "<<X>>" should equal "50" # features/b.feature:4
			    'X' does not exist. (Exception)

			4 scenarios (3 passed, 1 failed)
			8 steps (7 passed, 1 failed)
			"""
