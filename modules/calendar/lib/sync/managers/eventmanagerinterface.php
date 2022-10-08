<?php

namespace Bitrix\Calendar\Sync\Managers;

use Bitrix\Calendar;
use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Sync\Connection\SectionConnection;
use Bitrix\Calendar\Sync\Entities\SyncEvent;
use Bitrix\Calendar\Sync\Util\Context;
use Bitrix\Calendar\Sync\Util\EventContext;
use Bitrix\Calendar\Sync\Util\Result;
use Generator;

interface EventManagerInterface
{
	/**
	 * @param Event $event
	 * @param EventContext $context
	 *
	 * @return Result
	 * 'event' => [
	 * 		'id' => vendorEventId,
	 * 		'version' => etag,
	 * 	],
	 * 	'data' => [
	 * 		'location' => [free structure],
	 * 		'attendees' => [free structure],
	 * 	],
	 *
	 */
	public function create(Event $event, EventContext $context): Result;

	/**
	 * @param Event $event
	 * @param EventContext $context
	 *
	 * @return Result
	 */
	public function update(Event $event, EventContext $context): Result;

	/**
	 * @param Event $event
	 * @param EventContext $context
	 *
	 * @return Result
	 */
	public function delete(Event $event, EventContext $context): Result;

	/**
	 * @param Event $event
	 * @param EventContext $context
	 *
	 * @return Result
	 */
	public function createInstance(Event $event, EventContext $context): Result;

	/**
	 * @param Event $event
	 * @param EventContext $context
	 *
	 * @return Result
	 */
	public function updateInstance(Event $event, EventContext $context): Result;

	/**
	 * @param Event $event
	 * @param EventContext $context
	 *
	 * @return Result
	 */
	public function deleteInstance(Event $event, EventContext $context): Result;

	/**
	 * @param SyncEvent $recurrenceEvent
	 * @param SectionConnection $sectionConnection
	 * @param Context $context
	 *
	 * @return Result
	 */
	public function createRecurrence(
		SyncEvent $recurrenceEvent,
		SectionConnection $sectionConnection,
		Context $context
	): Result;

	/**
	 * @param SyncEvent $recurrenceEvent
	 * @param SectionConnection $sectionConnection
	 * @param Context $context
	 *
	 * @return Result
	 */
	public function updateRecurrence(
		SyncEvent $recurrenceEvent,
		SectionConnection $sectionConnection,
		Context $context
	): Result;
}
