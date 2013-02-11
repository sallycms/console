<?php
/*
 * Copyright (c) 2013, webvariants GbR, http://www.webvariants.de
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

namespace sly\Console;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use sly_App_Interface;
use sly_Container;
use sly_Core;

class App implements sly_App_Interface {
	protected $container;
	protected $root;
	protected $input;
	protected $output;
	protected $console;

	public function __construct(sly_Container $container, $rootDir) {
		$this->container = $container;
		$this->root      = realpath($rootDir);
	}

	public function initialize() {
		$container = $this->getContainer();
		$config    = $container->getConfig();

		// init the current language
		$container->setCurrentLanguageId($config->get('DEFAULT_CLANG_ID'));

		// load static config
		$config->loadStatic($this->root.'/config/static.yml');

		// init timezone
		date_default_timezone_set($config->get('TIMEZONE', 'UTC'));

		// ... and locale
		$container->setI18N(new \sly_I18N('en_gb', $this->root.'/lang'));

		// boot addOns
		sly_Core::loadAddOns();

		// register listeners
		sly_Core::registerListeners();

		// init console and error handling as early as possible
		$this->initConsole();
		$this->initErrorHandling($container);
	}

	public function run() {
		$config = $this->container->getConfig();

		foreach ($config->get('console/commands') as $name => $className) {
			$command = new $className($name, $this);
			$this->console->add($command);
		}

		$this->console->run();
	}

	public function getCurrentController() {

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

	protected function initConsole() {
		$this->input   = new ArgvInput();
		$this->output  = new ConsoleOutput();
		$this->console = new Application('Sally Console', '0.8.0');

		if (true === $this->input->hasParameterOption(array('--ansi'))) {
			$this->output->setDecorated(true);
		}
		elseif (true === $this->input->hasParameterOption(array('--no-ansi'))) {
			$this->output->setDecorated(false);
		}
	}

	protected function initErrorHandling(sly_Container $container) {
		$container->getErrorHandler()->uninit();

		$errorHandler = new ErrorHandler($container, $this->console, $this->output);
		$errorHandler->init();

		$container->setErrorHandler($errorHandler);
	}
}
