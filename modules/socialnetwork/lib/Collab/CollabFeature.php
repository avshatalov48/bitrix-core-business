<?php

declare(strict_types=1);


namespace Bitrix\Socialnetwork\Collab;

use Bitrix\Bitrix24;
use Bitrix\Main\Config\Configuration;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;

class CollabFeature
{
	public const FEATURE_ID = 'socialnetwork_collab';
	public const OPTION_NAME = 'feature_socialnetwork_collab';

	public static function isFeatureEnabled(): bool
	{
		if (!Loader::includeModule('bitrix24'))
		{
			return true;
		}

		return Bitrix24\Feature::isFeatureEnabled(self::FEATURE_ID);
	}

	public static function isOn(): bool
	{
		if (static::isDevMode())
		{
			return true;
		}

		return (bool)Option::get('socialnetwork', self::OPTION_NAME, true);
	}

	public static function turnOn(): void
	{
		Option::set('socialnetwork', self::OPTION_NAME, true);
	}

	public static function turnOff(): void
	{
		Option::delete('socialnetwork', ['name' => self::OPTION_NAME]);
	}

	public static function isDevMode(): bool
	{
		$exceptionHandling = Configuration::getValue('exception_handling');

		return !empty($exceptionHandling['debug']);
	}
}