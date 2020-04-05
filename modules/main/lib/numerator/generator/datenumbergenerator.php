<?
namespace Bitrix\Main\Numerator\Generator;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\Date;

Loc::loadMessages(__FILE__);

/**
 * Class DateNumberGenerator - is responsible for parsing date values in numerator's template
 * @package Bitrix\Main\Numerator\Generator
 */
class DateNumberGenerator extends NumberGenerator
{
	const TEMPLATE_WORD_YEAR  = 'YEAR';
	const TEMPLATE_WORD_MONTH = 'MONTH';
	const TEMPLATE_WORD_DAY   = 'DAY';

	/** @inheritdoc */
	public static function getTemplateWordsForParse()
	{
		return [
			static::getPatternFor(static::TEMPLATE_WORD_DAY),
			static::getPatternFor(static::TEMPLATE_WORD_MONTH),
			static::getPatternFor(static::TEMPLATE_WORD_YEAR),
		];
	}

	/** @inheritdoc */
	public static function getTemplateWordsSettings()
	{
		return [
			static::getPatternFor(static::TEMPLATE_WORD_DAY)   =>
				Loc::getMessage('BITRIX_MAIN_NUMERATOR_GENERATOR_DATENUMBERGENERATOR_WORD_DAY'),
			static::getPatternFor(static::TEMPLATE_WORD_MONTH) =>
				Loc::getMessage('BITRIX_MAIN_NUMERATOR_GENERATOR_DATENUMBERGENERATOR_WORD_MONTH'),
			static::getPatternFor(static::TEMPLATE_WORD_YEAR)  =>
				Loc::getMessage('BITRIX_MAIN_NUMERATOR_GENERATOR_DATENUMBERGENERATOR_WORD_YEAR'),
		];
	}

	/** @inheritdoc */
	public function parseTemplate($template)
	{
		$wordDay = date(Date::convertFormatToPhp(str_replace(["MM", "YYYY"], "",\CSite::GetDateFormat("SHORT"))), mktime(0, 0, 0, date("m"), date("d"), date("Y")));
		$wordDay = preg_replace("/[^0-9]/", "", $wordDay);
		$wordMonth = date(Date::convertFormatToPhp(str_replace(["DD", "YYYY"], "", \CSite::GetDateFormat("SHORT"))), mktime(0, 0, 0, date("m"), date("d"), date("Y")));
		$wordMonth = preg_replace("/[^0-9]/", "", $wordMonth);
		$template = str_replace(static::getPatternFor(static::TEMPLATE_WORD_DAY), $wordDay, $template);
		$template = str_replace(static::getPatternFor(static::TEMPLATE_WORD_MONTH), $wordMonth, $template);
		$template = str_replace(static::getPatternFor(static::TEMPLATE_WORD_YEAR), date('Y'), $template);

		return $template;
	}

	/**
	 * @return string
	 */
	public static function getAvailableForType()
	{
		return 'DEFAULT';
	}
}