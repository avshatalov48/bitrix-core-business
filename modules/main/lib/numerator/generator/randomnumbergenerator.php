<?
namespace Bitrix\Main\Numerator\Generator;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Numerator\Generator\Contract\UserConfigurable;
use Bitrix\Main\Result;

Loc::loadMessages(__FILE__);

/**
 * Class RandomNumberGenerator - replaces random symbol's sequence in numerator template
 * @package Bitrix\Main\Numerator\Generator
 */
class RandomNumberGenerator extends NumberGenerator implements UserConfigurable
{
	protected $length;
	const TEMPLATE_WORD_RANDOM = 'RANDOM';

	/** @inheritdoc */
	public function setConfig($config)
	{
		$this->setFromArrayOrDefault('length', $config, 5, 'int');
	}

	/** @inheritdoc */
	public function getConfig()
	{
		return ['length' => $this->length,];
	}

	/** @inheritdoc */
	public function parseTemplate($template)
	{
		return str_replace(static::getPatternFor(static::TEMPLATE_WORD_RANDOM), $this->generateRandomString(), $template);
	}

	/** @inheritdoc */
	public static function getTemplateWordsForParse()
	{
		return [static::getPatternFor(static::TEMPLATE_WORD_RANDOM)];
	}

	/**
	 * @return bool|string
	 */
	private function generateRandomString()
	{
		return randString($this->length, ['ABCDEFGHIJKLNMOPQRSTUVWXYZ', '0123456789']);
	}

	/**
	 * @return string
	 */
	public static function getAvailableForType()
	{
		return 'DEFAULT';
	}

	/** @inheritdoc */
	public static function getTemplateWordsSettings()
	{
		return [
			static::getPatternFor(static::TEMPLATE_WORD_RANDOM) =>
				Loc::getMessage('BITRIX_MAIN_NUMERATOR_GENERATOR_RANDOMNUMBERGENERATOR_WORD_RANDOM'),
		];
	}

	/** @inheritdoc */
	public static function getSettingsFields()
	{
		return [
			[
				'settingName' => 'length',
				'type'        => 'int',
				'default'     => 5,
				'title'       => Loc::getMessage('TITLE_BITRIX_MAIN_NUMERATOR_GENERATOR_RANDOMNUMBERGENERATOR_LENGTH'),
			],
		];
	}

	/** @inheritdoc */
	public function validateConfig($config)
	{
		$result = new Result();
		return $result;
	}
}