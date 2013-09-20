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
				new InputOption('assets', 'a', InputOption::VALUE_NONE, 'To re-validate the asset cache.'),
				new InputOption('reinit-addons', 'r', InputOption::VALUE_NONE, 'To re-initialize the assets of all addOns.'),
				new InputOption('sync-develop', 'd', InputOption::VALUE_NONE, 'To re-sync templates and modules.'),
				new InputOption('no-event', 'e', InputOption::VALUE_NONE, 'Do not trigger "cache cleared" event.')
			));
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$container = $this->getContainer();
		$all       = $input->getOption('all');

		// clear loader cache
		$output->write('Flushing autoloader cache...');
		\sly_Loader::clearCache();
		$output->writeln(' <info>done</info>.');

		// clear our own data caches
		$output->write('Flushing data cache...');
		$container->getCache()->flush('sly', true);
		$output->writeln(' <info>done</info>.');

		// sync develop files
		if ($all || $input->getOption('sync-develop')) {
			$output->write('Refreshing development contents...');
			$container->getTemplateService()->refresh();
			$container->getModuleService()->refresh();
			$output->writeln(' <info>done</info>.');
		}

		// re-initialize assets of all installed addOns
		if ($all || $input->getOption('reinit-addons')) {
			$addonService = $container->getAddOnService();
			$addonMngr    = $container->getAddOnManagerService();
			$addOns       = $addonService->getInstalledAddOns();

			if (!empty($addOns)) {
				$output->writeln('Refreshing addOn assets...');

				foreach ($addOns as $addOn) {
					$addonMngr->copyAssets($addOn);
					$output->writeln('   - '.$addOn);
				}

				$output->writeln('<info>done</info>.');
			}
			else {
				$output->writeln('Refreshing addOn assets... <info>no addOns found</info>.');
			}
		}

		// clear asset cache (force this if the assets have been re-initialized)
		if ($all || $input->getOption('reinit-addons') || $input->getOption('assets')) {
			$output->write('Flushing asset cache...');
			$container->getAssetService()->clearCache();
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
