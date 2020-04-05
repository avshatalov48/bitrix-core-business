<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage rest
 * @copyright 2001-2016 Bitrix
 */

namespace Bitrix\Rest\OAuth;


use Bitrix\Rest\Application;
use Bitrix\Rest\AppTable;
use Bitrix\Rest\AuthStorageInterface;
use Bitrix\Rest\Event\Session;
use Bitrix\Rest\OAuthService;

class Auth
{
	const AUTH_TYPE = 'oauth';

	const CACHE_TTL = 3600;
	const CACHE_PREFIX = "oauth_";

	const PARAM_LOCAL_USER = 'LOCAL_USER';
	const PARAM_TZ_OFFSET = 'TZ_OFFSET';

	/**
	 * @var AuthStorageInterface
	 */
	protected static $storage = null;

	protected static $authQueryParams = array(
		'auth', 'access_token'
	);

	protected static $authQueryAdditional = array(
		'auth_connector'
	);

	/**
	 * @deprecated Use \Bitrix\Rest\Application::getAuthProvider()->authorizeClient()
	 */
	public static function authorizeClient($clientId, $userId, $state = '')
	{
		return Application::getAuthProvider()->authorizeClient($clientId, $userId, $state);
	}

	/**
	 * @deprecated Use \Bitrix\Rest\Application::getAuthProvider()->get()
	 */
	public static function get($clientId, $scope, $additionalParams, $userId)
	{
		return Application::getAuthProvider()->get($clientId, $scope, $additionalParams, $userId);
	}

	public static function storeRegisteredAuth(array $tokenInfo)
	{
		static::getStorage()->store($tokenInfo);
	}

	public static function onRestCheckAuth(array $query, $scope, &$res)
	{
		$authKey = static::getAuthKey($query);

		if($authKey)
		{
			$tokenInfo = static::check($authKey);
			if(is_array($tokenInfo))
			{
				$error = array_key_exists('error', $tokenInfo);

				if(!$error && !array_key_exists('client_id', $tokenInfo))
				{
					$tokenInfo = array('error' => 'CONNECTION_ERROR', 'error_description' => 'Error connecting to authorization server');
					$error = true;
				}

				if(!$error)
				{
					$clientInfo = AppTable::getByClientId($tokenInfo['client_id']);
					if(is_array($clientInfo))
					{
						\CRestUtil::updateAppStatus($tokenInfo);
					}

					if(!is_array($clientInfo) || $clientInfo['ACTIVE'] !== 'Y')
					{
						$tokenInfo = array('error' => 'APPLICATION_NOT_FOUND', 'error_description' => 'Application not found');
						$error = true;
					}
				}

				if(!$error && $tokenInfo['expires'] <= time())
				{
					$tokenInfo = array('error' => 'expired_token', 'error_description' => 'The access token provided has expired');
					$error = true;
				}

				if(!$error && $scope !== \CRestUtil::GLOBAL_SCOPE && isset($tokenInfo['scope']))
				{
					$tokenScope = explode(',', $tokenInfo['scope']);
					if(!in_array($scope, $tokenScope))
					{
						$tokenInfo = array('error' => 'insufficient_scope', 'error_description' => 'The request requires higher privileges than provided by the access token');
						$error = true;
					}
				}

				if(!$error && $tokenInfo['user_id'] > 0)
				{
					if(!\CRestUtil::makeAuth($tokenInfo))
					{
						$tokenInfo = array('error' => 'authorization_error', 'error_description' => 'Unable to authorize user');
						$error = true;
					}
					elseif(!\CRestUtil::checkAppAccess($tokenInfo['client_id']))
					{
						$tokenInfo = array('error' => 'user_access_error', 'error_description' => 'The user does not have access to the application.');
						$error = true;
					}
				}

				$res = $tokenInfo;

				$res['parameters_clear'] = static::$authQueryParams;
				$res['auth_type'] = static::AUTH_TYPE;
				$res['parameters_callback'] = array(__CLASS__, 'updateTokenParameters');

				foreach(static::$authQueryAdditional as $key)
				{
					if(array_key_exists($key, $query))
					{
						$res[$key] = $query[$key];
						$res['parameters_clear'][] = $key;
					}
				}

				return !$error;
			}

			return false;
		}

		return null;
	}

	public static function getAuthKey(array $query)
	{
		$authKey = null;

		$authHeader = \Bitrix\Main\Application::getInstance()->getContext()->getRequest()->getHeader('Authorization');
		if($authHeader !== null)
		{
			if(preg_match('/^Bearer\s+/i', $authHeader))
			{
				$authKey = preg_replace('/^Bearer\s+/i', '', $authHeader);
			}
		}

		if($authKey === null)
		{
			foreach(static::$authQueryParams as $key)
			{
				if(array_key_exists($key, $query) && !is_array($query[$key]))
				{
					$authKey = $query[$key];
					break;
				}
			}
		}

		return $authKey;
	}

	public static function updateTokenParameters($tokenInfo)
	{
		$authResult = static::getStorage()->restore($tokenInfo['access_token']);

		if(is_array($authResult))
		{
			if(!is_array($authResult['parameters']))
			{
				$authResult['parameters'] = array();
			}

			$authResult['parameters'] = array_replace_recursive($authResult['parameters'], $tokenInfo['parameters']);

			static::getStorage()->rewrite($authResult);
		}
	}

	protected static function check($accessToken)
	{
		$authResult = static::getStorage()->restore($accessToken);
		if($authResult === false)
		{
			$client = OAuthService::getEngine()->getClient();
			$tokenInfo = $client->checkAuth($accessToken);

			if(is_array($tokenInfo))
			{
				if($tokenInfo['result'])
				{
					$authResult = $tokenInfo['result'];
					$authResult['user_id'] = $authResult['parameters'][static::PARAM_LOCAL_USER];
					unset($authResult['parameters'][static::PARAM_LOCAL_USER]);

					// compatibility with old oauth response
					if(!isset($authResult['expires']) && isset($authResult['expires_in']))
					{
						$authResult['expires'] = time() + $authResult['expires_in'];
					}
				}
				else
				{
					$authResult = $tokenInfo;
					$authResult['access_token'] = $accessToken;
				}

				static::getStorage()->store($authResult);
			}
			else
			{
				$authResult = ['access_token' => $accessToken];
			}
		}

		return $authResult;
	}

	protected static function getTokenParams($additionalParams, $userId)
	{
		if(!is_array($additionalParams))
		{
			$additionalParams = array();
		}

		$additionalParams[static::PARAM_LOCAL_USER] = $userId;
		$additionalParams[static::PARAM_TZ_OFFSET] = \CTimeZone::getOffset();
		$additionalParams[Session::PARAM_SESSION] = Session::get();

		return $additionalParams;
	}

	/**
	 * @return AuthStorageInterface
	 */
	public static function getStorage()
	{
		if(static::$storage === null)
		{
			static::setStorage(new StorageCache());
		}

		return static::$storage;
	}

	/**
	 * @param AuthStorageInterface $storage
	 */
	public static function setStorage(AuthStorageInterface $storage)
	{
		static::$storage = $storage;
	}
}