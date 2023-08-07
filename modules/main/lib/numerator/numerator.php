<?

namespace Bitrix\Main\Numerator;

use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Error;
use Bitrix\Main\Event;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Numerator\Generator\Contract\DynamicConfigurable;
use Bitrix\Main\Numerator\Generator\Contract\Sequenceable;
use Bitrix\Main\Numerator\Generator\Contract\UserConfigurable;
use Bitrix\Main\Numerator\Generator\NumberGenerator;
use Bitrix\Main\Numerator\Model\NumeratorSequenceTable;
use Bitrix\Main\Numerator\Model\NumeratorTable;
use Bitrix\Main\Result;

Loc::loadMessages(__FILE__);

/**
 * Class Numerator - generates numbers based on config,
 * is used for creating random, sequential numbers, also may contain prefix, date values etc.
 * @package Bitrix\Main\Numerator
 */
class Numerator
{
	private $template;
	private $type;
	private $name;
	/** @var NumberGenerator[] */
	private $generators = [];
	private $code;
	private $id;

	const NUMERATOR_DEFAULT_TYPE = 'DEFAULT';
	const NUMERATOR_ALL_GENERATORS_TYPE = 'ALL';
	/** * @var NumberGeneratorFactory */
	static protected $numberGeneratorFactory;

	/** return empty numerator object with no configuration
	 * @return static
	 */
	public static function create()
	{
		return new static();
	}

	private function __construct()
	{
	}

	/**
	 * @return NumberGeneratorFactory
	 */
	protected static function getNumberGeneratorFactory()
	{
		if (static::$numberGeneratorFactory === null)
		{
			static::$numberGeneratorFactory = new NumberGeneratorFactory();
		}
		return static::$numberGeneratorFactory;
	}

	/**
	 * @param $numeratorType
	 * @return mixed
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\NotImplementedException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getSettingsFields($numeratorType)
	{
		$numeratorsAmount = static::getNextNumeratorNumber($numeratorType);
		$settings = ['settingsFields' => [], 'settingsWords' => [],];
		$settings['settingsFields'][static::getType()] = [
			[
				'title' => Loc::getMessage('TITLE_BITRIX_MAIN_NUMERATOR_NUMERATOR_NAME_TITLE'),
				'settingName' => 'name',
				'type' => 'string',
				'default' => Loc::getMessage('NUMERATOR_DEFAULT_NUMERATOR_NAME', ['#NUMBER#' => $numeratorsAmount]),
			],
			[
				'settingName' => 'template',
				'type' => 'string',
				'title' => Loc::getMessage('TITLE_BITRIX_MAIN_NUMERATOR_NUMERATOR_TEMPLATE_TITLE'),
			],
		];
		$allGeneratorsClasses = static::getNumberGeneratorFactory()->getClasses();
		foreach ($allGeneratorsClasses as $class)
		{
			/** @var $class NumberGenerator|UserConfigurable */
			$isAvailableForAll = $class::getAvailableForType() == static::NUMERATOR_DEFAULT_TYPE;
			if ($isAvailableForAll || $class::getAvailableForType() == $numeratorType)
			{
				if (in_array(UserConfigurable::class, class_implements($class)))
				{
					$settings['settingsFields'][$class::getType()] = $class::getSettingsFields();
				}
				$settings['settingsWords'][$class::getType()] = $class::getTemplateWordsSettings();
			}
		}
		$settings['settingsWords'] = array_merge_recursive($settings['settingsWords'], static::getUserDefinedTemplateWords($numeratorType));

		return $settings;
	}

	/** For compatibility - users can defined their own type of template generation
	 * @param $numeratorType
	 * @return array
	 */
	protected static function getUserDefinedTemplateWords($numeratorType)
	{
		$settingsWords = [];
		$event = new Event('main', 'onBuildNumeratorTemplateWordsList', ['numeratorType' => $numeratorType]);
		$event->send();

		if ($event->getResults())
		{
			$count = 0;
			foreach ($event->getResults() as $eventResult)
			{
				$eventParameters = $eventResult->getParameters();
				if (isset($eventParameters['CODE']) && isset($eventParameters['NAME']))
				{
					$settingsWords["UserDefinedVirtualGenerator" . $count++] = [
						NumberGenerator::USER_DEFINED_SYMBOL_START . $eventParameters['CODE'] . NumberGenerator::USER_DEFINED_SYMBOL_END
						=> $eventParameters['NAME'],
					];
				}
				else
				{
					foreach ($eventParameters as $parameters)
					{
						if (isset($parameters['CODE']) && isset($parameters['NAME']))
						{
							$settingsWords["UserDefinedVirtualGenerator" . $count++] = [
								NumberGenerator::USER_DEFINED_SYMBOL_START . $parameters['CODE'] . NumberGenerator::USER_DEFINED_SYMBOL_END
								=> $parameters['NAME'],
							];
						}
					}
				}
			}
		}
		return $settingsWords;
	}

	/**
	 * @param string $type
	 * @param null $sort
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getListByType($type = null, $sort = null)
	{
		if (is_null($type))
		{
			$type = static::NUMERATOR_DEFAULT_TYPE;
		}
		return NumeratorTable::getNumeratorList($type, $sort);
	}

	/** Returns numerator related fields from db by its type
	 * (use it in case of only single one exists for the type)
	 * @param string $type
	 * @return array|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getOneByType($type = null)
	{
		if (is_null($type))
		{
			$type = static::NUMERATOR_DEFAULT_TYPE;
		}
		$numeratorSettings = static::getListByType($type);
		if ($numeratorSettings && isset($numeratorSettings[0]))
		{
			return $numeratorSettings[0];
		}
		return null;
	}

	/** returns all template words for creating new numerator depending on its type
	 * @param string $isAvailableByType
	 * @return array
	 * @throws \Bitrix\Main\NotImplementedException
	 */
	public static function getTemplateWordsForType($isAvailableByType = null)
	{
		if (is_null($isAvailableByType))
		{
			$isAvailableByType = static::NUMERATOR_DEFAULT_TYPE;
		}
		$settings = [];
		$allGeneratorsClasses = static::getNumberGeneratorFactory()->getClasses();
		foreach ($allGeneratorsClasses as $class)
		{
			/** @var $class NumberGenerator */
			$isAllTypesNeeded = $isAvailableByType === static::NUMERATOR_ALL_GENERATORS_TYPE;
			$isAvailableByDefault = $class::getAvailableForType() == static::NUMERATOR_DEFAULT_TYPE;
			if ($isAllTypesNeeded || $isAvailableByDefault || $class::getAvailableForType() == $isAvailableByType)
			{
				$settings = array_merge($settings, [$class::getType() => $class::getTemplateWordsForParse()]);
			}
		}
		return $settings;
	}

	/**
	 * @param $hash
	 */
	private function setNumberHashForGenerators($hash)
	{
		foreach ($this->generators as $index => $generator)
		{
			if ($generator instanceof Sequenceable)
			{
				$generator->setNumberHash($hash);
			}
		}
	}

	/** return next number. If numerator has {NUMBER} in template,
	 * Sequential counter value in database will be updated
	 * If you need next number for preview only, use previewNextNumber
	 * @param string|int $hash - you can reuse one numerator in various cases (for various companies etc.)
	 * by passing different hashes to it. For Sequential number it means using independent counters for every hash
	 * Hash will be ignored here, if it was already set in Load method or via setHash
	 * @return string
	 * @see Numerator::setHash()
	 * @see Numerator::previewNextNumber()
	 */
	public function getNext($hash = null)
	{
		$this->setNumberHashForGenerators($hash);
		$nextNumber = $this->template;
		foreach ($this->generators as $index => $generator)
		{
			/** @var $generator NumberGenerator */
			$nextNumber = $generator->parseTemplate($nextNumber);
		}

		return $nextNumber;
	}

	/**
	 * @param $dynamicConfig
	 */
	private function setDynamicConfigForGenerators($dynamicConfig)
	{
		if ($dynamicConfig !== null)
		{
			foreach ($this->generators as $generator)
			{
				if ($generator instanceof DynamicConfigurable)
				{
					$generator->setDynamicConfig($dynamicConfig);
				}
			}
		}
	}

	/**
	 * @param $numId
	 * @param $config - same configuration structure as using via setConfig method
	 * @return \Bitrix\Main\Entity\AddResult|\Bitrix\Main\Entity\UpdateResult|Result
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\NotImplementedException
	 * @throws \Bitrix\Main\ObjectException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function update($numId, $config)
	{
		$numerator = static::create();
		$config[static::getType()]['idFromDb'] = $numId;
		$result = $numerator->setNumeratorConfig($config);
		if ($result->isSuccess())
		{
			$result = $numerator->setGeneratorsConfig($config);
			if ($result->isSuccess())
			{
				return $numerator->save();
			}
		}
		return $result;
	}

	/**
	 * @param $hashable - object that returns hash string
	 * Used for Numerators containing Sequential number
	 * Hash can be set once, will be ignored here, if it was already set
	 * Typically hash is a string like COMPANY_64, or USER_42
	 */
	public function setHash($hashable)
	{
		if ($hashable instanceof Hashable)
		{
			$this->setNumberHashForGenerators($hashable->getHash());
		}
	}

	/**
	 * @param $dynamicConfig - anything (array|object|..) that will be used by DynamicConfigurable generators
	 * of Numerator for parsing template and creating number
	 */
	public function setDynamicConfig($dynamicConfig)
	{
		$this->setDynamicConfigForGenerators($dynamicConfig);
	}

	/**
	 * @return \Bitrix\Main\Entity\AddResult|\Bitrix\Main\Entity\UpdateResult
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function save()
	{
		$settingsToStore = $this->getSettings();
		$result = NumeratorTable::saveNumerator($this->id, [
			'CODE' => $this->code,
			'NAME' => $this->name,
			'TEMPLATE' => $this->template,
			'TYPE' => $this->type ? $this->type : static::NUMERATOR_DEFAULT_TYPE,
			'SETTINGS' => $settingsToStore,
		]);
		if ($result->isSuccess())
		{
			$this->id = $result->getId();
		}

		return $result;
	}

	/**
	 * @return array
	 */
	private function getSettings()
	{
		$settingsToStore = [];
		foreach ($this->generators as $numberGenerator)
		{
			if ($numberGenerator instanceof UserConfigurable)
			{
				/** @var UserConfigurable $numberGenerator */
				$settingsToStore = array_merge($settingsToStore, [$this->getTypeOfGenerator($numberGenerator) => $numberGenerator->getConfig(),]);
			}
		}
		return $settingsToStore;
	}

	/** Load numerator by id
	 * @param $numeratorId
	 * @param $source - optional, numerator dynamicConfig for generating next number,
	 * also can be Hashable ancestor for set up hash for numerator
	 * @return null|static
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 * @see Numerator::getNext()
	 */
	public static function load($numeratorId, $source = null)
	{
		if ($config = NumeratorTable::loadSettings($numeratorId))
		{
			$numerator = new static();
			$result = $numerator->setConfig($config);
			if (($result->isSuccess()))
			{
				$numerator->setDynamicConfig($source);
				$numerator->setHash($source);
				return $numerator;
			}
		}
		return null;
	}

	/**
	 * @param $code
	 * @param $source
	 * @return static|null
	 */
	public static function loadByCode($code, $source = null)
	{
		$id = NumeratorTable::getIdByCode($code);
		if ($id === null)
		{
			return null;
		}

		return self::load($id, $source);
	}

	/**
	 * @param $id
	 * @return $this|\Bitrix\Main\Entity\DeleteResult|Result
	 * @throws \Exception
	 */
	public static function delete($id)
	{
		if (!$id)
		{
			return (new Result())->addError(new Error('Numerator id is required'));
		}
		$result = NumeratorTable::delete((int)$id);
		if ($result->isSuccess())
		{
			NumeratorSequenceTable::deleteByNumeratorId($id);
		}
		return $result;
	}

	/** return next number, without updating database value (for numerator with sequential number)
	 * @param null $hash
	 * @return string
	 */
	public function previewNextNumber($hash = null)
	{
		$this->setNumberHashForGenerators($hash);
		$nextNumber = $this->template;
		foreach ($this->generators as $index => $generator)
		{
			/** @var $generator NumberGenerator */
			$nextNumber = $generator->parseTemplateForPreview($nextNumber);
		}

		return $nextNumber;
	}

	/**
	 * returns next sequential number, if numerator has sequence,
	 * null if it hasn't
	 * not increases the sequent number
	 * @param string $hash
	 * @return int|null
	 */
	public function previewNextSequentialNumber($hash = null)
	{
		$this->setNumberHashForGenerators($hash);
		foreach ($this->generators as $generator)
		{
			if ($generator instanceof Sequenceable)
			{
				return $generator->getNextNumber($this->id);
			}
		}
		return null;
	}

	/** check if numerator has {NUMBER} in template
	 * @return bool
	 */
	public function hasSequentialNumber()
	{
		foreach ($this->generators as $generator)
		{
			if ($generator instanceof Sequenceable)
			{
				return true;
			}
		}
		return false;
	}

	/**
	 * The only way to affect the NEXT number
	 * - function forces the numerator to start counting with a given number
	 * @param int $nextNumber
	 * @param $whereNumber - old value of next number
	 * @param string $hash
	 * @return Result
	 */
	public function setNextSequentialNumber($nextNumber, $whereNumber = null, $hash = null)
	{
		$this->setNumberHashForGenerators($hash);
		foreach ($this->generators as $generator)
		{
			if ($generator instanceof Sequenceable)
			{
				return $generator->setNextNumber($this->id, $nextNumber, $whereNumber);
			}
		}
		return (new Result())->addError(new Error(Loc::getMessage('NUMERATOR_SET_SEQUENTIAL_IS_IMPOSSIBLE')));
	}

	/** return numerator's configuration with filled in values for every setting
	 * @return array
	 */
	public function getConfig()
	{
		$selfConfig = [
			static::getType() => [
				'name' => $this->name,
				'template' => $this->template,
				'id' => $this->id,
				'code' => $this->code,
				'type' => $this->type,
			],
		];
		$generatorConfigs = [];
		foreach ($this->generators as $generator)
		{
			if ($generator instanceof UserConfigurable)
			{
				$generatorConfigs[$this->getTypeOfGenerator($generator)] = $generator->getConfig();
			}
		}
		return $selfConfig + $generatorConfigs;
	}

	/** sets configuration for numerator and validates settings
	 * @param $config
	 * @return Result - message that can be shown to an end user
	 * @throws \Bitrix\Main\NotImplementedException
	 */
	public function setConfig($config)
	{
		$result = $this->setNumeratorConfig($config);
		if (!$result->isSuccess())
		{
			return $result;
		};

		return $this->setGeneratorsConfig($config);
	}

	/**
	 * @param $config
	 * @return Result
	 */
	private function setNumeratorConfig($config)
	{
		$result = $this->validate($config);
		if (!$result->isSuccess())
		{
			return $result;
		};
		$this->type = trim($config[static::getType()]['type']);
		$this->setTemplate($config[static::getType()]['template']);
		$this->name = trim($config[static::getType()]['name']);
		if (isset($config[static::getType()]['idFromDb']))
		{
			$this->id = $config[static::getType()]['idFromDb'];
		}
		if (array_key_exists('code', $config[static::getType()]))
		{
			$code = $config[static::getType()]['code'];
			if (is_string($code))
			{
				$code = trim($code);
			}

			$this->code = (is_string($code) && !empty($code)) ? $code : null;
		}
		return $result;
	}

	private function createGenerators()
	{
		$generatorTypesToCreate = $this->getGeneratorTypesByTemplate();
		if ($this->type === static::NUMERATOR_ALL_GENERATORS_TYPE)
		{
			return $this->createGeneratorsOfTypes($generatorTypesToCreate);
		}

		$factory = static::getNumberGeneratorFactory();
		$typesForCurrentNumerator = [];
		foreach ($generatorTypesToCreate as $index => $generatorType)
		{
			$generatorClass = $factory->getClassByType($generatorType);
			if ($generatorClass::getAvailableForType() === $this->type
				|| $generatorClass::getAvailableForType() === static::NUMERATOR_DEFAULT_TYPE
			)
			{
				$typesForCurrentNumerator[] = $generatorType;
			}
		}

		return $this->createGeneratorsOfTypes($typesForCurrentNumerator);
	}

	/**
	 * @param $config
	 * @return Result
	 * @throws \Bitrix\Main\NotImplementedException
	 */
	private function setGeneratorsConfig($config)
	{
		$generators = $this->createGenerators();
		foreach ($generators as $index => $generator)
		{
			$this->addGenerator($generator);
		}
		$result = $this->validateGeneratorsConfig($config);

		if ($result->isSuccess())
		{
			foreach ($this->generators as $generator)
			{
				if ($generator instanceof UserConfigurable)
				{
					/** @var UserConfigurable $generator */
					$generator->setConfig($config[$this->getTypeOfGenerator($generator)] ?? null);
				}
			}
		};

		return $result;
	}

	/** type string used as key in configuration arrays
	 * @return string
	 */
	public static function getType()
	{
		return str_replace('\\', '_', static::class);
	}

	/**
	 * @param $template
	 */
	protected function setTemplate($template)
	{
		$this->template = str_replace(["\r\n", "\n"], '', trim($template));
	}

	/**
	 * @return array
	 * @throws \Bitrix\Main\NotImplementedException
	 */
	private function getTypeToTemplateWords()
	{
		$result = [];
		$typesToClasses = static::getNumberGeneratorFactory()->getTypeToClassMap();
		foreach ($typesToClasses as $type => $class)
		{
			/** @var NumberGenerator $class */
			$result[$type] = $class::getTemplateWordsForParse();
		}
		return $result;
	}

	/**
	 * @return array
	 * @throws \Bitrix\Main\NotImplementedException
	 */
	private function getGeneratorTypesByTemplate()
	{
		$generatorTypes = [];
		foreach ($this->getTypeToTemplateWords() as $type => $words)
		{
			foreach ($words as $word)
			{
				if (mb_stripos($this->template, $word) !== false)
				{
					$generatorTypes[$type] = 1;
				}
			}
		}

		return array_keys($generatorTypes);
	}

	/**
	 * @param $generatorTypesToCreate
	 * @return array
	 */
	private function createGeneratorsOfTypes($generatorTypesToCreate)
	{
		$generators = [];
		foreach ($generatorTypesToCreate as $key => $type)
		{
			if ($generator = static::getNumberGeneratorFactory()->createGeneratorByType($type))
			{
				$generators[] = $generator;
			}
		}

		return $generators;
	}

	/**
	 * @param $numeratorConfig
	 * @return Result
	 */
	private function validate($numeratorConfig)
	{
		$result = new Result();
		if (!isset($numeratorConfig[static::getType()]))
		{
			$result->addError(new Error('Numerator config is required'));
		}
		$numeratorBaseConfig = $numeratorConfig[static::getType()];

		if (isset($numeratorBaseConfig['code']))
		{
			if (is_string($numeratorBaseConfig['code']) && !empty($numeratorBaseConfig['code']))
			{
				$idWithSameCode = NumeratorTable::getIdByCode($numeratorBaseConfig['code']);
				if ($idWithSameCode !== null)
				{
					$id = (int)($numeratorBaseConfig['idFromDb'] ?? null);
					if ($id <= 0 || $idWithSameCode !== $id)
					{
						$result->addError(new Error('Another numerator with same code already exists'));
					}
				}
			}
			elseif (is_string($numeratorBaseConfig['code']))
			{
				$result->addError(new Error('Numerator code should be a non-empty string, if it is provided'));
			}
			else
			{
				$result->addError(new Error('Numerator code should be a string'));
			}
		}
		if (!(isset($numeratorBaseConfig['name']) && $numeratorBaseConfig['name']))
		{
			$result->addError(new Error(Loc::getMessage('NUMERATOR_VALIDATE_NAME_IS_REQUIRED')));
		}
		$resultTemplate = $this->validateTemplate($numeratorBaseConfig['template']);
		if ($resultTemplate->getErrors())
		{
			$result->addErrors($resultTemplate->getErrors());
		}

		return $result;
	}

	/**
	 * @param string $template
	 * @return Result
	 */
	private function validateTemplate($template)
	{
		if (!($template && $template != ''))
		{
			return (new Result())->addError(new Error(Loc::getMessage('NUMERATOR_VALIDATE_TEMPLATE_IS_REQUIRED')));
		}
		return new Result();
	}

	/**
	 * @param $isAvailableForType
	 * @return int
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private static function getNextNumeratorNumber($isAvailableForType)
	{
		$query = new Query(NumeratorTable::getEntity());
		$query->addSelect(new ExpressionField('COUNT', 'COUNT(*)'));
		$query->where('TYPE', $isAvailableForType);
		$result = $query->exec()->fetch();
		return $result ? $result['COUNT'] + 1 : 1;
	}

	/**
	 * @param $config
	 * @return Result
	 */
	private function validateGeneratorsConfig($config)
	{
		$result = new Result();
		foreach ($this->generators as $generator)
		{
			if ($generator instanceof UserConfigurable)
			{
				$generatorResult = ($generator->validateConfig($config));
				if ($generatorResult->getErrors())
				{
					$result->addErrors($generatorResult->getErrors());
				}
			}
		}

		return $result;
	}

	/**
	 * @param $generator
	 */
	private function addGenerator($generator)
	{
		$this->generators[] = $generator;
	}

	/**
	 * @param $generator
	 * @return string
	 */
	private function getTypeOfGenerator($generator)
	{
		/** @var NumberGenerator $generatorClass */
		$generatorClass = get_class($generator);
		return $generatorClass::getType();
	}

	public function getId()
	{
		return $this->id;
	}
}
