<?php

namespace Bitrix\Rest\OAuth;

use Bitrix\Main\SystemException;
use Bitrix\Rest\AuthProviderInterface;
use Bitrix\Rest\Event\Session;
use Bitrix\Rest\OAuthService;
use Bitrix\Main\Type\DateTime;

class Provider implements AuthProviderInterface
{
	/**
	 * @var Provider
	 */
	protected static $instance = null;

	public static function instance()
	{
		if(static::$instance === null)
		{
			static::$instance = new static();
		}

		return static::$instance;
	}

	public function authorizeClient($clientId, $userId, $state = '')
	{
		if($userId > 0)
		{
			$additionalParams = $this->getTokenParams(array(), $userId);

			$client = $this->getClient();
			$codeInfo = $client->getCode($clientId, $state, $additionalParams);

			if($codeInfo['result'])
			{
				return $codeInfo['result'];
			}
			else
			{
				return $codeInfo;
			}
		}

		return false;
	}

	public function get($clientId, $scope, $additionalParams, $userId)
	{
		if($userId > 0)
		{
			$additionalParams = $this->getTokenParams($additionalParams, $userId);

			$client = $this->getClient();
			$authResult = $client->getAuth($clientId, $scope, $additionalParams);

			if($authResult['result'])
			{
				if($authResult['result']['access_token'])
				{
					$authResult['result']['user_id'] = $userId;
					$authResult['result']['client_id'] = $clientId;

					Auth::storeRegisteredAuth($authResult['result']);
				}

				if (!empty($authResult['result']['date_finish_format']))
				{
					$dateFinish = new DateTime($authResult['result']['date_finish_format'], DATE_ATOM);
					$authResult['result']['date_finish'] = $dateFinish->getTimestamp();
				}

				return $authResult['result'];
			}
			else
			{
				return $authResult;
			}
		}

		return false;
	}

	protected function getClient()
	{
		if (!OAuthService::getEngine()->isRegistered())
		{
			try
			{
				OAuthService::register();
			}
			catch (SystemException)
			{
			}
		}

		return OAuthService::getEngine()->getClient();
	}

	protected function getTokenParams($additionalParams, $userId)
	{
		if(!is_array($additionalParams))
		{
			$additionalParams = array();
		}

		$additionalParams[Auth::PARAM_LOCAL_USER] = $userId;
		$additionalParams[Auth::PARAM_TZ_OFFSET] = \CTimeZone::getOffset();
		$additionalParams[Session::PARAM_SESSION] = Session::get();

		return $additionalParams;
	}
}