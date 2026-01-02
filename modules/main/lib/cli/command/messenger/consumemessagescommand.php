<?php

declare(strict_types=1);

namespace Bitrix\Main\Cli\Command\Messenger;

use Bitrix\Main\Config\Configuration;
use Bitrix\Main\Messenger\Config\WorkerRunMode;
use Bitrix\Main\Messenger\Internals\Worker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The command for run Messenger's worker in cli
 *
 * Example (run from `DOCUMENT_ROOT/bitrix` folder):
 *  ```bash
 * php bitrix.php messenger:consume
 *  ```
 */
class ConsumeMessagesCommand extends Command
{
	protected function configure(): void
	{
		$this
			->setName('messenger:consume')
			->setDescription('Run Messenger\'s worker')
			->setDefinition(
				[
					new InputArgument(
						'queues',
						InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
						'Names of the queues to consume'
					),
//					new InputOption('limit', 'l', InputOption::VALUE_REQUIRED, 'Limit the number of received messages'),
					new InputOption(
						'time-limit',
						't',
						InputOption::VALUE_REQUIRED,
						'The time limit in seconds the worker can handle new messages'
					),
					new InputOption(
						'sleep',
						null,
						InputOption::VALUE_REQUIRED,
						'Seconds to sleep before asking for new messages after worker iteration',
						1
					),
				]
			)
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$output->writeln('Started ' . date('Y-m-d H:i:s') . '. PID: ' . getmypid());

		if (null !== $timeLimit = $input->getOption('time-limit'))
		{
			if (!is_numeric($timeLimit) || $timeLimit < 1)
			{
				throw new InvalidOptionException(
					\sprintf('Option "time-limit" must be a positive integer, "%s" passed.', $timeLimit)
				);
			}
		}

		$config = Configuration::getValue('messenger');

		if (!$config)
		{
			return self::SUCCESS;
		}

		if (!empty($config['run_mode']) && $config['run_mode'] === WorkerRunMode::Cli->value)
		{
			$worker = new Worker();

			$options = [
				'sleep' => $input->getOption('sleep') * 1000000,
			];

			if ($queues = $input->getArgument('queues'))
			{
				$options['queues'] = $queues;
			}

			$startTime = time();
			$endTime = $timeLimit ? $startTime + $timeLimit : null;

			while (!$endTime || (time() < $endTime))
			{
				$worker->process($options);

				usleep($options['sleep']);
			}
		}
		else
		{
			$output->writeln(
				'Messenger not configured to run in CLI. Set "messenger.run_mode" to "cli" in .setting.php'
			);
		}

		$output->writeln('Stopped ' . date('Y-m-d H:i:s') . '. PID: ' . getmypid());

		return self::SUCCESS;
	}
}
