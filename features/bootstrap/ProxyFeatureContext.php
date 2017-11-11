<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

class ProxyFeatureContext implements Context, SnippetAcceptingContext {

	private $phpBin = '';
	private $process;
	private $workingDir = '';



	/**
	 * Initializes context.
	 */
	public function __construct() {

	}



	/**
	 * Cleans test folders in the temporary directory.
	 *
	 * @BeforeSuite
	 * @AfterSuite
	 */
	public static function cleanTestFolders() {
		if (is_dir($dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'behat')) {
			self::clearDirectory($dir);
		}
	}

	/**
	 * Prepares test folders in the temporary directory.
	 *
	 * @BeforeScenario
	 */
	public function prepareTestFolders() {
		$dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'behat' . DIRECTORY_SEPARATOR . uniqid();

		mkdir($dir . '/features/bootstrap/i18n', 0777, true);

		$phpFinder = new PhpExecutableFinder();
		if (false === $php = $phpFinder->find()) {
			throw new \RuntimeException('Unable to find the PHP executable.');
		}
		$this->workingDir = $dir;
		$this->phpBin = $php;
		$this->process = new Process(null);
	}



	/**
	 * @Given /^(?:there is )?a file named "([^"]*)" with:$/
	 */
	public function aFileNamedWith($filename, PyStringNode $content) {
		$content = strtr((string) $content, array("'''" => '"""'));
		$this->createFile($this->workingDir . '/' . $filename, $content);
	}

	/**
	 * Runs behat command with provided parameters
	 *
	 * @When /^I run "behat(?: ((?:\"|[^"])*))?"$/
	 * @When /^I run behat$/
	 *
	 * @param   string $argumentsString
	 */
	public function iRunBehat($argumentsString = '') {
		$argumentsString = strtr($argumentsString, array('\'' => '"'));

		$this->process->setWorkingDirectory($this->workingDir);
		$command = sprintf(
			'%s %s %s %s',
			$this->phpBin,
			escapeshellarg(BEHAT_BIN_PATH),
			$argumentsString,
			strtr('--no-colors --no-snippets --strict --format=progress --format-settings=\'{"timer": false}\'', array(
				'\'' => '"',
				'"' => '\"',
			))
		);
		$this->process->setCommandLine($command);

		// Don't reset the LANG variable on HHVM, because it breaks HHVM itself
		if (!defined('HHVM_VERSION')) {
			$env = $this->process->getEnv();
			$env['LANG'] = 'en'; // Ensures that the default language is en, whatever the OS locale is.
			$this->process->setEnv($env);
		}

		$this->process->start();
		$this->process->wait();
	}

	/**
	 * @Then /^it should (pass|fail) with:$/
	 */
	public function itShouldPassWith($shouldPassFail, PyStringNode $string) {
		$shouldPass = $shouldPassFail == 'pass';
		$passed = $this->getExitCode() === 0;

		$output = trim($this->getOutput());

		$did = $passed ? 'passed' : 'failed';
		$should = $shouldPass ? 'pass' : 'fail';

		if ($this->getExitCode() == (int) $shouldPass) {
			echo $output;
			throw new \Exception("Scenario $did, but should $should");
		}

		if ($this->cleanOutput($output) != $this->cleanOutput($string)) {
			echo $output;
			throw new \Exception("Scenario $did, but with wrong output");
		}
	}



	/**
	 *
	 */
	protected function cleanOutput($output) {
		return preg_replace('#([\r\n]+)[ \t]+#', '$1', trim($output));
	}

	/**
	 *
	 */
	private function getOutput() {
		$output = $this->process->getErrorOutput() . $this->process->getOutput();

		// Normalize the line endings in the output
		if ("\n" !== PHP_EOL) {
			$output = str_replace(PHP_EOL, "\n", $output);
		}

		// Replace wrong warning message of HHVM
		$output = str_replace('Notice: Undefined index: ', 'Notice: Undefined offset: ', $output);

		return trim(preg_replace("/ +$/m", '', $output));
	}

	/**
	 *
	 */
	private function getExitCode() {
		return $this->process->getExitCode();
	}

	/**
	 *
	 */
	private function createFile($filename, $content) {
		$path = dirname($filename);
		if (!is_dir($path)) {
			mkdir($path, 0777, true);
		}

		file_put_contents($filename, $content);
	}

	/**
	 *
	 */
	private static function clearDirectory($path) {
		$files = scandir($path);
		array_shift($files);
		array_shift($files);

		foreach ($files as $file) {
			$file = $path . DIRECTORY_SEPARATOR . $file;
			if (is_dir($file)) {
				self::clearDirectory($file);
			} else {
				unlink($file);
			}
		}

		rmdir($path);
	}

}
