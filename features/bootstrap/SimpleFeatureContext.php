<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode;

class SimpleFeatureContext implements Context, SnippetAcceptingContext {

	/**
	 * @Given a value :value
	 */
	public function aValue($value) {
		return $value;
	}

	/**
	 * @Given values :value1 and :value2
	 */
	public function valuesAnd($value1, $value2) {
		return [$value1, $value2];
	}

	/**
	 * @Then :a should equal :b
	 */
	public function shouldEqual($a, $b) {
		if ($a != $b) {
			throw new \Exception("a ($a) does not equal b ($b)");
		}
	}

	/**
	 * @Then the database should contain:
	 */
	public function theDatabaseShouldContain(PyStringNode $string) {
		throw new \Exception("My context has no way to access the database...");
	}

}
