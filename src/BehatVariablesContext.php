<?php

namespace rdx\behatvars;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Hook\Scope\AfterFeatureScope;
use Behat\Behat\Hook\Scope\AfterStepScope;
use rdx\behatvars\BehatVariablesArgumentTransformer;

class BehatVariablesContext implements Context, SnippetAcceptingContext {

	protected $lastResult = [];

	static protected $storage = array();

	/**
	 * Initializes context.
	 */
	public function __construct() {

	}



	/**
	 * @AfterStep
	 */
	public function afterStep(AfterStepScope $scope) {
		$this->lastResult = [];

		$result = $scope->getTestResult();
		if (is_callable(array($result, 'getCallResult'))) {
			$result = $result->getCallResult();
			if (is_callable(array($result, 'getReturn'))) {
				$result = $result->getReturn();
				if ($result !== null) {
					$this->lastResult = is_array($result) && isset($result[0]) ? array_values($result) : [$result];
				}
			}
		}
	}

	/**
	 * @AfterFeature
	 */
	static public function afterFeature(AfterFeatureScope $scope) {
		self::storageClear();
	}



	/**
	 * @When /^(?:I|we) save (?:it|that|those|them) into "([\w,]+)"$/
	 */
	public function saveItInto($slot) {
		if (!$this->lastResult) {
			throw new \Exception("Can't store empty return value. Have a step method return a value.");
		}

		$slots = explode(',', $slot);
		if (count($slots) != count($this->lastResult)) {
			$slots = count($slots);
			$results = count($this->lastResult);
			throw new \Exception("Number of slots ($slots) does not match number of last results ($results).");
		}

		$valids = array_filter($slots, [BehatVariablesArgumentTransformer::class, 'validSlotName']);
		if ($valids !== $slots) {
			throw new \Exception("Invalid slot name(s) in '$slot'");
		}

		foreach ($slots as $index => $slot) {
			$value = $this->lastResult[$index];
			$this->storageSet($slot, $value);
		}

		$this->lastResult = [];
	}



	/**
	 *
	 */
	protected function storageSet($name, $value) {
		if (!is_scalar($value)) {
			$type = gettype($value);
			throw new \Exception("Storing value must be scalar, but it's a '$type'.");
		}

		self::$storage[$name] = $value;
	}

	/**
	 *
	 */
	public function storageGet($name) {
		if (!isset(self::$storage[$name])) {
			throw new \Exception("Value for '$name' does not exist.");
		}

		return self::$storage[$name];
	}

	/**
	 *
	 */
	static protected function storageClear() {
		self::$storage = array();
	}

}
