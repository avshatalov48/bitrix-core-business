<?php

namespace Bitrix\Main\Cli\Command\Update;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Bitrix\Main\Cli\Command\Update\Service\UpdaterService;

abstract class UpdateCommand extends Command
{
	protected UpdaterService $updater;
	protected SymfonyStyle $io;

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->updater = new UpdaterService();
		$this->io = new SymfonyStyle($input, $output);

		$result = $this->updater->listUpdates();

		if (!$result->isSuccess())
		{
			$this->io->error((string)$result->getError());
			return self::FAILURE;
		}

		$updateList = $result->getData();

		if (isset($updateList['REPAIR']))
		{
			if (!$this->repair($updateList['REPAIR']))
			{
				return self::FAILURE;
			}
		}

		if (isset($updateList['CLIENT']))
		{
			$this->showClientInfo($updateList['CLIENT']);
		}

		if (isset($updateList['UPDATE_SYSTEM']))
		{
			return $this->upgradeUpdateSystem() ? self::SUCCESS : self::FAILURE;
		}

		if (!$this->executeCommand($updateList, $input))
		{
			return self::FAILURE;
		}

		return self::SUCCESS;
	}

	protected function repair(array $repair): bool
	{
		$result = $this->updater->repair($repair[0]['@']['TYPE']);
		if (!$result->isSuccess())
		{
			$this->io->error((string)$result->getError());
			return false;
		}
		return true;
	}

	protected function showClientInfo(array $client): void
	{
		$this->io->title('Client information');
		$this->io->writeln('Name: ' . ($client[0]['@']['NAME'] ?? 'n/a'));
		$this->io->writeln('Product: ' . ($client[0]["@"]['LICENSE'] ?? 'n/a'));
	}

	protected function upgradeUpdateSystem(): bool
	{
		$this->io->info('Upgrade of the Update System is required.');

		if ($this->io->confirm('Upgrade the Update System?'))
		{
			$result = $this->updater->updateUpdateSystem();
			if ($result->isSuccess())
			{
				$this->io->success('Update System upgraded successfully. Restart the command.');
				return true;
			}
			$this->io->error((string)$result->getError());
		}
		return false;
	}

	abstract protected function executeCommand(array $updateList, InputInterface $input): bool;
}
