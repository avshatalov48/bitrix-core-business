<?php

namespace Bitrix\Calendar\Sync\Google;

use Bitrix\Calendar\Core;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Sync\Managers\ServiceBase;
use Bitrix\Calendar\Util;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Web\HttpClient;

abstract class Manager extends ServiceBase
{
	/**
	 * @var HttpQuery
	 */
	protected HttpQuery $httpClient;
	protected int $userId;

	private static array $httpClients = [];

	/**
	 * @param Connection $connection
	 * @param int $userId
	 *
	 * @throws SystemException
	 */
	public function __construct(Connection $connection, int $userId)
	{
		parent::__construct($connection);
		$this->userId = $userId;
		
		if (!$this->initHttpClient())
		{
			$this->deactivateConnection();
		}
	}

	/**
	 *
	 * @param bool $force
	 *
	 * @return bool is success
	 *
	 * @throws SystemException
	 */
	private function initHttpClient(bool $force = false): bool
	{
		$success = true;
		$userId = $this->userId;
		if (!isset(self::$httpClients[$userId]) || $force)
		{
			if (!Loader::includeModule('socialservices'))
			{
				throw new SystemException('Module Socialservices not found');
			}

			$httpClient = new HttpClient();
			$oAuthEntity = $this->prepareAuthEntity($userId);

			if ($oAuthEntity->getToken())
			{
				$httpClient->setHeader('Authorization', 'Bearer ' . $oAuthEntity->getToken());
				$httpClient->setHeader('Content-Type', 'application/json');
				$httpClient->setHeader('Referer', \Bitrix\Calendar\Sync\Util\Helper::getDomain());
				unset($oAuthEntity);
			}
			else
			{
				$success = false;
			}

			self::$httpClients[$userId] = new HttpQuery($httpClient, $userId);
		}

		$this->httpClient = self::$httpClients[$userId];

		return $success;
	}
	
	/**
	 * @param int $userId
	 * @return \CGoogleOAuthInterface|\CGoogleProxyOAuthInterface
	 * @throws \Bitrix\Main\LoaderException
	 */
	private function prepareAuthEntity(int $userId): \CGoogleOAuthInterface|\CGoogleProxyOAuthInterface
	{
		if (\CSocServGoogleProxyOAuth::isProxyAuth())
		{
			$oAuth = new \CSocServGoogleProxyOAuth($userId);
		}
		else
		{
			$oAuth = new \CSocServGoogleOAuth($userId);
		}
		
		$oAuthEntity = $oAuth->getEntityOAuth();
		$oAuthEntity->addScope([
			'https://www.googleapis.com/auth/calendar',
			'https://www.googleapis.com/auth/calendar.readonly'
		]);
		$oAuthEntity->removeScope('https://www.googleapis.com/auth/drive');

		$oAuthEntity->setUser($userId);
		
		$tokens = $this->getStorageToken($userId);
		if ($tokens)
		{
			$oAuthEntity->setToken($tokens['OATOKEN']);
			$oAuthEntity->setAccessTokenExpires($tokens['OATOKEN_EXPIRES']);
			$oAuthEntity->setRefreshToken($tokens['REFRESH_TOKEN']);
		}
		
		if (!$oAuthEntity->checkAccessToken())
		{
			$oAuthEntity->getNewAccessToken(
				$oAuthEntity->getRefreshToken(),
				$userId,
				true,
			);
		}
		
		return $oAuthEntity;
	}

	private function deactivateConnection()
	{
		if ($this->connection->getId())
		{
			$this->connection
				->setStatus('[401] Unauthorized')
				->setLastSyncTime(new Core\Base\Date())
			;
			
			/** @var Core\Mappers\Factory $mapperFactory */
			$mapperFactory = ServiceLocator::getInstance()->get('calendar.service.mappers.factory');
			$mapperFactory->getConnection()->update($this->connection);
			
			Util::addPullEvent('refresh_sync_status', $this->connection->getOwner()->getId(), [
				'syncInfo' => [
					'google' => [
						'status' => false,
						'type' => $this->connection->getAccountType(),
						'connected' => true,
						'id' => $this->connection->getId(),
						'syncOffset' => 0
					],
				],
				'requestUid' => Util::getRequestUid(),
			]);
		}
	}
	
	protected function getStorageToken($userId)
	{
		return \Bitrix\Socialservices\UserTable::query()
			->setSelect(['USER_ID', 'EXTERNAL_AUTH_ID', 'OATOKEN', 'OATOKEN_EXPIRES', 'REFRESH_TOKEN'])
			->where('USER_ID', $userId)
			->where('EXTERNAL_AUTH_ID', 'GoogleOAuth')
			->exec()->fetch()
		;
	}

	/**
	 * @return bool
	 */
	protected function isRequestSuccess(): bool
	{
		return $this->httpClient->getStatus() === 200;
	}

	protected function isRequestDeleteSuccess(): bool
	{
		$acceptedCodes = [200, 201, 204, 404];

		return in_array($this->httpClient->getStatus(), $acceptedCodes);
	}
	
	/**
	 * @param Connection $connection
	 * @return void
	 * @throws LoaderException
	 */
	protected function handleUnauthorize(Connection $connection)
	{
		$userId = $connection->getOwner()->getId();
		$oAuth = $this->prepareAuthEntity($userId);
		$userTokenInfo = $this->getStorageToken($userId);
		$refreshResult = false;
		
		if ($userTokenInfo['REFRESH_TOKEN'])
		{
			$refreshResult = $oAuth->getNewAccessToken($userTokenInfo['REFRESH_TOKEN'], $userId, true);
		}
		
		if (!$refreshResult)
		{
			$this->deactivateConnection();
		}
	}

	/**
	 * Request to Google API and handle errors
	 * @param $params
	 *
	 * @return void
	 *
	 */
	protected function request($params)
	{
		// TODO: implement it
	}
}
