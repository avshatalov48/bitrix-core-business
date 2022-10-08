<?php

namespace Bitrix\Calendar\Sync\Managers;

use Bitrix\Calendar\Core;
use Bitrix\Calendar\Sync\Connection\Connection;

/**
 *
 */
abstract class SyncManagerBase implements SyncManagerInterface
{
	/**
	 * @var string
	 */
	protected string $serviceName = '';
	/**
	 * @var Connection
	 */
	protected Connection $connection;

	/**
	 * @param Connection $connection
	 */
	public function __construct(Connection $connection)
	{
		$this->connection = $connection;
		$this->serviceName = $this->initServiceName();
	}

	/**
	 * @return string
	 */
	public function getServiceName(): string
	{
		return $this->serviceName;
	}

	abstract protected function initServiceName(): string;
}
