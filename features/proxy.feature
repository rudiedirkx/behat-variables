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

			  /** @Then :value1 should equal :value2 */
			  public function shouldEqual($value1, $value2) {
			    if ($value1 != $value2) {
			      throw new \Exception("value1 ($value1) does not equal value2 ($value2)");
			    }
			  }
			}
			"""
		And   a file named "features/test.feature" with:
			"""
			Feature: Remember a variable

			  Scenario: Remember a variable
			    Given a value "50"
			    And I save it into "X"
			    Then "<<X>>" should equal "50"
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
			..F

			--- Failed steps:

			  Then "<<X>>" should equal "50" # features/test.feature:6
			    value1 (<<X>>) does not equal value2 (50) (Exception)

			1 scenario (1 failed)
			3 steps (2 passed, 1 failed)
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
			.U-

			1 scenario (1 undefined)
			3 steps (1 passed, 1 undefined, 1 skipped)

			--- FeatureContext has missing steps. Define them with these snippets:

			  /**
			   * @Given I save it into :arg1
			   */
			  public function iSaveItInto($arg1)
			  {
			    throw new PendingException();
			  }
			"""

	Scenario: Remember a variable
		When I run behat
		Then it should pass with:
			"""
			...

			1 scenario (1 passed)
			3 steps (3 passed)
			"""
