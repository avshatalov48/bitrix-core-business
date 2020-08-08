<?php


namespace Bitrix\Sale\Exchange\Integration;


class Settings
{
	public static function getOAuthRestUrl()
	{
		return "https://oauth.bitrix.info/rest/";
	}

	public static function getOAuthAccessTokenUrl()
	{
		return "https://oauth.bitrix.info/oauth/token/";
	}
}