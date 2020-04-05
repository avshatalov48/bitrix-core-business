<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage rest
 * @copyright 2001-2016 Bitrix
 */

namespace Bitrix\Rest;


use Bitrix\Main\ArgumentException;
use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;
use Bitrix\Rest\OAuth\Engine;

Loc::loadMessages(__FILE__);

if(!defined("BITRIX_OAUTH_URL"))
{
	$defaultValue = \Bitrix\Main\Config\Option::get('rest', 'oauth_server', 'https://oauth.bitrix.info');
	define("BITRIX_OAUTH_URL", $defaultValue);
}

if(!defined('BITRIXREST_URL'))
{
	define('BITRIXREST_URL', BITRIX_OAUTH_URL);
}


class OAuthService
{
	const SERVICE_URL = BITRIXREST_URL;
	const CLIENT_TYPE = 'B';

	const REGISTER = "/oauth/register/";

	protected static $engine = null;

	/**
	 * @return \Bitrix\Rest\OAuth\Engine
	 */
	public static function getEngine()
	{
		if(!static::$engine)
		{
			static::$engine = new Engine();
		}

		return static::$engine;
	}

	public static function register()
	{
		$httpClient = new HttpClient();

		$queryParams = array(
			"redirect_uri" => static::getRedirectUri(),
			"type" => static::CLIENT_TYPE,
		);

		$memberId = \CRestUtil::getMemberId();
		if($memberId !== null)
		{
			$queryParams["member_id"] = $memberId;
		}

		$queryParams = \CRestUtil::signLicenseRequest($queryParams, static::getEngine()->getLicense());

		$httpResult = $httpClient->post(static::SERVICE_URL.static::REGISTER, $queryParams);

		try
		{
			$result = Json::decode($httpResult);
		}
		catch(ArgumentException $e)
		{
			$result = array(
				"error" => "Wrong answer from service: ".$httpResult,
			);
		}

		if($result["error"])
		{
			throw new SystemException($result["error"]);
		}
		else
		{
			static::getEngine()->setAccess($result);
		}
	}

	public static function unregister()
	{
		if(static::getEngine()->isRegistered())
		{
			static::getEngine()->clearAccess();
		}
	}

	public static function getMemberId()
	{
		if(static::getEngine()->isRegistered())
		{
			return md5(static::getEngine()->getClientId());
		}
		else
		{
			return null;
		}
	}

	public static function getRedirectUri()
	{
		$request = Context::getCurrent()->getRequest();
		$server = Context::getCurrent()->getServer();

		$host = defined('BX24_HOST_NAME') ? BX24_HOST_NAME : $server->getHttpHost();

		return ($request->isHttps() ? 'https' : 'http').'://'.preg_replace("/:(443|80)$/", "", $host);
	}
}