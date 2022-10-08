<?php

namespace Bitrix\Calendar\Sync\Office365;

use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Sync\Factories\VendorFactoryBase;
use Bitrix\Calendar\Sync\Managers\PushManagerInterface;
use Bitrix\Calendar\Sync\Managers\SyncManagerInterface;

/**
 * @deprecated
 */
class VendorFactory extends VendorFactoryBase
{
	private Office365Context $context;
	/** @var SyncManagerInterface */
	private SyncManagerInterface $eventManager;
	/** @var SyncManagerInterface */
	private SyncManagerInterface $sectionManager;
	/** @var PushManagerInterface */
	private $pushManager;

	public function __construct(Connection $connection)
	{
		parent::__construct($connection);
		$this->context = Office365Context::getConnectionContext($connection);
	}

	public function getEventManager(): SyncManagerInterface
	{
		if (empty($this->eventManager))
		{
			$this->eventManager = new EventManager($this->context);
		}

		return $this->eventManager;
	}

	public function getSectionManager(): SyncManagerInterface
	{
		if (empty($this->sectionManager))
		{
			$this->sectionManager = new SectionManager($this->context);
		}

		return $this->sectionManager;
	}

	public function getConnection(): Connection
	{
		return $this->connection;
	}

	public function getCode(): string
	{
		return $this->getConnection()->getVendor()->getCode();
	}

	public function canSubscribeSection()
	{
		return true;
	}

	public function getPushManager(): ?PushManagerInterface
	{
		if (empty($this->pushManager))
		{
			$this->pushManager = new PushManager($this->context);
		}
		return $this->pushManager;
	}
}
