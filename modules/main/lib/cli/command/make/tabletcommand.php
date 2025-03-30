<?php

namespace Bitrix\Main\Cli\Command\Make;

use Bitrix\Main\Cli\Command\Make\Service\Tablet\GenerateDto;
use Bitrix\Main\Cli\Command\Make\Service\TabletService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for generate ORM tablet.
 *
 * Example (run from `DOCUMENT_ROOT/bitrix` folder):
 * ```bash
	php bitrix.php make:tablet my_table partner.module
 * ```
 *
 * Example custom namespace:
 * ```bash
	php bitrix.php make:tablet my_table --namespace My\Custom\Namespace
 * ```
 *
 * Example generate to custom folder (default generate to document root):
 * ```bash
	php bitrix.php make:tablet my_table partner.module --root ./my/folder
 * ```
 *
 * Example generate to custom file:
 * ```bash
	php bitrix.php make:tablet my_table partner.module --show > ./my/folder/my-custom-file.php
 * ```
 */
final class TabletCommand extends Command
{
	private TabletService $service;

	protected function configure(): void
	{
		$this->service = new TabletService();

		$this
			->setName('make:tablet')
			->setDescription('Make ORM tablet for table')
			->addArgument('table_name', InputArgument::REQUIRED, 'table name')
			->addArgument('module', InputArgument::OPTIONAL, 'module id')
			->addOption('namespace', 'ns', InputOption::VALUE_REQUIRED, 'custom namespace')
			->addOption('root', null, InputOption::VALUE_REQUIRED, 'root folder for generate. Defaults server document root')
			->addOption('psr4', null, InputOption::VALUE_NEGATABLE, 'generate file path in PSR4 / camelCase style, ex: `module/lib/My/ClassName.php`', true)
			->addOption('show', null, InputOption::VALUE_NONE, 'outputs to console, without saving it. It can be used to save to an arbitrary location when using the `>` operator.')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$tableName = $input->getArgument('table_name');
		if (!is_string($tableName))
		{
			throw new InvalidArgumentException('Table name must be string');
		}

		$dto = new GenerateDto(
			tableName: $tableName,
			namespace: $input->getOption('namespace'),
			moduleId: $input->getArgument('module'),
			rootFolder: $input->getOption('root'),
			psr4: $input->getOption('psr4') === true,
		);

		if ($input->getOption('show') === true)
		{
			$output->write($this->service->generateContent($dto));
			$output->writeln("\n");
		}
		else
		{
			$this->service->generateFile($dto);
		}

		return self::SUCCESS;
	}
}
