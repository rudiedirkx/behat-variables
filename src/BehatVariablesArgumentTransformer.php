<?php

namespace rdx\behatvars;

use Behat\Behat\Definition\Call\DefinitionCall;
use Behat\Behat\Transformation\Transformer\ArgumentTransformer;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;

class BehatVariablesArgumentTransformer implements ArgumentTransformer {

	const SLOT_NAME_OPEN = '<<';
	const SLOT_NAME_CLOSE = '>>';
	const SLOT_NAME_REGEX = '#<<([a-z]\w*)>>#i';

	protected $context;
	protected $matches;

	/**
	 *
	 */
	static public function validSlotName($slot) {
		// Empty is allowed, and must be handled by the caller.
		if ($slot === '') {
			return true;
		}

		$slot = self::SLOT_NAME_OPEN . $slot . self::SLOT_NAME_CLOSE;
		return preg_match(self::SLOT_NAME_REGEX, $slot);
	}

	/**
	 *
	 */
	public function supportsDefinitionAndArgument(DefinitionCall $definitionCall, $argumentIndex, $argumentValue) {
		// Check if the given argument is supported and if so if it contains tokens.
		switch (true) {
		  case is_scalar($argumentValue):
		  case $argumentValue instanceof PyStringNode:
		  case $argumentValue instanceof TableNode:
		     return (bool) preg_match_all(self::SLOT_NAME_REGEX, (string) $argumentValue, $this->matches, PREG_SET_ORDER);
		}
		return false;
	}

	/**
	 *
	 */
	public function transformArgument(DefinitionCall $definitionCall, $argumentIndex, $argumentValue) {
		// If this is a NodeTable we need to process every row / column separately.
		if ($argumentValue instanceof TableNode) {
			return $this->transformNodeTableArgument($definitionCall, $argumentIndex, $argumentValue);
		}
		$replacements = array();
		foreach ($this->matches as $match) {
			$replacements[ $match[0] ] = BehatVariablesDatabase::get($match[1]);
		}

		$newArgumentValue = strtr((string) $argumentValue, $replacements);

		if ($argumentValue instanceof PyStringNode) {
			$newArgumentValue = explode("\n", $newArgumentValue);
			$newArgumentValue = new PyStringNode($newArgumentValue, count($newArgumentValue));
		}

		return $newArgumentValue;
	}

	/**
	 * Transforms a whole argument table.
	 *
	 * @param TableNode $argumentValue
	 */
	public function transformNodeTableArgument(DefinitionCall $definitionCall, $argumentIndex, TableNode $argumentValue) {
		$newTableData = $argumentValue->getTable();
		foreach ($newTableData as $i => $row) {
			foreach ($row as $c => $v) {
				// Re-use the scalar replacement function.
				$newTableData[$i][$c] = $this->transformArgument($definitionCall, $i, $v);
			}
		}
		return new TableNode($newTableData);
	}
}
