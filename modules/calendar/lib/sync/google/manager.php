<?php

namespace Bitrix\Calendar\Sync\Google;

use Bitrix\Calendar\Core;
use Bitrix\Calendar\Integration\Socialservices\Auth\GoogleAuthHelper;
use Bitrix\Calendar\Integration\Pull\PushCommand;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Sync\Managers\ServiceBase;
use Bitrix\Calendar\Util;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectPropertyException;
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
	 * @throws LoaderException
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
			$httpClient = new HttpClient();
			$oAuthEntity = GoogleAuthHelper::getUserAuthEntity($userId);

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

			Util::addPullEvent(
				PushCommand::RefreshSyncStatus,
				$this->connection->getOwner()->getId(),
				[
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
				]
			);
		}
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
	 *
	 * @throws LoaderException
	 * @throws SystemException
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 */
	protected function handleUnauthorize(Connection $connection)
	{
		$userId = $connection->getOwner()->getId();
		$oAuth = GoogleAuthHelper::getUserAuthEntity($userId);
		$userTokenInfo = GoogleAuthHelper::getStoredTokens($userId);
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
