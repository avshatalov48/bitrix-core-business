<?php

namespace Bitrix\Main\Cli\Command\Update;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Command to update modules.
 *
 * Examples (run from `DOCUMENT_ROOT/bitrix` folder):
 * ```bash
 * php bitrix.php update:modules
 * php bitrix.php update:modules -m main
 * php bitrix.php update:modules -i updates.json
 * ```
 */
class ModulesCommand extends UpdateCommand
{
	protected function configure()
	{
		$this
			->setName('update:modules')
			->setDescription('Updates modules.')
			->addOption('modules', 'm', InputOption::VALUE_REQUIRED, 'Comma separated list of modules to update.')
			->addOption('import', 'i', InputOption::VALUE_REQUIRED, 'JSON file with modules versions (exported in the expert mode).')
		;
	}

	protected function executeCommand(array $updateList, InputInterface $input): bool
	{
		if (isset($updateList['MODULES'][0]['#']['MODULE']))
		{
			$updateList = $this->updater->transformUpdateList($updateList['MODULES'][0]['#']['MODULE']);

			$this->showUpdates($updateList);

			$importFile = $input->getOption('import');
			if (!empty($importFile))
			{
				// expert mode
				if (!$this->installExpertUpdates($updateList, $importFile))
				{
					return false;
				}
			}
			else
			{
				if (!$this->installUpdates($updateList, $input->getOption('modules')))
				{
					return false;
				}
			}
		}
		else
		{
			$this->io->info('No updates found.');
		}

		return true;
	}

	protected function installUpdates(array $updateList, ?string $inputModules): bool
	{
		$selectedModules = [];
		if (!empty($inputModules))
		{
			$selectedModules = explode(',', $inputModules);
		}

		$modules = [];
		foreach ($updateList as $moduleId => $module)
		{
			if (empty($selectedModules) || in_array($moduleId, $selectedModules))
			{
				$modules[$moduleId] = array_key_last($module['VERSION']);
			}
		}

		if (!empty($modules))
		{
			if (!empty($selectedModules))
			{
				// need to check dependencies
				$dependentModules = $this->updater->getDependencies($updateList, $modules);
				$modules = array_merge($modules, $dependentModules);
				ksort($modules);

				$this->io->title('Updates to install');
				foreach ($modules as $moduleId => $version)
				{
					$this->io->writeln($moduleId . ' (' . $version . ')');
				}
			}

			if ($this->io->confirm('Install updates?'))
			{
				$result = $this->updater->installUpdates(array_keys($modules), $this->io);
				if ($result->isSuccess())
				{
					$this->io->success('Updates installed successfully.');
				}
				else
				{
					$this->io->error((string)$result->getError());
					return false;
				}
			}
		}
		else
		{
			$this->io->info('No modules selected.');
		}

		return true;
	}

	protected function showUpdates(array $updateList): void
	{
		$this->io->title('Available updates');

		$updates = [];
		foreach ($updateList as $moduleId => $module)
		{
			$updates[] = $moduleId . ' (' . array_key_last($module['VERSION']) . ')';
		}
		sort($updates);

		$this->io->write($updates, true);
	}

	protected function installExpertUpdates(array $updateList, string $importFile): bool
	{
		if (($import = file_get_contents($importFile)) === false)
		{
			$this->io->error('Import of expert updates failed (can\'t read the file).');
			return false;
		}

		$expertModules = json_decode($import, true);
		if (!\CUpdateExpertMode::isCorrectModulesStructure($expertModules))
		{
			$this->io->error('Import of expert updates failed (JSON structure).');
			return false;
		}

		$this->io->title('Imported expert updates');
		foreach ($expertModules as $module => $version)
		{
			$this->io->writeln($module . ' (' . $version . ')');
		}

		$result = $this->updater->checkExpertDependencies($updateList, $expertModules);

		if (!$result->isSuccess())
		{
			$this->io->error((string)$result->getError());

			foreach ($result->getData() as $module => $dependencies)
			{
				$this->io->writeln($module . ' requires ' . implode(', ', $dependencies));
			}
			$this->io->newLine();

			return false;
		}

		if ($this->io->confirm('Install updates?'))
		{
			$result = $this->updater->installUpdates($expertModules, $this->io);
			if ($result->isSuccess())
			{
				$this->io->success('Updates installed successfully.');
			}
			else
			{
				$this->io->error((string)$result->getError());
				return false;
			}
		}

		return true;
	}
}
