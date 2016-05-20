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

class ClearCache extends Base {
	protected function configure() {
		$this
			->setName('sly:cache:clear')
			->setDescription('Clears the system cache.')
			->setDefinition(array(
				new InputOption('all', null, InputOption::VALUE_NONE, 'To perform all optional tasks as well.'),
				new InputOption('sync-develop', 'd', InputOption::VALUE_NONE, 'To re-sync templates and modules.'),
				new InputOption('no-event', 'e', InputOption::VALUE_NONE, 'Do not trigger "cache cleared" event.')
			));
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$container = $this->getContainer();
		$all       = $input->getOption('all');

		// clear our own data caches
		$output->write('Flushing data cache...');
		$container['sly-cache']->clear('sly', true);
		$output->writeln(' <info>done</info>.');

		// sync develop files
		if ($all || $input->getOption('sync-develop')) {
			$output->write('Refreshing development contents...');
			$container['sly-service-template']->refresh();
			$container['sly-service-module']->refresh();
			$output->writeln(' <info>done</info>.');
		}

		if (!$input->getOption('no-event')) {
			$output->write('Trigger SLY_CACHE_CLEARED event...');
			$container['sly-dispatcher']->notify('SLY_CACHE_CLEARED', null, array(
				'backend'   => false,
				'container' => $container
			));
			$output->writeln(' <info>done</info>.');
		}
	}
}
