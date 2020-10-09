<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2018 Bitrix
 */

namespace Bitrix\Main\Cli;

use Bitrix\Iblock\IblockTable;
use Bitrix\Main\Application;
use Bitrix\Main\Authentication\Context;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Data\AddResult;
use Bitrix\Main\ORM\Data\Result;
use Bitrix\Main\ORM\Data\UpdateResult;
use Bitrix\Main\ORM\Fields\ArrayField;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Entity;
use Bitrix\Main\ORM\Fields\FieldTypeMask;
use Bitrix\Main\ORM\Fields\UserTypeField;
use Bitrix\Main\ORM\Objectify\EntityObject;
use Bitrix\Main\ORM\Fields\DateField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\ORM\Fields\Relations\ManyToMany;
use Bitrix\Main\ORM\Fields\Relations\OneToMany;
use Bitrix\Main\ORM\Fields\FloatField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Objectify\Collection;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\ScalarField;
use Bitrix\Main\ORM\Objectify\State;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Text\StringHelper;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Type\Dictionary;
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
class OrmAnnotateCommand extends Command
{
	protected $debug = 0;

	protected $modulesScanned = [];

	protected $filesIncluded = 0;

	protected $entitiesFound = [];

	protected $excludedFiles = [
		'main/lib/text/string.php',
		'main/lib/composite/compatibility/aliases.php',
		'sale/lib/delivery/extra_services/string.php',
	];

	const ANNOTATION_MARKER = 'ORMENTITYANNOTATION';

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

		$time = getmicrotime();
		$memoryBefore = memory_get_usage();

		/** @var \Exception[] $exceptions deferred errors */
		$exceptions = [];

		// handle already known classes (but we don't know their modules)
		// as long as there are no any Table by default, we can ignore it
		$this->handleClasses($this->getDeclaredClassesDiff(), $input, $output);

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
		$filePath = ($filePath{0} == '/')
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
				if ($rawAnnotation{0} === ':')
				{
					$endPos = mb_strpos($rawAnnotation, ' */');
					$entityClass = mb_substr($rawAnnotation, 1, $endPos - 1);
					//$annotation = substr($rawAnnotation, $endPos + 3 + strlen(PHP_EOL));

					$annotations[$entityClass] = '/* '.static::ANNOTATION_MARKER.rtrim($rawAnnotation);
				}
			}
		}

		// add/rewrite new entities
		foreach ($this->entitiesFound as $entityClass)
		{
			try
			{
				$entity = Entity::getInstance($entityClass);
				$entityAnnotation = static::annotateEntity($entity, $input,$output);
				$annotations[$entityClass] = "/* ".static::ANNOTATION_MARKER.":{$entityClass} */".PHP_EOL;
				$annotations[$entityClass] .= $entityAnnotation;
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
		$time = round(getmicrotime() - $time, 2);
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
	}

	protected function getDirsToScan($inputModules, InputInterface $input, OutputInterface $output)
	{
		$basePaths = [
			Application::getDocumentRoot().Application::getPersonalRoot().'/modules/',
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

	protected function registerFallbackAutoload()
	{
		spl_autoload_register(function($className) {
			list($vendor, $module) = explode('\\', $className);

			if (!empty($module))
			{
				Loader::includeModule($module);
			}

			return Loader::autoLoad($className);
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
				if (mb_substr($currentPath, -mb_strlen($excludedFile)) === $excludedFile)
				{
					continue 2;
				}
			}

			/** @var $iterator \RecursiveDirectoryIterator */
			/** @var $item \SplFileInfo */
			if ($item->isFile() && $item->isReadable() && mb_substr($item->getFilename(), -4) == '.php')
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

			if (is_subclass_of($class, DataManager::class) && mb_substr($class, -5) == 'Table')
			{
				$reflection = new \ReflectionClass($class);

				if ($reflection->isAbstract())
				{
					continue;
				}

				$debugMsg .= ' found!';
				$this->entitiesFound[] = $class;
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

	public static function annotateEntity(Entity $entity, InputInterface $input, OutputInterface $output)
	{
		$entityNamespace = trim($entity->getNamespace(), '\\');
		$dataClass = $entity->getDataClass();

		$objectClass = $entity->getObjectClass();
		$objectClassName = $entity->getObjectClassName();
		$objectDefaultClassName = Entity::getDefaultObjectClassName($entity->getName());

		$collectionClass = $entity->getCollectionClass();
		$collectionClassName = $entity->getCollectionClassName();
		$collectionDefaultClassName = Entity::getDefaultCollectionClassName($entity->getName());

		$code = [];
		$objectCode = [];
		$collectionCode = [];

		$code[] = "namespace {$entityNamespace} {"; // start namespace
		$code[] = "\t/**"; // start class annotations
		$code[] = "\t * {$objectClassName}";
		$code[] = "\t * @see {$dataClass}";
		$code[] = "\t *";
		$code[] = "\t * Custom methods:";
		$code[] = "\t * ---------------";
		$code[] = "\t *";

		foreach ($entity->getFields() as $field)
		{
			$objectFieldCode = [];
			$collectionFieldCode = [];

			if ($field instanceof ScalarField)
			{
				list($objectFieldCode, $collectionFieldCode) = static::annotateScalarField($field);
			}
			elseif ($field instanceof UserTypeField)
			{
				list($objectFieldCode, $collectionFieldCode) = static::annotateUserType($field);
			}
			elseif ($field instanceof ExpressionField)
			{
				list($objectFieldCode, $collectionFieldCode) = static::annotateExpression($field);
			}
			elseif ($field instanceof Reference)
			{
				list($objectFieldCode, $collectionFieldCode) = static::annotateReference($field);
			}
			elseif ($field instanceof OneToMany)
			{
				list($objectFieldCode, $collectionFieldCode) = static::annotateOneToMany($field);
			}
			elseif ($field instanceof ManyToMany)
			{
				list($objectFieldCode, $collectionFieldCode) = static::annotateManyToMany($field);
			}

			$objectCode = array_merge($objectCode, $objectFieldCode);
			$collectionCode = array_merge($collectionCode, $collectionFieldCode);
		}

		// common class methods
		$code = array_merge($code, $objectCode);
		$code[] = "\t *";
		$code[] = "\t * Common methods:";
		$code[] = "\t * ---------------";
		$code[] = "\t *";
		$code[] = "\t * @property-read \\".Entity::class." \$entity";
		$code[] = "\t * @property-read array \$primary";
		$code[] = "\t * @property-read int \$state @see \\".State::class;
		$code[] = "\t * @property-read \\".Dictionary::class." \$customData";
		$code[] = "\t * @property \\".Context::class." \$authContext";
		$code[] = "\t * @method mixed get(\$fieldName)";
		$code[] = "\t * @method mixed remindActual(\$fieldName)";
		$code[] = "\t * @method mixed require(\$fieldName)";
		$code[] = "\t * @method bool has(\$fieldName)";
		$code[] = "\t * @method bool isFilled(\$fieldName)";
		$code[] = "\t * @method bool isChanged(\$fieldName)";
		$code[] = "\t * @method {$objectClass} set(\$fieldName, \$value)";
		$code[] = "\t * @method {$objectClass} reset(\$fieldName)";
		$code[] = "\t * @method {$objectClass} unset(\$fieldName)";
		$code[] = "\t * @method void addTo(\$fieldName, \$value)";
		$code[] = "\t * @method void removeFrom(\$fieldName, \$value)";
		$code[] = "\t * @method void removeAll(\$fieldName)";
		$code[] = "\t * @method \\".Result::class." delete()";
		$code[] = "\t * @method void fill(\$fields = \\".FieldTypeMask::class."::ALL) flag or array of field names";
		$code[] = "\t * @method mixed[] collectValues(\$valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, \$fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)";
		$code[] = "\t * @method \\".AddResult::class."|\\".UpdateResult::class."|\\".Result::class." save()";
		$code[] = "\t * @method static {$objectClass} wakeUp(\$data)";
		//$code[] = "\t *";
		//$code[] = "\t * for parent class, @see \\".EntityObject::class;
		// xTODO we can put path to the original file here
		$code[] = "\t */"; // end class annotations
		$code[] = "\tclass {$objectDefaultClassName} {";
		$code[] = "\t\t/* @var {$dataClass} */";
		$code[] = "\t\tstatic public \$dataClass = '{$dataClass}';";
		$code[] = "\t\t/**";
		$code[] = "\t\t * @param bool|array \$setDefaultValues";
		$code[] = "\t\t */";
		$code[] = "\t\tpublic function __construct(\$setDefaultValues = true) {}";
		$code[] = "\t}"; // end class

		// compatibility with default classes
		if (mb_strpos($objectClassName, Entity::DEFAULT_OBJECT_PREFIX) !== 0) // better to compare full classes definitions
		{
			$defaultObjectClassName = Entity::getDefaultObjectClassName($entity->getName());

			// no need anymore as far as custom class inherits EO_
			//$code[] = "\tclass_alias('{$objectClass}', '{$entityNamespace}\\{$defaultObjectClassName}');";
		}

		$code[] = "}"; // end namespace

		// annotate collection class
		$code[] = "namespace {$entityNamespace} {"; // start namespace
		$code[] = "\t/**";
		$code[] = "\t * {$collectionClassName}";
		$code[] = "\t *";
		$code[] = "\t * Custom methods:";
		$code[] = "\t * ---------------";
		$code[] = "\t *";

		$code = array_merge($code, $collectionCode);

		$code[] = "\t *";
		$code[] = "\t * Common methods:";
		$code[] = "\t * ---------------";
		$code[] = "\t *";
		$code[] = "\t * @property-read \\".Entity::class." \$entity";
		$code[] = "\t * @method void add({$objectClass} \$object)";
		$code[] = "\t * @method bool has({$objectClass} \$object)";
		$code[] = "\t * @method bool hasByPrimary(\$primary)";
		$code[] = "\t * @method {$objectClass} getByPrimary(\$primary)";
		$code[] = "\t * @method {$objectClass}[] getAll()";
		$code[] = "\t * @method bool remove({$objectClass} \$object)";
		$code[] = "\t * @method void removeByPrimary(\$primary)";
		$code[] = "\t * @method void fill(\$fields = \\".FieldTypeMask::class."::ALL) flag or array of field names";
		$code[] = "\t * @method static {$collectionClass} wakeUp(\$data)";
		$code[] = "\t * @method \\".Result::class." save(\$ignoreEvents = false)";
		$code[] = "\t * @method void offsetSet() ArrayAccess";
		$code[] = "\t * @method void offsetExists() ArrayAccess";
		$code[] = "\t * @method void offsetUnset() ArrayAccess";
		$code[] = "\t * @method void offsetGet() ArrayAccess";
		$code[] = "\t * @method void rewind() Iterator";
		$code[] = "\t * @method {$objectClass} current() Iterator";
		$code[] = "\t * @method mixed key() Iterator";
		$code[] = "\t * @method void next() Iterator";
		$code[] = "\t * @method bool valid() Iterator";
		$code[] = "\t * @method int count() Countable";
		// xTODO we can put path to the original file here
		$code[] = "\t */";
		$code[] = "\tclass {$collectionDefaultClassName} implements \ArrayAccess, \Iterator, \Countable {";
		$code[] = "\t\t/* @var {$dataClass} */";
		$code[] = "\t\tstatic public \$dataClass = '{$dataClass}';";
		$code[] = "\t}"; // end class

		// compatibility with default classes
		if (mb_strpos($collectionClassName, Entity::DEFAULT_OBJECT_PREFIX) !== 0) // better to compare full classes definitions
		{
			$defaultCollectionClassName = Entity::getDefaultCollectionClassName($entity->getName());

			// no need anymore as far as custom class inherits EO_
			//$code[] = "\tclass_alias('{$entityNamespace}\\{$collectionClassName}', '{$entityNamespace}\\{$defaultCollectionClassName}');";
		}

		$code[] = "}"; // end namespace


		// annotate query and result
		$dataClassName = $entity->getName().'Table';
		$queryClassName = Entity::DEFAULT_OBJECT_PREFIX.$entity->getName().'_Query';
		$resultClassName = Entity::DEFAULT_OBJECT_PREFIX.$entity->getName().'_Result';
		$entityClassName = Entity::DEFAULT_OBJECT_PREFIX.$entity->getName().'_Entity';

		$code[] = "namespace {$entityNamespace} {"; // start namespace
		$code[] = "\t/**";
		$code[] = "\t * @method static {$queryClassName} query()";
		$code[] = "\t * @method static {$resultClassName} getByPrimary(\$primary, array \$parameters = array())";
		$code[] = "\t * @method static {$resultClassName} getById(\$id)";
		$code[] = "\t * @method static {$resultClassName} getList(array \$parameters = array())";
		$code[] = "\t * @method static {$entityClassName} getEntity()";
		$code[] = "\t * @method static {$objectClass} createObject(\$setDefaultValues = true)";
		$code[] = "\t * @method static {$collectionClass} createCollection()";
		$code[] = "\t * @method static {$objectClass} wakeUpObject(\$row)";
		$code[] = "\t * @method static {$collectionClass} wakeUpCollection(\$rows)";
		$code[] = "\t */";
		$code[] = "\tclass {$dataClassName} extends \\".DataManager::class." {}";

		$code[] = "\t/**";
		$code[] = "\t * @method {$resultClassName} exec()";
		$code[] = "\t * @method {$objectClass} fetchObject()";
		$code[] = "\t * @method {$collectionClass} fetchCollection()";
		$code[] = "\t */";
		$code[] = "\tclass {$queryClassName} extends \\".Query::class." {}";

		$code[] = "\t/**";
		$code[] = "\t * @method {$objectClass} fetchObject()";
		$code[] = "\t * @method {$collectionClass} fetchCollection()";
		$code[] = "\t */";
		$code[] = "\tclass {$resultClassName} extends \\".\Bitrix\Main\ORM\Query\Result::class." {}";

		$code[] = "\t/**";
		$code[] = "\t * @method {$objectClass} createObject(\$setDefaultValues = true)";
		$code[] = "\t * @method {$collectionClass} createCollection()";
		$code[] = "\t * @method {$objectClass} wakeUpObject(\$row)";
		$code[] = "\t * @method {$collectionClass} wakeUpCollection(\$rows)";
		$code[] = "\t */";
		$code[] = "\tclass {$entityClassName} extends \\".Entity::class." {}";

		$code[] = "}"; // end namespace

		return join(PHP_EOL, $code);
	}

	public static function annotateScalarField(ScalarField $field)
	{
		// TODO no setter if it is reference-elemental (could expressions become elemental?)

		$objectClass = $field->getEntity()->getObjectClass();
		$getterDataType = $field->getGetterTypeHint();
		$setterDataType = $field->getSetterTypeHint();
		list($lName, $uName) = static::getFieldNameCamelCase($field->getName());

		$objectCode = [];
		$collectionCode = [];

		$objectCode[] = "\t * @method {$getterDataType} get{$uName}()";
		$objectCode[] = "\t * @method {$objectClass} set{$uName}({$setterDataType}|\\".SqlExpression::class." \${$lName})";

		$objectCode[] = "\t * @method bool has{$uName}()";
		$objectCode[] = "\t * @method bool is{$uName}Filled()";
		$objectCode[] = "\t * @method bool is{$uName}Changed()";

		$collectionCode[] = "\t * @method {$getterDataType}[] get{$uName}List()";

		if (!$field->isPrimary())
		{
			$objectCode[] = "\t * @method {$getterDataType} remindActual{$uName}()";
			$objectCode[] = "\t * @method {$getterDataType} require{$uName}()";

			$objectCode[] = "\t * @method {$objectClass} reset{$uName}()";
			$objectCode[] = "\t * @method {$objectClass} unset{$uName}()";

			$objectCode[] = "\t * @method {$getterDataType} fill{$uName}()";
			$collectionCode[] = "\t * @method {$getterDataType}[] fill{$uName}()";
		}

		return [$objectCode, $collectionCode];
	}

	public static function annotateUserType(UserTypeField $field)
	{
		// no setter
		$objectClass = $field->getEntity()->getObjectClass();

		/** @var ScalarField $scalarFieldClass */
		$scalarFieldClass = $field->getValueType();
		$dataType = (new $scalarFieldClass('TMP'))->getSetterTypeHint();
		$dataType = $field->isMultiple() ? $dataType.'[]' : $dataType;
		list($lName, $uName) = static::getFieldNameCamelCase($field->getName());

		list($objectCode, $collectionCode) = static::annotateExpression($field);

		// add setter
		$objectCode[] = "\t * @method {$objectClass} set{$uName}({$dataType} \${$lName})";

		$objectCode[] = "\t * @method bool is{$uName}Changed()";

		return [$objectCode, $collectionCode];
	}

	public static function annotateExpression(ExpressionField $field)
	{
		// no setter
		$objectClass = $field->getEntity()->getObjectClass();

		$scalarFieldClass = $field->getValueType();
		$dataType = (new $scalarFieldClass('TMP'))->getGetterTypeHint();
		list($lName, $uName) = static::getFieldNameCamelCase($field->getName());

		$objectCode = [];
		$collectionCode = [];

		$objectCode[] = "\t * @method {$dataType} get{$uName}()";
		$objectCode[] = "\t * @method {$dataType} remindActual{$uName}()";
		$objectCode[] = "\t * @method {$dataType} require{$uName}()";

		$objectCode[] = "\t * @method bool has{$uName}()";
		$objectCode[] = "\t * @method bool is{$uName}Filled()";

		$collectionCode[] = "\t * @method {$dataType}[] get{$uName}List()";

		$objectCode[] = "\t * @method {$objectClass} unset{$uName}()";

		$objectCode[] = "\t * @method {$dataType} fill{$uName}()";
		$collectionCode[] = "\t * @method {$dataType}[] fill{$uName}()";

		return [$objectCode, $collectionCode];
	}

	public static function annotateReference(Reference $field)
	{
		if (!static::tryToFindEntity($field->getRefEntityName()))
		{
			return [[], []];
		}

		$objectClass = $field->getEntity()->getObjectClass();
		$collectionClass = $field->getEntity()->getCollectionClass();
		$collectionDataType = $field->getRefEntity()->getCollectionClass();

		$getterTypeHint = $field->getGetterTypeHint();
		$setterTypeHint = $field->getSetterTypeHint();

		list($lName, $uName) = static::getFieldNameCamelCase($field->getName());

		$objectCode = [];
		$collectionCode = [];

		$objectCode[] = "\t * @method {$getterTypeHint} get{$uName}()";
		$objectCode[] = "\t * @method {$getterTypeHint} remindActual{$uName}()";
		$objectCode[] = "\t * @method {$getterTypeHint} require{$uName}()";

		$objectCode[] = "\t * @method {$objectClass} set{$uName}({$setterTypeHint} \$object)";
		$objectCode[] = "\t * @method {$objectClass} reset{$uName}()";
		$objectCode[] = "\t * @method {$objectClass} unset{$uName}()";

		$objectCode[] = "\t * @method bool has{$uName}()";
		$objectCode[] = "\t * @method bool is{$uName}Filled()";
		$objectCode[] = "\t * @method bool is{$uName}Changed()";

		$collectionCode[] = "\t * @method {$getterTypeHint}[] get{$uName}List()";
		$collectionCode[] = "\t * @method {$collectionClass} get{$uName}Collection()";

		$objectCode[] = "\t * @method {$getterTypeHint} fill{$uName}()";
		$collectionCode[] = "\t * @method {$collectionDataType} fill{$uName}()";

		return [$objectCode, $collectionCode];
	}

	public static function annotateOneToMany(OneToMany $field)
	{
		if (!static::tryToFindEntity($field->getRefEntityName()))
		{
			return [[], []];
		}

		$objectClass = $field->getEntity()->getObjectClass();
		$collectionDataType = $field->getRefEntity()->getCollectionClass();
		$objectVarName = lcfirst($field->getRefEntity()->getName());

		$setterTypeHint = $field->getSetterTypeHint();

		list($lName, $uName) = static::getFieldNameCamelCase($field->getName());

		$objectCode = [];
		$collectionCode = [];

		$objectCode[] = "\t * @method {$collectionDataType} get{$uName}()";
		$objectCode[] = "\t * @method {$collectionDataType} require{$uName}()";
		$objectCode[] = "\t * @method {$collectionDataType} fill{$uName}()";

		$objectCode[] = "\t * @method bool has{$uName}()";
		$objectCode[] = "\t * @method bool is{$uName}Filled()";
		$objectCode[] = "\t * @method bool is{$uName}Changed()";

		$objectCode[] = "\t * @method void addTo{$uName}({$setterTypeHint} \${$objectVarName})";
		$objectCode[] = "\t * @method void removeFrom{$uName}({$setterTypeHint} \${$objectVarName})";
		$objectCode[] = "\t * @method void removeAll{$uName}()";

		$objectCode[] = "\t * @method {$objectClass} reset{$uName}()";
		$objectCode[] = "\t * @method {$objectClass} unset{$uName}()";

		$collectionCode[] = "\t * @method {$collectionDataType}[] get{$uName}List()";
		$collectionCode[] = "\t * @method {$collectionDataType} get{$uName}Collection()";
		$collectionCode[] = "\t * @method {$collectionDataType} fill{$uName}()";

		return [$objectCode, $collectionCode];
	}

	public static function annotateManyToMany(ManyToMany $field)
	{
		if (!static::tryToFindEntity($field->getRefEntityName()))
		{
			return [[], []];
		}

		$objectClass = $field->getEntity()->getObjectClass();
		$collectionDataType = $field->getRefEntity()->getCollectionClass();
		$objectVarName = lcfirst($field->getRefEntity()->getName());

		$setterTypeHint = $field->getSetterTypeHint();

		list($lName, $uName) = static::getFieldNameCamelCase($field->getName());

		$objectCode = [];
		$collectionCode = [];

		$objectCode[] = "\t * @method {$collectionDataType} get{$uName}()";
		$objectCode[] = "\t * @method {$collectionDataType} require{$uName}()";
		$objectCode[] = "\t * @method {$collectionDataType} fill{$uName}()";

		$objectCode[] = "\t * @method bool has{$uName}()";
		$objectCode[] = "\t * @method bool is{$uName}Filled()";
		$objectCode[] = "\t * @method bool is{$uName}Changed()";

		$objectCode[] = "\t * @method void addTo{$uName}({$setterTypeHint} \${$objectVarName})";
		$objectCode[] = "\t * @method void removeFrom{$uName}({$setterTypeHint} \${$objectVarName})";
		$objectCode[] = "\t * @method void removeAll{$uName}()";

		$objectCode[] = "\t * @method {$objectClass} reset{$uName}()";
		$objectCode[] = "\t * @method {$objectClass} unset{$uName}()";

		$collectionCode[] = "\t * @method {$collectionDataType}[] get{$uName}List()";
		$collectionCode[] = "\t * @method {$collectionDataType} get{$uName}Collection()";
		$collectionCode[] = "\t * @method {$collectionDataType} fill{$uName}()";

		return [$objectCode, $collectionCode];
	}

	public static function tryToFindEntity($entityClass)
	{
		$entityClass = Entity::normalizeEntityClass($entityClass);

		if (!class_exists($entityClass))
		{
			// try to find remote entity
			$classParts = array_values(array_filter(
				explode('\\', mb_strtolower($entityClass))
			));

			if ($classParts[0] == 'bitrix')
			{
				$moduleName = $classParts[1];
			}
			else
			{
				$moduleName = $classParts[0].'.'.$classParts[1];
			}

			if (!Loader::includeModule($moduleName) || !class_exists($entityClass))
			{
				return false;
			}
		}

		return true;
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

	protected static function getFieldNameCamelCase($fieldName)
	{
		$upperFirstName = StringHelper::snake2camel($fieldName);
		$lowerFirstName = lcfirst($upperFirstName);

		return [$lowerFirstName, $upperFirstName];
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