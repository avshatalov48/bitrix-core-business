<?php

namespace Bitrix\Main\Cli\Command\Dev;

use Bitrix\Main\Cli\Command\Dev\Service\LocatorCodesService;
use Bitrix\Main\Cli\Command\Dev\Service\Locator\LocatorCodesDto;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for generate .phpstorm.meta.php file from .settings.php.
 *
 * Example (run from `DOCUMENT_ROOT/bitrix` folder):
 * ```bash
 * php bitrix.php dev:locator-codes module
 * ```
 *
 * Generate to console:
 * ```bash
 * php bitrix.php dev:locator-codes module --show
 * ```
 *
 * Generate to custom file:
 * ```bash
 * php bitrix.php dev:locator-codes module --show > ./my/folder/my-custom-file.php
 * ```
 */
final class LocatorCodesCommand extends Command
{
	protected function configure(): void
	{
		$this
			->setName('dev:locator-codes')
			->setDescription('Generate .phpstorm.meta.php file')
			->addArgument('module', InputArgument::REQUIRED, 'Module name')
			->addArgument('code', InputArgument::OPTIONAL, 'Code')
			->addOption('show', null, InputOption::VALUE_NONE, 'outputs to console, without saving it. It can be used to save to an arbitrary location when using the `>` operator.')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$module = $input->getArgument('module');
		if (!is_string($module))
		{
			throw new InvalidArgumentException('Module name must be string');
		}

		$code = $input->getArgument('code');
		if (!is_string($code))
		{
			$code = "bitrix_{$module}_locator_codes";
		}

		$dto = new LocatorCodesDto(
			module: $module,
			code: $code,
		);

		$service = new LocatorCodesService();

		if ($input->getOption('show') === true)
		{
			$output->write($service->generateContent($dto));
			$output->writeln("\n");
		}
		else
		{
			$service->generateFile($dto);
		}

		return self::SUCCESS;
	}
}
