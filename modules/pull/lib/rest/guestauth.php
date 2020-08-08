<?php

namespace Bitrix\Pull\Rest;

class GuestAuth
{
	const AUTH_TYPE = 'pull_guest';

	const METHODS_WITHOUT_AUTH = [
		'server.time',
		'pull.config.get',

	];

	const PULL_UID_PARAM = 'pull_guest_id';

	protected static $authQueryParams = array(
		'pull_guest_id',
	);

	public static function onRestCheckAuth(array $query, $scope, &$res)
	{
		global $USER;
		if($USER->IsAuthorized() || !defined("PULL_USER_ID"))
		{
			return null;
		}

		$authCode = null;
		foreach(static::$authQueryParams as $key)
		{
			if(array_key_exists($key, $query))
			{
				$authCode = $query[$key];
				break;
			}
		}

		if($authCode === null)
		{
			return null;
		}

		if(static::checkQueryMethod(static::METHODS_WITHOUT_AUTH))
		{
			if((int)$authCode === (int)PULL_USER_ID)
			{
				$res = self::getSuccessfulResult();
				return true;
			}
		}

		return null;
	}

	protected static function checkQueryMethod($whiteListMethods)
	{
		if (\CRestServer::instance()->getMethod() == 'batch')
		{
			$result = false;
			foreach (\CRestServer::instance()->getQuery()['cmd'] as $key => $method)
			{
				$method = mb_substr($method, 0, mb_strrpos($method, '?'));
				$result = in_array(mb_strtolower($method), $whiteListMethods);
				if (!$result)
				{
					break;
				}
			}
		}
		else
		{
			$result = in_array(\CRestServer::instance()->getMethod(), $whiteListMethods);
		}

		return $result;
	}

	protected static function getSuccessfulResult()
	{
		return [
			'user_id' => defined("PULL_USER_ID") ? PULL_USER_ID : 0,
			'scope' => implode(',', \CRestUtil::getScopeList()),
			'parameters_clear' => static::$authQueryParams,
			'auth_type' => static::AUTH_TYPE,
		];
	}
}