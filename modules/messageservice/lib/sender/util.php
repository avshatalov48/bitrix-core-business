<?php

namespace Bitrix\MessageService\Sender;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Web\HttpClient;

class Util
{
	public static function getHttpClientError(HttpClient $httpClient): ?Error
	{
		$err = $httpClient->getError();
		if (empty($err))
		{
			return null;
		}
		$code = array_key_first($err);

		return new Error($err[$code], $code);
	}

	public static function getHttpClientErrorString(HttpClient $httpClient): ?string
	{
		$err = $httpClient->getError();
		if (empty($err))
		{
			return null;
		}
		$code = array_key_first($err);

		return "[{$code}]: {$err[$code]}";
	}

	public static function getPortalZone(): string
	{
		if (Loader::includeModule('bitrix24'))
		{
			return \CBitrix24::getPortalZone();
        }
		$portalZone = Option::get("main", "vendor", "1c_bitrix_portal");

		switch ($portalZone)
		{
			case "ua_bitrix_portal":
				return "ua";
			case "bitrix_portal":
				return "en";
			case "1c_bitrix_portal":
			default:
				return "ru";
		}
	}
}