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

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
use sly_Container;
use sly_ErrorHandler;
use sly_ErrorHandler_Base;
use RuntimeException;
use Exception;

class ErrorHandler extends sly_ErrorHandler_Base implements sly_ErrorHandler {
	protected $runShutdown;
	protected $container;
	protected $console;
	protected $output;

	public function __construct(sly_Container $container, Application $console, OutputInterface $output) {
		$this->container   = $container;
		$this->console     = $console;
		$this->output      = $output;
		$this->runShutdown = true;
	}

	/**
	 * Initialize error handler
	 *
	 * This method sets the error level, disables all error handling by PHP and
	 * registered itself as the new error and exception handler.
	 */
	public function init() {
		error_reporting(E_ALL & ~E_STRICT & ~E_DEPRECATED);
		ini_set('display_errors', 'Off');
		ini_set('log_errors', 'Off');
		ini_set('html_errors', 'Off');

		set_exception_handler(array($this, 'handleException'));
		set_error_handler(array($this, 'handleError'));
		register_shutdown_function(array($this, 'shutdownFunction'));
	}

	/**
	 * Un-initialize the error handler
	 *
	 * Call this if you don't want the error handling anymore.
	 */
	public function uninit() {
		parent::uninit();
		$this->runShutdown = false;
	}

	/**
	 * Handle regular PHP errors
	 *
	 * This method is called when a notice, warning or error happened. It will
	 * log the error, but not print it.
	 *
	 * @param int    $severity
	 * @param string $message
	 * @param string $file
	 * @param int    $line
	 * @param array  $context
	 */
	public function handleError($severity, $message, $file, $line, array $context = null) {
		// always die away if the problem was *really* bad
		if ($severity & (E_ERROR | E_PARSE | E_USER_ERROR)) {
			$exception = new RuntimeException(self::$codes[$severity].': '.$message, $severity);
			$this->handleException($exception);
		}

		// only perform special handling when required
		if ($severity & error_reporting()) {
			$this->output->writeln('<error>'.self::$codes[$severity].': '.$message.'</error>');
		}
	}

	/**
	 * Handle uncaught exceptions
	 *
	 * This method is called when an exception is thrown, but not caught. It will
	 * log the exception and stop the script execution by displaying a neutral
	 * error page.
	 *
	 * @param $exception
	 */
	public function handleException($exception) {
		$this->console->renderException($exception, $this->output);
		exit(1);
	}

	/**
	 * Shutdown function
	 *
	 * This method is called when the scripts exits. It checks for unhandled
	 * errors and calls the regular error handling when necessarry.
	 *
	 * Call uninit() if you do not want this function to perform anything.
	 */
	public function shutdownFunction() {
		if ($this->runShutdown) {
			$e = error_get_last();

			// run regular error handling when there's an error
			if (isset($e['type'])) {
				$this->handleError($e['type'], $e['message'], $e['file'], $e['line'], null);
			}
		}
	}
}
