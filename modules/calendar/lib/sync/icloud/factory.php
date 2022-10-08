<?php

namespace Bitrix\Calendar\Sync\Icloud;

use Bitrix\Calendar\Sync;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Sync\Managers\IncomingEventManagerInterface;
use Bitrix\Calendar\Sync\Managers\IncomingSectionManagerInterface;
use Bitrix\Calendar\Sync\Managers\OutgoingEventManagerInterface;
use Bitrix\Calendar\Sync\Managers\OutgoingSectionManagerInterface;
use Bitrix\Calendar\Sync\Util\Context;

class Factory extends Sync\Factories\FactoryBase
{
	public const SERVICE_NAME = 'icloud';

	/**
	 * @return Sync\Managers\EventManagerInterface
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	public function getEventManager(): Sync\Managers\EventManagerInterface
	{
		return new EventManager($this->getConnection(), $this->getConnection()->getOwner()->getId());
	}

	/**
	 * @return Sync\Managers\SectionManagerInterface
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	public function getSectionManager(): Sync\Managers\SectionManagerInterface
	{
		return new SectionManager($this->getConnection(),  $this->getConnection()->getOwner()->getId());
	}

	/**
	 * @return Connection
	 */
	public function getConnection(): Connection
	{
		return $this->connection;
	}

	/**
	 * @return string
	 */
	public function getCode(): string
	{
		return self::SERVICE_NAME;
	}

	/**
	 * @return bool
	 */
	public function canSubscribeSection(): bool
	{
		return false;
	}

	/**
	 * @return Context
	 */
	public function getContext(): Context
	{
		return $this->context;
	}

	/**
	 * @return IncomingSectionManagerInterface
	 */
	public function getIncomingSectionManager(): IncomingSectionManagerInterface
	{
		return new IncomingManager();
	}

	/**
	 * @return IncomingEventManagerInterface
	 */
	public function getIncomingEventManager(): IncomingEventManagerInterface
	{
		return new IncomingManager();
	}

	/**
	 * @return OutgoingEventManagerInterface
	 */
	public function getOutgoingEventManager(): OutgoingEventManagerInterface
	{
		return new OutgoingEventManager();
	}

	/**
	 * @return OutgoingSectionManagerInterface
	 */
	public function getOutgoingSectionManager(): OutgoingSectionManagerInterface
	{
		return new OutgoingSectionManager();
	}
}
