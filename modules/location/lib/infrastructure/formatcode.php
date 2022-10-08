<?php

namespace Bitrix\Location\Infrastructure;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Event;

/**
 * Class CurrentFormatCode
 * @package Bitrix\Location\Entity\Format
 * Responsible for the setting and obtaining the format code.
 */
class FormatCode
{
	protected static $optionName = 'address_format_code';
	protected static $onChangedEventName = 'onCurrentFormatCodeChanged';

	public static function getCurrent(string $languageId = LANGUAGE_ID, string $siteId = ''): string
	{
		return Option::get(
			'location',
			static::$optionName,
			static::getDefault($languageId),
			$siteId
		);
	}

	public static function setCurrent(string $formatCode, string $siteId = ''): void
	{
		Option::set(
			'location',
			static::$optionName,
			$formatCode,
			$siteId
		);

		$event = new Event('location', static::$onChangedEventName, ['formatCode' => $formatCode]);
		$event->send();
	}

	/**
	 * @param string $languageId
	 * @return string
	 * copy & paste to location/default_option.php after the change.
	 */
	public static function getDefault(string $languageId = LANGUAGE_ID): string
	{
		switch ($languageId)
		{
			case 'kz':
				$result = 'RU_2';
				break;

			case 'de':
				$result = 'EU';
				break;

			case 'en':
				$result = 'US';
				break;

			//case 'ru':
			//case 'by':
			//case 'ua':
			default:
				$result = 'RU';
		}

		return $result;
	}
}