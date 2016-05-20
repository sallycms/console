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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AddonsList extends Addons {
	
	protected function getCommandName() {
		return 'sly:addons:list';
	}
	
	protected function getCommandDescription() {
		return 'List Addons';
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$container = $this->getContainer();
		$service   = $container->getAddOnService();
		$addons    = $this->geRequestedAddonList($input, $output);
		
		if (!empty($addons)) {
			$maxLength = 0;
			foreach($addons as $addon) {
				$maxLength = max(array(mb_strlen($addon), $maxLength));
			}
			
			$output->writeln('| '.str_pad('AddOn', $maxLength).' | Status |');
			
			$states = array(
				'available',
				'<info>installed</info>',
				'<comment>active</comment>',
				'<fg=yellow>required</>',
				'<error>not compatible</error>'
			);

			foreach($addons as $addon) {
				if (!$service->isCompatible($addon)) {
					$state = $states[4];
				}
				elseif ($service->isRequired($addon)) {
					$state = $states[3];
				}
				elseif($service->isActivated($addon)) {
					$state = $states[2];
				}
				elseif($service->isInstalled($addon)) {
					$state = $states[1];
				}
				else {
					$state = $states[0];
				}
				$output->writeln('| '.str_pad($addon, $maxLength).' | '.$state.' |');
			}
		}
		
		$output->writeln('  <info>done</info>');
	}
}
