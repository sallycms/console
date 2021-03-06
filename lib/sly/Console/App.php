<?php
/*
 * Copyright (c) 2016, webvariants GbR, http://www.webvariants.de
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

namespace sly\Console;

use sly_App_Interface;
use sly_Container;
use sly_Core;
use sly_I18N;
use sly_Util_YAML;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use wv\BabelCache\Cache\Blackhole;

class App implements sly_App_Interface {
	protected $container;
	protected $root;
	protected $input;
	protected $output;
	protected $console;
	protected $unusedCache;

	public function __construct(sly_Container $container, $rootDir) {
		$this->container = $container;
		$this->root      = realpath($rootDir);
	}

	public function initialize() {
		$container = $this->getContainer();
		$config    = $container->getConfig();

		// init the current language
		$container->setCurrentLanguageId($config->get('default_clang_id'));

		// load static config
		$config->setStatic('/', sly_Util_YAML::load($this->root.'/config/static.yml'));

		// check whether the cache is available on CLI (APC for example is not)
		$this->initCache();

		// init timezone
		date_default_timezone_set($config->get('timezone', 'UTC'));

		// ... and locale
		$container->setI18N(new sly_I18N('en_gb', $this->root.'/lang'));

		// inject a no-op session, since $_SESSION is not defined in CLI
		$container['sly-session'] = new NullSession();

		// boot addOns, but only if the system has already been installed
		if ($config->get('setup', true) === false) {
			sly_Core::loadAddOns();
		}

		// register listeners
		sly_Core::registerListeners();

		// init console and error handling as early as possible
		$this->initConsole();
		$this->initErrorHandling();

		if ($this->unusedCache) {
			$this->output->writeln(array(
				'',
				'<error>WARNING:</error> The selected caching strategy (<comment>'.$this->unusedCache.'</comment>) '.
				'is not available on the command line. All commands are therefore using <info>blackhole</info> '.
				'as the caching implementation and <error>do not affect the actual website\'s cache data</error>. To '.
				'avoid this, you can either change the strategy to an always-available implementation (like '.
				'filesystem-based) or manually clear the cache in the backend after performing tasks in the console.',
				''
			));
		}
	}

	public function run() {
		$config = $this->container['sly-config'];

		foreach ($config->get('console/commands') as $name => $className) {
			$command = new $className($name, $this);
			$this->console->add($command);
		}

		// allow addOns to add custom options to the available commands
		$this->container['sly-dispatcher']->notify('SLY_CONSOLE_COMMANDS', $this->console, array(
			'container' => $this->container
		));

		$this->console->run();
	}

	public function getCurrentController() {

	}

	public function getCurrentControllerName() {

	}

	public function getCurrentAction() {
		return 'console';
	}

	public function getContainer() {
		return $this->container;
	}

	public function isBackend() {
		return true;
	}

	protected function initCache() {
		$config   = $this->container['sly-config'];
		$factory  = $this->container['sly-cache-factory'];
		$strategy = $config->get('caching_strategy', 'memory');

		if ($strategy && !$factory->isAvailable($strategy)) {
			$this->container['sly-cache'] = new Blackhole();

			$this->unusedCache = $strategy;
		}
	}

	protected function initConsole() {
		$this->input   = new ArgvInput();
		$this->output  = new ConsoleOutput();
		$this->console = new Application('Sally Console', '0.9');

		if (true === $this->input->hasParameterOption(array('--ansi'))) {
			$this->output->setDecorated(true);
		}
		elseif (true === $this->input->hasParameterOption(array('--no-ansi'))) {
			$this->output->setDecorated(false);
		}

		// put IO into container, so addOns can use it
		$this->container['sly-console-input']  = $this->input;
		$this->container['sly-console-output'] = $this->output;
		$this->container['sly-console-app']    = $this->console;
	}

	protected function initErrorHandling() {
		$this->container->getErrorHandler()->uninit();

		$errorHandler = new ErrorHandler($this->container, $this->console, $this->output);
		$errorHandler->init();

		$this->container->setErrorHandler($errorHandler);
	}
}
