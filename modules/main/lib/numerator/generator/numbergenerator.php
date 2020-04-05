<?php
namespace Bitrix\Main\Numerator\Generator;

use Bitrix\Main\EventResult;
use Bitrix\Main\NotImplementedException;

/**
 * Class NumberGenerator
 * @package Bitrix\Main\Numerator
 */
abstract class NumberGenerator
{
	const USER_DEFINED_SYMBOL_START = '{USER_DEFINED:';
	const USER_DEFINED_SYMBOL_END   = '}';
	const SYMBOL_START              = '{';
	const SYMBOL_END                = '}';

	/** replace specific symbol (that generator is responsible for)
	 * with some string by internal logic
	 * @param $template
	 * @return string after parse
	 */
	public abstract function parseTemplate($template);

	/** Must not affect internal counters and keep storage value unchanged,
	 * by default - same logic as in parseTemplate
	 * @param $template
	 * @return string
	 */
	public function parseTemplateForPreview($template)
	{
		return $this->parseTemplate($template);
	}

	/**
	 * return type of numerator that this generator can work with
	 * @throws NotImplementedException
	 */
	public static function getAvailableForType()
	{
		throw new NotImplementedException(static::class . ':' . __FUNCTION__ . ' is not implemented');
	}

	/**
	 * return array of words that can be parsed by generator
	 * @throws NotImplementedException
	 */
	public static function getTemplateWordsForParse()
	{
		throw new NotImplementedException(static::class . ':' . __FUNCTION__ . ' is not implemented');
	}

	/**
	 * return array, where keys are words of generator
	 * and values are corresponding titles for showing to end user for each word
	 * e.g. {PREFIX} => 'prefix'
	 * @throws NotImplementedException
	 */
	public static function getTemplateWordsSettings()
	{
		throw new NotImplementedException(static::class . ':' . __FUNCTION__ . ' is not implemented');
	}

	/**
	 * in case of inheritance (adding new custom generator)
	 * you should register this function of your class as module Dependency
	 * for module 'main' and event NumberGeneratorFactory::EVENT_GENERATOR_CLASSES_COLLECT
	 * @see NumberGeneratorFactory::EVENT_GENERATOR_CLASSES_COLLECT
	 * @return string - static class name
	 */
	public function onGeneratorClassesCollect()
	{
		return new EventResult(EventResult::SUCCESS, static::class);
	}

	/**
	 * @return string
	 */
	public static function getType()
	{
		return str_replace('\\', '_', static::class);
	}

	/**
	 * @param $value
	 * @param $config
	 * @param null $default
	 * @param null $type
	 */
	protected function setFromArrayOrDefault($value, $config, $default = null, $type = null)
	{
		if (property_exists(static::class, $value))
		{
			if (isset($config[$value]) && $config[$value])
			{
				if ($type === 'int')
				{
					$this->$value = intval($config[$value]);
					return;
				}
				if ($type === 'bool')
				{
					$this->$value = (bool)$config[$value];
					return;
				}
				$this->$value = $config[$value];
			}
			else
			{
				$this->$value = $default;
			}
		}
	}

	/**
	 * @param $word
	 * @return string
	 */
	protected static function getPatternFor($word)
	{
		return self::SYMBOL_START . $word . self::SYMBOL_END;
	}
}