<?php

namespace Bitrix\Main\Cli\Command\Update;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Bitrix\Main\ModuleManager;

/**
 * Command to update modules versions.
 *
 * Examples (run from `DOCUMENT_ROOT/bitrix` folder):
 * ```bash
 * php bitrix.php update:versions versions.json
 * ```
 */
class VersionsCommand extends Command
{
	protected SymfonyStyle $io;

	protected function configure()
	{
		$this
			->setName('update:versions')
			->setDescription('Updates modules versions.')
			->addArgument('versions', InputArgument::REQUIRED, 'JSON file with modules versions.')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->io = new SymfonyStyle($input, $output);

		if (!$this->updateVersions($input->getArgument('versions')))
		{
			return self::FAILURE;
		}

		return self::SUCCESS;
	}

	protected function updateVersions(string $importFile): bool
	{
		if (($import = file_get_contents($importFile)) === false)
		{
			$this->io->error('Import of modules versions failed (can\'t read the file).');
			return false;
		}

		$modules = json_decode($import, true);
		if (!is_array($modules))
		{
			$this->io->error('Import of modules versions failed (JSON structure).');
			return false;
		}

		foreach ($modules as $module => $version)
		{
			if ($info = \CModule::CreateModuleObject($module))
			{
				if (version_compare($info->MODULE_VERSION, $version) >= 0)
				{
					if (($newVersion = ModuleManager::decreaseVersion($module, 1, $version)) !== null)
					{
						$this->io->writeln('Updated ' . $module . ' (' . $info->MODULE_VERSION . ') to version ' . $newVersion);
					}
				}
			}
			else
			{
				$this->io->warning('Module ' . $module . ' not found.');
			}
		}

		return true;
	}
}
