<?php
/*
 * Copyright (c) 2016, webvariants GbR, http://www.webvariants.de
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

namespace sly\Console\Command;

use sly_Exception as CatchableException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AddonsDeactivate extends Addons {
	
	protected function getCommandName() {
		return 'sly:addons:deactivate';
	}
	
	protected function getCommandDescription() {
		return 'Deactivate Addons.';
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$container = $this->getContainer();
		$service   = $container->getAddOnManagerService();
		$addons    = $this->geRequestedAddonList($input, $output);
		
		if (!empty($addons)) {
			$output->writeln('  <info>Deactivating Addons:</info>');

			foreach($addons as $addon) {
				try {
					$service->deactivate($addon);
					$output->writeln('    <info>Deactivating Addon '.$addon.'</info>');
				} catch (CatchableException $e) {
					$output->writeln('    <error>Deactivation of Addon '.$addon.' failed with message '.$e->getMessage().'</error>');
				}
			}
		}
		
		$output->writeln('  <info>done</info>');
	}
}
