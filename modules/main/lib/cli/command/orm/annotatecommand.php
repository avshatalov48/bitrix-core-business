<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2018 Bitrix
 */

namespace Bitrix\Main\Cli\Command\Orm;

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Annotations\AnnotationInterface;
use Bitrix\Main\ORM\Annotations\AnnotationTrait;
use Bitrix\Main\ORM\Fields\ArrayField;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Entity;
use Bitrix\Main\ORM\Fields\DateField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\FloatField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @package    bitrix
 * @subpackage main
 */
class AnnotateCommand extends Command implements AnnotationInterface
{
	use AnnotationTrait;

	protected $debug = 0;

	protected $modulesScanned = [];

	protected $filesIncluded = 0;

	/** @var array Filled by handleClasses() */
	protected $entitiesFound = [];

	protected $excludedFiles = [
		'main/lib/text/string.php',
		'main/lib/composite/compatibility/aliases.php',
		'sale/lib/delivery/extra_services/string.php',
	];

	protected function configure()
	{
		$inBitrixDir = realpath(Application::getDocumentRoot().Application::getPersonalRoot()) === realpath(getcwd());

		$this
			// the name of the command (the part after "bin/console")
			->setName('orm:annotate')

			// the short description shown while running "php bin/console list"
			->setDescription('Scans project for ORM Entities.')

			// the full command description shown when running the command with
			// the "--help" option
			->setHelp('This system command optimizes Entity Relation Map building.')

			->setDefinition(
				new InputDefinition(array(
					new InputArgument(
						'output', InputArgument::OPTIONAL, 'File for annotations to be saved to',
						$inBitrixDir
							? 'modules/orm_annotations.php'
							: Application::getDocumentRoot().Application::getPersonalRoot().'/modules/orm_annotations.php'
					),
					new InputOption(
						'modules', 'm', InputOption::VALUE_OPTIONAL,
						'Modules to be scanned, separated by comma.', 'main'
					),
					new InputOption(
						'clean', 'c', InputOption::VALUE_NONE,
						'Clean current entity map.'
					),
				))
			)
		;

		// disable Loader::requireModule exception
		Loader::setRequireThrowException(false);
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$output->writeln([
			'Entity Scanner',
			'==============',
			'',
		]);

		$time = microtime(true);
		$memoryBefore = memory_get_usage();

		/** @var \Exception[] $exceptions deferred errors */
		$exceptions = [];

		// handle already known classes (but we don't know their modules)
		// as long as there are no any Table by default, we can ignore it
		//$this->handleClasses($this->getDeclaredClassesDiff(), $input, $output);

		// skip already defined classes
		$this->getDeclaredClassesDiff();

		// scan dirs
		$inputModules = [];
		$inputModulesRaw = $input->getOption('modules');

		if (!empty($inputModulesRaw) && $inputModulesRaw != 'all')
		{
			$inputModules = explode(',', $inputModulesRaw);
		}

		$dirs = $this->getDirsToScan($inputModules, $input, $output);

		foreach ($dirs as $dir)
		{
			$this->scanDir($dir, $input, $output);
		}

		// scan for bitrix entities
		$this->scanBitrixEntities($inputModules, $input, $output);

		// get classes from outside regular filesystem (e.g. iblock, hlblock)
		try
		{
			$this->handleVirtualClasses($inputModules, $input, $output);
		}
		catch (\Exception $e)
		{
			$exceptions[] = $e;
		}

		// output file path
		$filePath = $input->getArgument('output');
		$filePath = ($filePath[0] == '/')
			? $filePath // absolute
			: getcwd().'/'.$filePath; // relative

		// handle entities
		$annotations = [];

		// get current annotations
		if (!$input->getOption('clean') && file_exists($filePath) && is_readable($filePath))
		{
			$rawAnnotations = explode('/* '.static::ANNOTATION_MARKER, file_get_contents($filePath));

			foreach ($rawAnnotations as $rawAnnotation)
			{
				if ($rawAnnotation[0] === ':')
				{
					$endPos = mb_strpos($rawAnnotation, ' */');
					$entityClass = mb_substr($rawAnnotation, 1, $endPos - 1);
					//$annotation = substr($rawAnnotation, $endPos + 3 + strlen(PHP_EOL));

					$annotations[$entityClass] = '/* '.static::ANNOTATION_MARKER.rtrim($rawAnnotation);
				}
			}
		}

		// add/rewrite new entities
		foreach ($this->entitiesFound as $entityMeta)
		{
			try
			{
				$entityClass = $entityMeta['class'];
				$annotateUfOnly = $entityMeta['ufOnly'];

				$entity = Entity::getInstance($entityClass);
				$entityAnnotation = static::annotateEntity($entity, $annotateUfOnly);

				if (!empty($entityAnnotation))
				{
					$annotations[$entityClass] = "/* ".static::ANNOTATION_MARKER.":{$entityClass} */".PHP_EOL;
					$annotations[$entityClass] .= $entityAnnotation;
				}
			}
			catch (\Exception $e)
			{
				$exceptions[] = $e;
			}
		}

		// write to file
		$fileContent = '<?php'.PHP_EOL.PHP_EOL.join(PHP_EOL, $annotations);
		file_put_contents($filePath, $fileContent);

		$output->writeln('Map has been saved to: '.$filePath);

		// summary stats
		$time = round(microtime(true) - $time, 2);
		$memoryAfter = memory_get_usage();
		$memoryDiff = $memoryAfter - $memoryBefore;

		$output->writeln('Scanned modules: '.join(', ', $this->modulesScanned));
		$output->writeln('Scanned files: '.$this->filesIncluded);
		$output->writeln('Found entities: '.count($this->entitiesFound));
		$output->writeln('Time: '.$time.' sec');
		$output->writeln('Memory usage: '.(round($memoryAfter/1024/1024, 1)).'M (+'.(round($memoryDiff/1024/1024, 1)).'M)');
		$output->writeln('Memory peak usage: '.(round(memory_get_peak_usage()/1024/1024, 1)).'M');

		if (!empty($exceptions))
		{
			$io = new SymfonyStyle($input, $output);

			foreach ($exceptions as $e)
			{
				$io->warning('Exception: '.$e->getMessage().PHP_EOL.$e->getTraceAsString());
			}
		}

		return 0;
	}

	protected function getDirsToScan($inputModules, InputInterface $input, OutputInterface $output)
	{
		$basePaths = [
			//Application::getDocumentRoot().Application::getPersonalRoot().'/modules/',
			Application::getDocumentRoot().'/local/modules/'
		];

		$dirs = [];

		foreach ($basePaths as $basePath)
		{
			if (!file_exists($basePath))
			{
				continue;
			}

			$moduleList = [];

			foreach (new \DirectoryIterator($basePath) as $item)
			{
				if($item->isDir() && !$item->isDot())
				{
					$moduleList[] = $item->getFilename();
				}
			}

			// filter for input modules
			if (!empty($inputModules))
			{
				$moduleList = array_intersect($moduleList, $inputModules);
			}

			foreach ($moduleList as $moduleName)
			{
				// filter for installed modules
				if (!Loader::includeModule($moduleName))
				{
					continue;
				}

				$libDir = $basePath.$moduleName.'/lib';
				if (is_dir($libDir) && is_readable($libDir))
				{
					$dirs[] = $libDir;
				}

				$libDir = $basePath.$moduleName.'/dev/lib';
				if (is_dir($libDir) && is_readable($libDir))
				{
					$dirs[] = $libDir;
				}

				$this->modulesScanned[] = $moduleName;
			}
		}

		return $dirs;
	}

	protected function scanBitrixEntities($inputModules, InputInterface $input, OutputInterface $output)
	{
		$basePath = Application::getDocumentRoot().Application::getPersonalRoot().'/modules/';

		// get all available modules
		$moduleList = [];

		foreach (new \DirectoryIterator($basePath) as $item)
		{
			if($item->isDir() && !$item->isDot())
			{
				$moduleList[] = $item->getFilename();
			}
		}

		// filter for input modules
		if (!empty($inputModules))
		{
			$moduleList = array_intersect($moduleList, $inputModules);
		}

		// collect classes
		foreach ($moduleList as $moduleName)
		{
			$ufPath = $basePath.$moduleName.'/meta/'.static::ANNOTATION_UF_FILENAME;

			if (file_exists($ufPath))
			{
				$classes = include $ufPath;

				foreach ($classes as $class)
				{
					if (class_exists($class))
					{
						$this->entitiesFound[] = [
							'class' => $class,
							'ufOnly' => true,
						];
					}
				}
			}
		}

		// clear diff buffer
		$this->getDeclaredClassesDiff();
	}

	protected function registerFallbackAutoload()
	{
		spl_autoload_register(function($className) {
			list($vendor, $module) = explode('\\', $className);

			if (!empty($module))
			{
				Loader::includeModule($module);
			}

			Loader::autoLoad($className);
		});
	}

	protected function scanDir($dir, InputInterface $input, OutputInterface $output)
	{
		$this->debug($output,'scan dir: '.$dir);

		$this->registerFallbackAutoload();

		foreach (
			$iterator = new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS | \RecursiveDirectoryIterator::FOLLOW_SYMLINKS),
				\RecursiveIteratorIterator::SELF_FIRST) as $item
		)
		{
			// check for stop list
			foreach ($this->excludedFiles as $excludedFile)
			{
				$currentPath = str_replace('\\', '/', $item->getPathname());
				if (str_ends_with($currentPath, $excludedFile))
				{
					continue 2;
				}
			}

			/** @var $iterator \RecursiveDirectoryIterator */
			/** @var $item \SplFileInfo */
			if ($item->isFile() && $item->isReadable() && str_ends_with($item->getFilename(), '.php'))
			{
				$this->debug($output,'handle file: '.$item->getPathname());

				try
				{
					// get classes from file
					include_once $item->getPathname();
					$this->filesIncluded++;

					$classes = $this->getDeclaredClassesDiff();

					// check classes
					$this->handleClasses($classes, $input, $output);
				}
				catch (\Throwable $e) // php7
				{
					$this->debug($output, $e->getMessage());
				}
				catch (\Exception $e) // php5
				{
					$this->debug($output, $e->getMessage());
				}
			}
		}
	}

	protected function handleClasses($classes, InputInterface $input, OutputInterface $output)
	{
		foreach ($classes as $class)
		{
			$debugMsg = $class;

			if (is_subclass_of($class, DataManager::class) && str_ends_with($class, 'Table'))
			{
				if ((new \ReflectionClass($class))->isAbstract())
				{
					continue;
				}

				$debugMsg .= ' found!';
				$this->entitiesFound[] = [
					'class' => $class,
					'ufOnly' => false,
				];
			}

			$this->debug($output, $debugMsg);
		}
	}

	protected function getDeclaredClassesDiff()
	{
		static $lastDeclaredClasses = [];

		$currentDeclaredClasses = get_declared_classes();
		$diff = array_diff($currentDeclaredClasses, $lastDeclaredClasses);
		$lastDeclaredClasses = $currentDeclaredClasses;

		return $diff;
	}

	/**
	 * Builds annotation for classes outside regular filesystem (e.g. iblock, hlblock)
	 *
	 * @param array           $inputModules
	 * @param InputInterface  $input
	 * @param OutputInterface $output
	 */
	protected function handleVirtualClasses($inputModules, InputInterface $input, OutputInterface $output)
	{
		// init new classes by event
		$event = new \Bitrix\Main\Event("main", "onVirtualClassBuildList", [], $inputModules);
		$event->send();

		// no need to handle event result, get classes from the memory
		$classes = $this->getDeclaredClassesDiff();

		$this->handleClasses($classes, $input, $output);
	}

	/**
	 * @deprecated
	 *
	 * @param $field
	 *
	 * @return string
	 */
	public static function scalarFieldToTypeHint($field)
	{
		if (is_string($field))
		{
			$fieldClass = $field;
		}
		else
		{
			$fieldClass = get_class($field);
		}

		switch ($fieldClass)
		{
			case DateField::class:
				return '\\'.Date::class;
			case DatetimeField::class:
				return '\\'.DateTime::class;
			case IntegerField::class:
				return '\\int';
			case BooleanField::class:
				return '\\boolean';
			case FloatField::class:
				return '\\float';
			case ArrayField::class:
				return 'array';
			default:
				return '\\string';
		}
	}

	protected function debug(OutputInterface $output, $message)
	{
		if ($this->debug)
		{
			$output->writeln($message);
		}
	}
}
