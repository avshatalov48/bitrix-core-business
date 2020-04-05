<?
namespace Bitrix\Main\Numerator;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

/**
 * Class NumberGeneratorFactory
 * @package Bitrix\Main\Numerator
 */
class NumberGeneratorFactory
{
	const EVENT_GENERATOR_CLASSES_COLLECT = 'onNumberGeneratorsClassesCollect';

	/**
	 * returns generator object, created based on its class
	 * @param $type
	 * @return mixed
	 */
	public function createGeneratorByType($type)
	{
		$class = $this->getClassByType($type);

		return new $class();
	}

	/**
	 * returns array where the keys are types of generators
	 * and the values are generators classes
	 * @return array
	 */
	public function getTypeToClassMap()
	{
		static $generatorTypeToClassMap = [];
		if (empty($generatorTypeToClassMap))
		{
			$generatorClasses = [
				Generator\SequentNumberGenerator::class,
				Generator\DateNumberGenerator::class,
				Generator\RandomNumberGenerator::class,
				Generator\PrefixNumberGenerator::class,
			];
			$generatorClasses = array_merge_recursive($generatorClasses, $this->collectCustomGeneratorClasses());

			foreach ($generatorClasses as $generatorClass)
			{
				/** @var Generator\NumberGenerator $generatorClass */
				$generatorTypeToClassMap[$generatorClass::getType()] = $generatorClass;
			}
		}

		return $generatorTypeToClassMap;
	}

	/**
	 * @return string[]
	 */
	private function collectCustomGeneratorClasses()
	{
		$event = new Event('main', self::EVENT_GENERATOR_CLASSES_COLLECT);
		$event->send();
		$results = $event->getResults();

		$generatorClasses = [];
		foreach ($results as $result)
		{
			if ($result->getType() != EventResult::SUCCESS)
			{
				continue;
			}
			$className = $result->getParameters();

			if (class_exists($className) && (is_subclass_of($className, Generator\NumberGenerator::class)))
			{
				$generatorClasses[] = $result->getParameters();
			}
		}

		return $generatorClasses;
	}

	/**
	 * returns class of generator by its type
	 * @param $type
	 * @return mixed|null
	 */
	public function getClassByType($type)
	{
		if (in_array($type, array_keys($this->getTypeToClassMap())))
		{
			return $this->getTypeToClassMap()[$type];
		}

		return null;
	}

	/**
	 * returns all available generators classes
	 * @return array
	 */
	public function getClasses()
	{
		return array_values($this->getTypeToClassMap());
	}
}