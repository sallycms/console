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

use sly_Session;

class NullSession extends sly_Session {
	public function __construct() {
		parent::__construct('');
	}

	public function getID() {
		return false;
	}

	public function getInstallID() {
		return '';
	}

	public function get($key, $type, $default = null) {
		return $default;
	}

	public function set($key, $value) {
		// do nothing
	}

	public function delete($key) {
		// do nothing
	}

	public function flush() {
		// do nothing
	}

	public function destroy() {
		// do nothing
	}

	public function regenerateID() {
		// do nothing
	}
}
