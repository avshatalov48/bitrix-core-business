<?php

namespace Bitrix\Main\Cli\Command\Make;

use Bitrix\Main\Cli\Command\Make\Service\ComponentService;
use Bitrix\Main\Cli\Command\Make\Service\Component\GenerateDto;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command for generate a simple component with a class and a template.
 *
 * **Example** (run from `DOCUMENT_ROOT/bitrix` folder) (will be created using the path <root>/bitrix/modules/calendar/install/components/bitrix/calendar.open-events.list):
 *
 * ```bash
 * php bitrix.php make:component calendar.open-events.list
 * ```
 *
 * **Example custom namespace** (will be created using the path <root>/local/modules/calendar/install/components/up/calendar.open-events.list):
 *
 * ```bash
 * php bitrix.php make:component up:calendar.open-events.list
 *  ```
 *
 * **Example local** (will be created using the path <root>/local/modules/calendar/install/components/bitrix/calendar.open-events.list):
 *
 * ```bash
 * php bitrix.php make:component calendar.open-events.list --local
 *  ```
 *
 * **Example no module** (will be created using the path <root>/local/components/up/calendar.open-events.list):
 *
 * ```bash
 * php bitrix.php make:component up:calendar.open-events.list --no-module
 *  ```
 */
final class ComponentCommand extends Command
{
	protected ComponentService $service;

	protected function configure(): void
	{
		$this->service = new ComponentService();

		$this
			->setName('make:component')
			->setDescription('Create a simple component with a class and a template')
			->addArgument('name', InputArgument::REQUIRED, 'component name')
			->addOption('module', null, InputOption::VALUE_REQUIRED, 'module id')
			->addOption('no-module', null, InputOption::VALUE_NONE, 'component will be created in components folder, outside the module')
			->addOption('local', null, InputOption::VALUE_NONE, 'component will be created in DOCUMENT_ROOT/local folder')
			->addOption('root', null, InputOption::VALUE_REQUIRED, 'the root folder for generate. Defaults server document root')
			->addOption('show', null, InputOption::VALUE_NONE, 'outputs to console, without saving it')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		[$namespace, $name] = $this->prepareComponentName($input);

		$dto = new GenerateDto(
			name: $name,
			namespace: $namespace,
			module: $input->getOption('module') ?? explode('.', $name)[0],
			noModule: $input->getOption('no-module') === true,
			local: $input->getOption('local') === true,
			root: $input->getOption('root'),
		);

		$styledOutput = new SymfonyStyle($input, $output);

		$pathToComponent = $this->service->getPathToComponent($dto);

		if ($input->getOption('show') === true)
		{
			$styledOutput->title('===============CLASS===============');
			$styledOutput->text($this->service->generateClassContent($dto));
			$styledOutput->title('==============TEMPLATE=============');
			$styledOutput->text($this->service->generateTemplateContent($dto));
			$styledOutput->title('=============LANG FILE=============');
			$styledOutput->text($this->service->generateLangContent($dto));
			$styledOutput->title('                                   ');
			$styledOutput->success("The component '$name' will be created using the path $pathToComponent\n");
		}
		else
		{
			$this->service->generateClassFile($dto);
			$this->service->generateTemplateFile($dto);
			$this->service->generateLangFile($dto);
			$styledOutput->success("The component '$name' was created using the path $pathToComponent\n");
		}

		$styledOutput->title("===============USAGE===============");
		$styledOutput->writeln(<<<PHP
\$APPLICATION->IncludeComponent(
	'{$namespace}:{$name}',
	'',
	[],
);

PHP);

		return self::SUCCESS;
	}

	protected function prepareComponentName(InputInterface $input): array
	{
		$name = $input->getArgument('name');
		if (!is_string($name))
		{
			throw new InvalidArgumentException('Component name must be a string');
		}

		if (!\CComponentEngine::checkComponentName($name))
		{
			throw new InvalidArgumentException('Invalid component name');
		}

		if (str_contains($name, ':'))
		{
			return explode(':', $name);
		}

		return ['bitrix', $name];
	}
}
