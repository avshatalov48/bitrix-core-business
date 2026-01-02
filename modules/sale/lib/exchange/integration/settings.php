<?php

namespace Bitrix\Sale\Exchange\Integration;

use \Bitrix\Main\License\UrlProvider;

class Settings
{
	public static function getOAuthRestUrl()
	{
		$urlProvider = new UrlProvider();

		return "https://oauth.{$urlProvider->getTechDomain()}/rest/";
	}

	public static function getOAuthAccessTokenUrl()
	{
		$urlProvider = new UrlProvider();

		return "https://oauth.{$urlProvider->getTechDomain()}/oauth/token/";
	}
}
