<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use rdx\behatvars\BehatVariablesDatabase;

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
	 * @Given values :value1 and :value2 and :value3
	 */
	public function valuesAndAnd($value1, $value2, $value3) {
		return [$value1, $value2, $value3];
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
		// Convert database to TableNode, to compare against test string
		$table = array();
		foreach (BehatVariablesDatabase::all() as $name => $value) {
			$table[] = array($name, $value);
		}

		$table = new TableNode($table);
		$table = trim($table);

		if (trim($string) != $table) {
			throw new \Exception("Database contents are wrong:\n$table");
		}
	}

	/**
	 * @Then set into :variable variable text:
	 */
	public function setIntoVariableText($variable, PyStringNode $string) {
		BehatVariablesDatabase::set($variable, (string) $string);
	}
}
