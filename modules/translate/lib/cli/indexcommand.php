<?php

namespace Bitrix\Translate\Cli;

use Bitrix\Translate;
use Symfony\Component\Console;


class IndexCommand extends Console\Command\Command
{
	/**
	 * Configures the current command.
	 * @return void
	 */
	protected function configure()
	{
		//$inBitrixDir = realpath(Application::getDocumentRoot().Application::getPersonalRoot()) === realpath(getcwd());

		$this
			// the name of the command (the part after "bin/console")
			->setName('translate:index')

			// the short description shown while running "php bin/console list"
			->setDescription('Indexes project for localization language files.')

			// the full command description shown when running the command with
			// the "--help" option
			->setHelp('This system command builds search index of localization language files.')

			->setDefinition(
				new Console\Input\InputDefinition(array(
					new Console\Input\InputOption(
						'path',
						'p',
						Console\Input\InputArgument::OPTIONAL,
						'Path to look through.',
						'/bitrix/modules'
					)
				))
			)
		;
	}

	/**
	 * Executes the current command.
	 * @param Console\Input\InputInterface $input Console input steam.
	 * @param Console\Output\OutputInterface $output Console output steam.
	 * @return int|null
	 */
	protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
	{
		$vn = ($output->getVerbosity() > Console\Output\OutputInterface::VERBOSITY_QUIET);
		$vv = ($output->getVerbosity() >= Console\Output\OutputInterface::VERBOSITY_VERY_VERBOSE);

		$path = $input->getOption('path');

		if ($vn)
		{
			$startTime = getmicrotime();
			$memoryBefore = memory_get_usage();
			$output->writeln('--------Translate::index-----------');
			$output->writeln("Indexing path: {$path}");
		}

		$filt = new Translate\Filter([
			'path' => $path
		]);

		//-----------------
		// lang folders
		$pathLang = new Translate\Index\PathLangCollection();
		if ($vn)
		{
			$pathLang::$verbose = $vv;
			$output->write("PathLangCollection::purge..");
		}

		$pathLang->purge($filt);

		if ($vn)
		{
			$time = round(getmicrotime() - $startTime, 2);
			$output->writeln("\tdone\t{$time}sec");
			$output->write("PathLangCollection::collect..");
		}

		$pathLang->collect($filt);

		if ($vn)
		{
			$time = round(getmicrotime() - $startTime, 2);
			$output->writeln("\tdone\t{$time}sec");
		}

		//-----------------
		// path structure
		$pathIndex = new Translate\Index\PathIndexCollection();
		if ($vn)
		{
			$pathIndex::$verbose = $vv;
			$output->write("PathIndexCollection::purge..");
		}

		$pathIndex->purge($filt)->unvalidate($filt);
		if ($vn)
		{
			$time = round(getmicrotime() - $startTime, 2);
			$output->writeln("\tdone\t{$time}sec");
			$output->write("PathIndexCollection::collect..");
		}

		$pathIndex->collect($filt);

		if ($vn)
		{
			$time = round(getmicrotime() - $startTime, 2);
			$output->writeln("\tdone\t{$time}sec");
		}

		//-----------------
		// lang files
		$fileIndex = new Translate\Index\FileIndexCollection();
		if ($vn)
		{
			$fileIndex::$verbose = $vv;
			$output->write("FileIndexCollection::collect..");
		}

		$fileIndex->collect($filt);

		if ($vn)
		{
			$time = round(getmicrotime() - $startTime, 2);
			$output->writeln("\tdone\t{$time}sec");
		}

		//-----------------
		// phrases
		$phraseIndex = new Translate\Index\PhraseIndexCollection();
		if ($vn)
		{
			$phraseIndex::$verbose = $vv;
			$output->write("PhraseIndexCollection::collect..");
		}

		$phraseIndex->collect($filt);
		$pathIndex->validate($filt, false);

		if ($vn)
		{
			// summary stats
			$time = round(getmicrotime() - $startTime, 2);
			$output->writeln("\tdone\t{$time}sec");

			$memoryAfter = memory_get_usage();
			$memoryDiff = $memoryAfter - $memoryBefore;
			$output->writeln('Memory usage: '.(round($memoryAfter/1024/1024, 1)).'M (+'.(round($memoryDiff/1024/1024, 1)).'M)');
			$output->writeln('Memory peak usage: '.(round(memory_get_peak_usage()/1024/1024, 1)).'M');
		}

		return 0;
	}
}