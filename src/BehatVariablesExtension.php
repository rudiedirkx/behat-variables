<?php

namespace rdx\behatvars;

use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Behat\Testwork\ServiceContainer\ServiceProcessor;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class BehatVariablesExtension implements Extension {

	public function getConfigKey() {
		return 'behatvars';
	}

	public function initialize(ExtensionManager $extensionManager) {

	}

	public function configure(ArrayNodeDefinition $builder) {

	}

	public function load(ContainerBuilder $container, array $config) {
		$loader = new YamlFileLoader($container, new FileLocator(__DIR__));
		$loader->load('services.yml');
	}

	public function process(ContainerBuilder $container) {

	}

}
