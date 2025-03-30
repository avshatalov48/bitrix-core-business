<?php

namespace Bitrix\Main\Cli\Command\Update;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Command to update language files.
 *
 * Examples (run from `DOCUMENT_ROOT/bitrix` folder):
 * ```bash
 * php bitrix.php update:languages
 * php bitrix.php update:languages -l it
 * ```
 */
class LanguagesCommand extends UpdateCommand
{
	protected function configure()
	{
		$this
			->setName('update:languages')
			->setDescription('Updates language files.')
			->addOption('languages', 'l', InputOption::VALUE_REQUIRED, 'Comma separated list of language codes to update.')
		;
	}

	protected function executeCommand(array $updateList, InputInterface $input): bool
	{
		if (isset($updateList["LANGS"][0]["#"]["INST"][0]["#"]["LANG"]))
		{
			$this->showUpdates($updateList["LANGS"][0]["#"]["INST"][0]["#"]["LANG"]);

			if (!$this->installUpdates($updateList["LANGS"][0]["#"]["INST"][0]["#"]["LANG"], $input->getOption('languages')))
			{
				return false;
			}
		}
		else
		{
			$this->io->info('No updates found.');
		}

		return true;
	}

	protected function installUpdates(array $updateList, ?string $inputLanguages): bool
	{
		$selectedLanguages = [];
		if (!empty($inputLanguages))
		{
			$selectedLanguages = explode(',', $inputLanguages);
		}

		$languages = [];
		foreach ($updateList as $language)
		{
			$languageId = $language["@"]["ID"];
			if (empty($selectedLanguages) || in_array($languageId, $selectedLanguages))
			{
				$languages[$languageId] = '[' . $languageId . '] '. $language["@"]["NAME"] . ' (' . $language["@"]["DATE"] . ')';
			}
		}

		if (!empty($languages))
		{
			if (!empty($selectedLanguages))
			{
				ksort($languages);

				$this->io->title('Language files to install');
				foreach ($languages as $language)
				{
					$this->io->writeln($language);
				}
			}

			if ($this->io->confirm('Install updates?'))
			{
				$result = $this->updater->installLanguages(array_keys($languages), $this->io);
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
			$this->io->info('No languages selected.');
		}

		return true;
	}

	protected function showUpdates(array $updateList): void
	{
		$this->io->title('Available language files updates');

		$updates = [];
		foreach ($updateList as $language)
		{
			$updates[] = '[' . $language["@"]["ID"] . '] '. $language["@"]["NAME"] . ' (' . $language["@"]["DATE"] . ')';
		}
		sort($updates);

		$this->io->write($updates, true);
	}
}
