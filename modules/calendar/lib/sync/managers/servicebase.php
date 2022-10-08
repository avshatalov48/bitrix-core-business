<?php

namespace Bitrix\Calendar\Sync\Managers;

use Bitrix\Calendar\Core;
use Bitrix\Calendar\Sync\Connection\Connection;

abstract class ServiceBase implements ServiceInterface
{
	/**
	 * @var Connection
	 */
	protected Connection $connection;
	/**
	 * @var string
	 */
	protected string $serviceName;

	/**
	 * @param Connection $connection
	 */
	public function __construct(Connection $connection)
	{
		$this->connection = $connection;
		$this->serviceName = $this->connection->getVendor()->getCode();
	}

	/**
	 * @return string
	 */
	public function getServiceName(): string
	{
		return $this->serviceName;
	}

}
