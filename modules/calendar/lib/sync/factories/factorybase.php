<?php

namespace Bitrix\Calendar\Sync\Factories;

use Bitrix\Calendar\Core\Base\BaseException;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Sync\Managers\PushManagerInterface;
use Bitrix\Calendar\Sync\Managers\ServiceInterface;
use Bitrix\Calendar\Sync\Util\Context;

abstract class FactoryBase implements FactoryInterface, ServiceInterface, PushFactoryInterface
{
	/**
	 * you should set a constant for the following classes
	 *
	 * @var string
	 */
	public const SERVICE_NAME = '';
	/**
	 * @var Connection
	 */
	protected Connection $connection;
	/**
	 * @var Context|null
	 */
	protected ?Context $context;

	/**
	 * todo deal with naming
	 */
	// abstract public function getVendorSyncService(): VendorSyncService;

	/**
	 * todo You need to return the Context instance by getting it from the Context
	 * @return Context|null
	 */
	abstract public function getContext(): ?Context;

	/**
	 * @throws BaseException
	 */
	public function __construct(Connection $connection, Context $context = null)
	{
		if ($connection->getOwner() === null)
		{
			throw new BaseException('the connection must have owner');
		}

		$this->connection = $connection;
		$this->context = $context;
	}

	/**
	 * @return string
	 */
	public function getServiceName(): string
	{
		return static::SERVICE_NAME;
	}

	public function canSubscribeSection(): bool
	{
		return false;
	}

	public function canSubscribeConnection(): bool
	{
		return false;
	}

	public function getPushManager(): ?PushManagerInterface
	{
		return null;
	}
}
