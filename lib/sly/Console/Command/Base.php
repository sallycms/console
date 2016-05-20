<?php
/*
 * Copyright (c) 2013, webvariants GbR, http://www.webvariants.de
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

namespace sly\Console\Command;

use Symfony\Component\Console\Command\Command;
use sly\Console\App;

class Base extends Command {
	private $app;

	/**
	 * constructor
	 *
	 * @param string $name  command identifer, not used as the real command name in SallyCMS
	 * @param App    $app   Sally console app instance
	 */
	public function __construct($name, App $app) {
		$this->app = $app;

		parent::__construct($name);
	}

	/**
	 * get console app
	 *
	 * @return sly\Console\App
	 */
	public function getApp() {
		return $this->app;
	}

	/**
	 * get DI container
	 *
	 * @return sly_Container
	 */
	public function getContainer() {
		return $this->app->getContainer();
	}
}
