<?php

namespace Bitrix\Location\Infrastructure;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Event;
use Bitrix\Main\Application;

/**
 * Class CurrentFormatCode
 * @package Bitrix\Location\Entity\Format
 * Responsible for the setting and obtaining the format code.
 */
class FormatCode
{
	protected static $optionName = 'address_format_code';
	protected static $onChangedEventName = 'onCurrentFormatCodeChanged';

	public static function getCurrent(string $regionId = null, string $siteId = ''): string
	{
		return Option::get(
			'location',
			static::$optionName,
			static::getDefault($regionId),
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
	 * @param string|null $regionId
	 * @return string
	 */
	public static function getDefault(string $regionId = null): string
	{
		$regionId = $regionId ?? static::getRegion();

		$map = [
			'kz' => 'RU_2',
			'en' => 'US',
			'eu' => 'EU',
			'de' => 'EU',
			'la' => 'EU',
			'br' => 'BR',
			'fr' => 'EU',
			'it' => 'EU',
			'pl' => 'EU',
			'uk' => 'UK',
		];

		return $map[$regionId] ?? 'RU';
	}

	/**
	 * @return string
	 */
	private static function getRegion(): string
	{
		$region = Application::getInstance()->getLicense()->getRegion();

		return $region ?? LANGUAGE_ID;
	}
}
