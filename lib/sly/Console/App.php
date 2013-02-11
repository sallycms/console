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

class App implements \sly_App_Interface {
	protected $container;
	protected $root;

	public function __construct(\sly_Container $container, $rootDir) {
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
		\sly_Core::loadAddOns();

		// register listeners
		\sly_Core::registerListeners();
	}

	public function run() {

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
}
