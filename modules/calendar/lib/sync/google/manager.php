<?php

namespace Bitrix\Calendar\Sync\Google;

use Bitrix\Calendar\Core;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Sync\Exceptions\AuthException;
use Bitrix\Calendar\Sync\Managers\ServiceBase;
use Bitrix\Calendar\Sync\Util\Helper;
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
			if (\CSocServGoogleProxyOAuth::isProxyAuth())
			{
				$oAuth = new \CSocServGoogleProxyOAuth($userId);
			}
			else
			{
				$oAuth = new \CSocServGoogleOAuth($userId);
			}

			$oAuth->getEntityOAuth()->addScope(
				[
					'https://www.googleapis.com/auth/calendar',
					'https://www.googleapis.com/auth/calendar.readonly',
				]
			);

			$oAuth->getEntityOAuth()->setUser($userId);

			if ($oAuth->getEntityOAuth()->GetAccessToken())
			{
				$httpClient->setHeader('Authorization', 'Bearer ' . $oAuth->getEntityOAuth()->getToken());
				$httpClient->setHeader('Content-Type', 'application/json');
				$httpClient->setHeader('Referer', Helper::getDomain());
				unset($oAuth);
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

	private function deactivateConnection()
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
	 *
	 * @return void
	 *
	 * @throws SystemException
	 */
	protected function handleUnauthorize(Connection $connection)
	{
		$this->deactivateConnection();
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
