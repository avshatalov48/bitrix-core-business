<?php
namespace Bitrix\MessageService\Providers\Edna;

abstract class RegionHelper
{
	public const REGION_RU = ['ru'];
	public const REGION_OPTION_FORCE = 'force_region';
	public const REGION_PHRASE_POSTFIX = '_IO';

	public static function isInternational(): bool
	{
		$region = \Bitrix\Main\Config\Option::get('messageservice', self::REGION_OPTION_FORCE, false);
		if (!$region)
		{
			$region = \Bitrix\Main\Application::getInstance()->getLicense()->getRegion();
			\Bitrix\Main\Config\Option::set('messageservice', self::REGION_OPTION_FORCE, $region);
		}

		return !in_array($region, self::REGION_RU, true);
	}

	public static function getPhrase(string $phraseCode): string
	{
		return self::isInternational() ? $phraseCode . self::REGION_PHRASE_POSTFIX : $phraseCode;
	}

	abstract static function getApiEndPoint();
}
