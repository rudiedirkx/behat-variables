<?php

namespace rdx\behatvars;

use Behat\Behat\Definition\Call\DefinitionCall;
use Behat\Behat\Transformation\Transformer\ArgumentTransformer;
use rdx\behatvars\BehatVariablesContext;

class BehatVariablesArgumentTransformer implements ArgumentTransformer {

	const SLOT_NAME_OPEN = '<<';
	const SLOT_NAME_CLOSE = '>>';
	const SLOT_NAME_REGEX = '#<<(\w+)>>#i';

	protected $context;
	protected $matches;

	/**
	 *
	 */
	static public function validSlotName($slot) {
		$slot = self::SLOT_NAME_OPEN . $slot . self::SLOT_NAME_CLOSE;
		return preg_match(self::SLOT_NAME_REGEX, $slot);
	}

	/**
	 *
	 */
	public function supportsDefinitionAndArgument(DefinitionCall $definitionCall, $argumentIndex, $argumentValue) {
		return
			is_string($argumentValue) &&
			($this->context = $this->getContext($definitionCall)) &&
			preg_match_all(self::SLOT_NAME_REGEX, $argumentValue, $this->matches, PREG_SET_ORDER);
	}

	/**
	 *
	 */
	public function transformArgument(DefinitionCall $definitionCall, $argumentIndex, $argumentValue) {
		$replacements = array();
		foreach ($this->matches as $match) {
			$replacements[ $match[0] ] = $this->context->storageGet($match[1]);
		}

		return strtr($argumentValue, $replacements);
	}

	/**
	 *
	 */
	protected function getContext(DefinitionCall $definitionCall) {
		$contexts = $definitionCall->getEnvironment()->getContexts();
		foreach ($contexts as $context) {
			if ($context instanceof BehatVariablesContext) {
				return $context;
			}
		}
	}

}
