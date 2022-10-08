<?php

namespace Bitrix\Calendar\Sync\Google;

use Bitrix\Calendar\Sync;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Sync\Managers\IncomingEventManagerInterface;
use Bitrix\Calendar\Sync\Managers\IncomingSectionManagerInterface;
use Bitrix\Calendar\Sync\Managers\OutgoingEventManagerInterface;
use Bitrix\Calendar\Sync\Managers\OutgoingSectionManagerInterface;
use Bitrix\Calendar\Sync\Managers\PushManagerInterface;
use Bitrix\Calendar\Sync\Util\Context;

class Factory extends Sync\Factories\FactoryBase
{
	public const SERVICE_NAME = 'google_api_oauth';

	public function getEventManager(): Sync\Managers\EventManagerInterface
	{
		return new EventManager($this->getConnection(), $this->getConnection()->getOwner()->getId());
	}

	public function getSectionManager(): Sync\Managers\SectionManagerInterface
	{
		return new SectionManager($this->getConnection(), $this->getConnection()->getOwner()->getId());
	}

	public function getConnection(): Connection
	{
		return $this->connection;
	}

	public function getCode(): string
	{
		return self::SERVICE_NAME;
	}

	public function getContext(): Context
	{
		return $this->context;
	}

	public function getImportManager(): Sync\Managers\IncomingSectionManagerInterface
	{
		return new ImportManager($this->getConnection(), $this->getConnection()->getOwner()->getId());
	}

	public function canSubscribeSection(): bool
	{
		return true;
	}

	public function canSubscribeConnection(): bool
	{
		return true;
	}

	public function getPushManager(): ?PushManagerInterface
	{
		// TODO: check, that owner is user
		return new PushManager($this->connection, $this->connection->getOwner()->getId());
	}

	public function getIncomingSectionManager(): IncomingSectionManagerInterface
	{
		return new ImportManager($this->getConnection(), $this->getConnection()->getOwner()->getId());
	}

	public function getIncomingEventManager(): IncomingEventManagerInterface
	{
		return new ImportManager($this->getConnection(), $this->getConnection()->getOwner()->getId());
	}

	public function getOutgoingEventManager(): OutgoingEventManagerInterface
	{
		return new OutgoingEventManager($this->getConnection(), $this->getConnection()->getOwner()->getId());
	}

	public function getOutgoingSectionManager(): OutgoingSectionManagerInterface
	{
		return new OutgoingSectionManager($this->getConnection(), $this->getConnection()->getOwner()->getId());
	}
}
