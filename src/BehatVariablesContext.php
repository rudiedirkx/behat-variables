<?php

namespace rdx\behatvars;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Hook\Scope\AfterFeatureScope;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Behat\Tester\Result\ExecutedStepResult;
use rdx\behatvars\BehatVariablesArgumentTransformer;
use rdx\behatvars\BehatVariablesDatabase;

class BehatVariablesContext implements Context, SnippetAcceptingContext {

	protected $lastResult = [];



	/**
	 * @AfterStep
	 */
	public function afterStep(AfterStepScope $scope) {
		$this->lastResult = [];

		$result = $scope->getTestResult();
		if ($result instanceof ExecutedStepResult) {
			$result = $result->getCallResult()->getReturn();
			if ($result !== null) {
				$this->lastResult = is_array($result) && isset($result[0]) ? array_values($result) : [$result];
			}
		}
	}

	/**
	 * @AfterFeature
	 */
	static public function afterFeature(AfterFeatureScope $scope) {
		BehatVariablesDatabase::clear();
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
			BehatVariablesDatabase::set($slot, $value);
		}

		$this->lastResult = [];
	}

}
