<?php

namespace Bitrix\Calendar\Sync\Office365;

use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Sync\Factories\FactoryBase;
use Bitrix\Calendar\Sync\Managers\EventManagerInterface;
use Bitrix\Calendar\Sync\Managers\IncomingEventManagerInterface;
use Bitrix\Calendar\Sync\Managers\IncomingSectionManagerInterface;
use Bitrix\Calendar\Sync\Managers\OutgoingEventManagerInterface;
use Bitrix\Calendar\Sync\Managers\OutgoingSectionManagerInterface;
use Bitrix\Calendar\Sync\Managers\PushManagerInterface;
use Bitrix\Calendar\Sync\Managers\SectionManagerInterface;
use Bitrix\Calendar\Sync;
use Bitrix\Main\ObjectNotFoundException;

class Factory extends FactoryBase
{
	protected ?Sync\Util\Context $context;

	/** @var Office365Context */
	private Office365Context $officeContext;

	public const SERVICE_NAME = 'office365';

	/** @var EventManagerInterface */
	private EventManagerInterface $eventManager;
	/** @var SectionManagerInterface */
	private SectionManagerInterface $sectionManager;

	/**
	 * @param Connection $connection
	 * @param Sync\Util\Context|null $context
	 *
	 * @throws ObjectNotFoundException
	 */
	public function __construct(Connection $connection, Sync\Util\Context $context = null)
	{
		parent::__construct($connection, $context);
		$this->officeContext = Office365Context::getConnectionContext($connection);
	}

	public function getEventManager(): EventManagerInterface
	{
		if (empty($this->eventManager))
		{
			$this->eventManager = new EventManager($this->officeContext);
		}

		return $this->eventManager;
	}

	public function getSectionManager(): SectionManagerInterface
	{
		if (empty($this->sectionManager))
		{
			$this->sectionManager = new SectionManager($this->officeContext);
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

	public function canSubscribeSection(): bool
	{
		return true;
	}

	// public function getVendorSyncService(): VendorSyncService
	// {
	// 	// TODO: Implement getVendorSyncService() method.
	// }

	public function getContext(): Sync\Util\Context
	{
		return $this->context;
	}

	public function getImportManager(): IncomingSectionManagerInterface
	{
		return $this->officeContext->getIncomingManager();
	}

	public function getPushManager(): ?PushManagerInterface
	{
		return $this->officeContext->getPushManager();
	}

	/**
	 * @return IncomingSectionManagerInterface
	 */
	public function getIncomingSectionManager(): IncomingSectionManagerInterface
	{
		return $this->officeContext->getIncomingManager();
	}

	public function getIncomingEventManager(): IncomingEventManagerInterface
	{
		return $this->officeContext->getIncomingManager();
	}

	public function getOutgoingEventManager(): OutgoingEventManagerInterface
	{
		return $this->officeContext->getOutgoingEventManager();
	}

	public function getOutgoingSectionManager(): OutgoingSectionManagerInterface
	{
		// TODO: Implement getOutgoingSectionManager() method.
	}
}
