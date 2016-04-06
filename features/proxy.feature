@proxy
Feature: Proxy features to test fails

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

			  /** @Given values :value1 and :value2 */
			  public function valuesAnd($value1, $value2) {
			    return [$value1, $value2];
			  }

			  /** @Then :value1 should equal :value2 */
			  public function shouldEqual($value1, $value2) {
			    if ($value1 != $value2) {
			      throw new \Exception("value1 ($value1) does not equal value2 ($value2)");
			    }
			  }
			}
			"""
		And a file named "features/test.feature" with:
			"""
			Feature: Remember a variable

			  Scenario: Remember a variable
			    Given a value "50"
			    And I save it into "X"
			    Given a value "51"
			    And I save it into "y"
			    Then "<<X>>" should equal "50"
			    And "<<y>>" should equal "51"
			"""

	Scenario: Forgetting BehatVariablesExtension
		Given a file named "behat.yml" with:
			"""
			default:
			  suites:
			    default:
			      contexts:
			        - rdx\behatvars\BehatVariablesContext
			        - FeatureContext
			"""
		When I run behat
		Then it should fail with:
			"""
			....F-

			--- Failed steps:

			  Then "<<X>>" should equal "50" # features/test.feature:8
			    value1 (<<X>>) does not equal value2 (50) (Exception)

			1 scenario (1 failed)
			6 steps (4 passed, 1 failed, 1 skipped)
			"""

	Scenario: Forgetting BehatVariablesContext
		Given a file named "behat.yml" with:
			"""
			default:
			  suites:
			    default:
			      contexts:
			        - FeatureContext
			  extensions:
			      rdx\behatvars\BehatVariablesExtension: ~
			"""
		When I run behat
		Then it should fail with:
			"""
			.U-U--

			1 scenario (1 undefined)
			6 steps (1 passed, 2 undefined, 3 skipped)
			"""

	Scenario: Remember a variable
		When I run behat
		Then it should pass with:
			"""
			......

			1 scenario (1 passed)
			6 steps (6 passed)
			"""

	Scenario: Invalid slot names
		Given a file named "features/test.feature" with:
			"""
			Feature: Remember a variable

			  Scenario: Remember a variable
			    Given a value "50"
			    And I save it into "123abc"
			    And a value "51"
			    And I save it into "=="
			    And a value "52"
			    And I save it into "<<abc>>"
			"""
		When I run behat
		Then it should fail with:
			"""
			.F----

			--- Failed steps:

			  And I save it into "123abc" # features/test.feature:5
			    Invalid slot name(s) in '123abc'. Beware the white space! (Exception)

			1 scenario (1 failed)
			6 steps (1 passed, 1 failed, 4 skipped)
			"""

	Scenario: Invalid slot names due to spaces
		Given a file named "features/test.feature" with:
			"""
			Feature: Remember a variable

			  Scenario: Remember a variable
			    Given values "50" and "51"
			    And I save them into "X, Y"
			    Then "<<X>>" should equal "50"
			    And "<<Y>>" should equal "51"
			"""
		When I run behat
		Then it should fail with:
			"""
			.F--

			--- Failed steps:

			  And I save them into "X, Y" # features/test.feature:5
			    Invalid slot name(s) in 'X, Y'. Beware the white space! (Exception)

			1 scenario (1 failed)
			4 steps (1 passed, 1 failed, 2 skipped)
			"""
