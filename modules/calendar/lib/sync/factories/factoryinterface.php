<?php

namespace Bitrix\Calendar\Sync\Factories;

use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Sync\Managers\EventManagerInterface;
use Bitrix\Calendar\Sync\Managers\IncomingEventManagerInterface;
use Bitrix\Calendar\Sync\Managers\IncomingSectionManagerInterface;
use Bitrix\Calendar\Sync\Managers\OutgoingEventManagerInterface;
use Bitrix\Calendar\Sync\Managers\OutgoingSectionManagerInterface;
use Bitrix\Calendar\Sync\Managers\SectionManagerInterface;

/**
 * @extends PushFactoryInterface
 */
interface FactoryInterface
{
	/**
	 * @return EventManagerInterface
	 */
	public function getEventManager(): EventManagerInterface;

	/**
	 * @return SectionManagerInterface
	 */
	public function getSectionManager(): SectionManagerInterface;

	/**
	 * @return Connection
	 */
	public function getConnection(): Connection;

	/**
	 * @return string
	 */
	public function getServiceName(): string;

	/**
	 * @return IncomingSectionManagerInterface
	 */
	public function getIncomingSectionManager(): IncomingSectionManagerInterface;

	/**
	 * @return IncomingEventManagerInterface
	 */
	public function getIncomingEventManager(): IncomingEventManagerInterface;

	/**
	 * @return OutgoingEventManagerInterface
	 */
	public function getOutgoingEventManager(): OutgoingEventManagerInterface;

	/**
	 * @return OutgoingSectionManagerInterface
	 */
	public function getOutgoingSectionManager(): OutgoingSectionManagerInterface;
}
