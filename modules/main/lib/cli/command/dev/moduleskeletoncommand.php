<?php

declare(strict_types=1);

namespace Bitrix\Main\Cli\Command\Dev;

use Bitrix\Main\Cli\Command\Dev\Service\Module\ModuleSkeletonDto;
use Bitrix\Main\Cli\Command\Dev\Service\ModuleSkeletonService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for generate module skeleton
 *
 * Example (run from `DOCUMENT_ROOT/bitrix` folder):
 * ```bash
 * php bitrix.php dev:module-skeleton module
 * ```
 *
 * Create with subdirectory
 * ```bash
 * php bitrix.php dev:module-skeleton module subdir
 * ```
 */
final class ModuleSkeletonCommand extends Command
{
	protected function configure(): void
	{
		$this
			->setName('dev:module-skeleton')
			->setDescription('Generate module skeleton')
			->addArgument('module', InputArgument::REQUIRED, 'Module name')
			->addArgument('dir', InputArgument::OPTIONAL, 'Directory in [module]/lib, where you want to place skeleton')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$module = $input->getArgument('module');
		if (!is_string($module))
		{
			throw new InvalidArgumentException('Module name must be string');
		}

		$dir = $input->getArgument('dir') ?? '';

		$dto = new ModuleSkeletonDto(
			module: $module, directory: $dir,
		);

		$service = new ModuleSkeletonService();

		$service->generateSkeleton($dto);

		return self::SUCCESS;
	}
}