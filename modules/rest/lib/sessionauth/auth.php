<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage rest
 * @copyright 2001-2016 Bitrix
 */

namespace Bitrix\Rest\SessionAuth;

use Bitrix\Main\Context;
use Bitrix\Main\UserTable;

class Auth
{
	const AUTH_TYPE = 'sessionauth';

	protected static $authQueryParams = array(
		'sessid',
	);

	public static function isAccessAllowed(): bool
	{
		global $USER;

		$externalAuthId = $USER->GetParam('EXTERNAL_AUTH_ID');

		if ($USER->IsAdmin() || $externalAuthId === "__controller")
		{
			return true;
		}

		// fake user like as BOT, IMCONNECTOR, SHOP
		$blackList = UserTable::getExternalUserTypes();
		if (in_array($externalAuthId, $blackList, true))
		{
			return false;
		}

		if (!\Bitrix\Main\Loader::includeModule('intranet'))
		{
			return true;
		}

		if (\Bitrix\Intranet\Util::isIntranetUser())
		{
			return true;
		}

		if (\Bitrix\Intranet\Util::isExtranetUser())
		{
			return true;
		}

		return false;
	}

	public static function onRestCheckAuth(array $query, $scope, &$res)
	{
		global $USER;

		$authKey = null;
		foreach(static::$authQueryParams as $key)
		{
			if(array_key_exists($key, $query))
			{
				$authKey = $query[$key];
				break;
			}
		}

		if($authKey !== null || Context::getCurrent()->getRequest()->getHeader('X-Bitrix-Csrf-Token') !== null)
		{
			static::checkHttpAuth();
			static::checkCookieAuth();

			if(!$USER->isAuthorized())
			{
				$error = true;
				$res = array('error' => 'access_denied', 'error_description' => 'User not authorized', 'additional' => array('sessid' => bitrix_sessid(), 'extended_error' => 'user_not_authorized'));
			}
			else if(check_bitrix_sessid() || $authKey === bitrix_sessid())
			{
				if (self::isAccessAllowed())
				{
					$error = false;
					$res = array(
						'user_id' => $USER->GetID(),
						'scope' => implode(',', \CRestUtil::getScopeList()),
						'parameters_clear' => static::$authQueryParams,
						'auth_type' => static::AUTH_TYPE,
					);

					self::setLastActivityDate($USER->GetID(), $query);

					if ($query['BX_SESSION_LOCK'] ?? null !== 'Y')
					{
						session_write_close();
					}
				}
				else
				{
					$error = true;
					$res = array('error' => 'access_denied', 'error_description' => 'Access denied for this type of user', 'additional' => array('type' => $USER->GetParam('EXTERNAL_AUTH_ID')));
				}
			}
			else
			{
				$error = true;
				$res = array('error' => 'session_failed', 'error_description' => 'Sessid check failed', 'additional' => array('sessid' => bitrix_sessid()));
			}

			return !$error;
		}

		return null;
	}

	private static function setLastActivityDate($userId, $query)
	{
		$query = array_change_key_case($query, CASE_UPPER);
		if (isset($query['BX_LAST_ACTIVITY']) && $query['BX_LAST_ACTIVITY'] == 'N')
		{
			return false;
		}

		$useCache = isset($query['BX_LAST_ACTIVITY_USE_CACHE']) && $query['BX_LAST_ACTIVITY_USE_CACHE'] == 'N'? false: true;

		if (isset($query['BX_MOBILE']) && $query['BX_MOBILE'] == 'Y')
		{
			if ($query['BX_MOBILE_BACKGROUND'] != 'Y' && \Bitrix\Main\Loader::includeModule('mobile'))
			{
				\Bitrix\Mobile\User::setOnline($userId, $useCache);
				\CUser::SetLastActivityDate($userId, $useCache);
			}
		}
		else
		{
			\CUser::SetLastActivityDate($userId, $useCache);
		}

		return true;
	}

	protected static function requireHttpAuth()
	{
		global $USER;
		$USER->RequiredHTTPAuthBasic('Bitrix REST');
	}

	protected static function checkHttpAuth()
	{
		global $USER, $APPLICATION;

		if(!$USER->IsAuthorized())
		{
			$httpAuth = $USER->LoginByHttpAuth();
			if($httpAuth !== null)
			{
				$APPLICATION->SetAuthResult($httpAuth);
			}
		}
	}

	protected static function checkCookieAuth()
	{
		global $USER;

		if(!$USER->IsAuthorized())
		{
			$USER->LoginByCookies();
		}
	}
}