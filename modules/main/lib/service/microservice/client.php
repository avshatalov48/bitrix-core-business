<?php

namespace Bitrix\Main\Service\MicroService;

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;

class Client
{
	const TYPE_BITRIX24 = "B24";
	const TYPE_BOX = "BOX";

	public static function getPortalType(): string
	{
		if(Loader::includeModule("bitrix24") && defined("BX24_HOST_NAME"))
		{
			return static::TYPE_BITRIX24;
		}
		else
		{
			return static::TYPE_BOX;
		}
	}

	/**
	 * Return license code of the portal (to be used as a part of request verification scheme).
	 *
	 * @return string
	 */
	public static function getLicenseCode(): string
	{
		if(defined('BX24_HOST_NAME'))
		{
			return BX24_HOST_NAME;
		}
		else
		{
			return Application::getInstance()->getLicense()->getPublicHashKey();
		}
	}

	public static function getServerName(): string
	{
		if(defined('BX24_HOST_NAME'))
		{
			return "https://" . BX24_HOST_NAME;
		}
		else
		{
			return (\CMain::isHTTPS() ? "https" : "http")."://".((defined("SITE_SERVER_NAME") && strlen(SITE_SERVER_NAME) > 0) ? SITE_SERVER_NAME : Option::get("main", "server_name"));
		}
	}

	/**
	 * Returns request authorization hash string.
	 *
	 * @param array $parameters Array or request parameters to be signed.
	 * @param string $suffix Suffix to append to signed string
	 *
	 * @return string
	 */
	public static function signRequest(array $parameters, string $suffix = ""): string
	{
		$paramStr = md5(implode("|", $parameters) . ($suffix ? "|" . $suffix : ""));

		$portalType = static::getPortalType();
		if ($portalType == self::TYPE_BITRIX24 && function_exists('bx_sign'))
		{
			return bx_sign($paramStr);
		}
		else
		{
			return md5($paramStr . Application::getInstance()->getLicense()->getHashLicenseKey());
		}
	}
}