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

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use sly_Service_AddOn_Manager;
use sly_Container;

abstract class Addons extends Base {
	abstract protected function getCommandName();
	abstract protected function getCommandDescription();
	
	protected function geRequestedAddonList(InputInterface $input, OutputInterface $output) {
		$container = $this->getContainer();
		
		$addonList = $container->getAddOnService()->getRegisteredAddOns();
		$addon     = $input->getArgument('addon');
		$result    = $addon ? array_intersect($addonList, array($addon)) : $addonList;

		if ($addon && empty($result)) {
			$output->writeln('<error>Did not find '.$addon.'</error>');
		}
		
		return $result;
	}
	
	protected function configure() {
		$this
			->setName($this->getCommandName())
			->setDescription($this->getCommandDescription())
			->setDefinition(array(
				new InputArgument('addon', InputArgument::OPTIONAL, 'Select a addon.', null),
			));
	}
	
	protected function execute(InputInterface $input, OutputInterface $output) {
		
		
	}
}
