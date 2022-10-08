<?php

namespace Bitrix\Calendar\Sync\Icloud;

use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Sync\Connection\Server;
use Bitrix\Calendar\Sync\Managers\ServiceBase;

abstract class AbstractManager extends ServiceBase
{
	protected int $userId;
	protected ?ApiService $apiService = null;

	/**
	 * @param Connection $connection
	 * @param int $userId
	 *
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	public function __construct(Connection $connection, int $userId)
	{
		parent::__construct($connection);
		$this->userId = $userId;
		$this->apiService = $this->prepareApiService($connection);
	}

	/**
	 * @param Connection $connection
	 *
	 * @return ApiService
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	protected function prepareApiService(Connection $connection): ApiService
	{
		$server = $this->prepareServerData($connection->getServer());

		return new ApiService($server, $this->userId);
	}

	/**
	 * @return string
	 */
	protected function initServiceName(): string
	{
		return Helper::ACCOUNT_TYPE;
	}

	/**
	 * @param Server $server
	 *
	 * @return array
	 */
	protected function prepareServerData(Server $server): array
	{
		return [
			'SERVER_SCHEME' => $server->getScheme(),
			'SERVER_HOST' => $server->getHost(),
			'SERVER_PORT' => $server->getPort(),
			'SERVER_USERNAME' => $server->getUserName(),
			'SERVER_PASSWORD' => $server->getPassword(),
		];
	}
}