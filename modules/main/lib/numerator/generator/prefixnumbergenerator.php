<?php
namespace Bitrix\Main\Numerator\Generator;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Numerator\Generator\Contract\UserConfigurable;
use Bitrix\Main\Result;

Loc::loadMessages(__FILE__);

/**
 * Class PrefixNumberGenerator - replace prefix in numerator template
 * @package Bitrix\Main\Numerator\Generator
 */
class PrefixNumberGenerator extends NumberGenerator implements UserConfigurable
{
	protected $prefix;
	const TEMPLATE_WORD_PREFIX = 'PREFIX';

	/** @inheritdoc */
	public function setConfig($config)
	{
		$this->setFromArrayOrDefault('prefix', $config, '');
	}

	/** @inheritdoc */
	public function getConfig()
	{
		return ['prefix' => $this->prefix];
	}

	/**
	 * @return string
	 */
	public static function getAvailableForType()
	{
		return 'DEFAULT';
	}

	/** @inheritdoc */
	public function parseTemplate($template)
	{
		return str_replace(static::getPatternFor(static::TEMPLATE_WORD_PREFIX), $this->prefix, $template);
	}

	/** @inheritdoc */
	public static function getTemplateWordsForParse()
	{
		return [static::getPatternFor(static::TEMPLATE_WORD_PREFIX)];
	}

	/** @inheritdoc */
	public static function getTemplateWordsSettings()
	{
		return [
			static::getPatternFor(static::TEMPLATE_WORD_PREFIX) =>
				Loc::getMessage('BITRIX_MAIN_NUMERATOR_GENERATOR_PREFIXNUMBERGENERATOR_WORD_PREFIX'),
		];
	}

	/** @inheritdoc */
	public static function getSettingsFields()
	{
		return [
			[
				'settingName' => 'prefix',
				'type'        => 'string',
				'title'       => static::getPrefixSettingsTitle(),
			],
		];
	}

	/**
	 * @return string
	 */
	protected static function getPrefixSettingsTitle()
	{
		return Loc::getMessage('TITLE_BITRIX_MAIN_NUMERATOR_GENERATOR_PREFIXNUMBERGENERATOR_PREFIX');
	}

	/** @inheritdoc */
	public function validateConfig($config)
	{
		$result = new Result();
		return $result;
	}
}