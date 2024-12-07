<?php

namespace Bitrix\Socialnetwork\Space\List\RecentActivity\Event\Processor;

use Bitrix\Socialnetwork\Integration\Calendar\RecentActivity\CalendarCommentProcessor;
use Bitrix\Socialnetwork\Integration\Calendar\RecentActivity\CalendarProcessor;
use Bitrix\Socialnetwork\Integration\Tasks\RecentActivity\TaskCommentProcessor;
use Bitrix\Socialnetwork\Integration\Tasks\RecentActivity\TaskProcessor;
use Bitrix\Socialnetwork\Internals\EventService\Event;
use Bitrix\Socialnetwork\Internals\EventService\EventDictionary;
use Bitrix\Socialnetwork\Internals\EventService\Recepients\Recepient;

final class Factory
{
	private const CALENDAR_EVENTS = [
		EventDictionary::EVENT_SPACE_CALENDAR_INVITE,
		EventDictionary::EVENT_SPACE_CALENDAR_EVENT_DEL,
		EventDictionary::EVENT_SPACE_CALENDAR_EVENT_REMOVE_USERS,
	];

	private const TASK_EVENTS = [
		EventDictionary::EVENT_SPACE_TASK_ADD,
		EventDictionary::EVENT_SPACE_TASK_UPDATE,
		EventDictionary::EVENT_SPACE_TASK_DELETE,
		EventDictionary::EVENT_SPACE_TASK_REMOVE_USERS,
	];

	private const LIVEFEED_EVENTS = [
		EventDictionary::EVENT_SPACE_LIVEFEED_POST_ADD,
		EventDictionary::EVENT_SPACE_LIVEFEED_POST_UPD,
		EventDictionary::EVENT_SPACE_LIVEFEED_POST_DEL,
		EventDictionary::EVENT_SPACE_LIVEFEED_POST_REMOVE_USERS,
	];

	private const CALENDAR_COMMENT_EVENTS = [
		EventDictionary::EVENT_SPACE_CALENDAR_EVENT_COMMENT_ADD,
		EventDictionary::EVENT_SPACE_CALENDAR_EVENT_COMMENT_DEL,
	];

	private const TASK_COMMENT_EVENTS = [
		EventDictionary::EVENT_SPACE_TASK_COMMENT_ADD,
		EventDictionary::EVENT_SPACE_TASK_COMMENT_DELETE,
	];

	private const LIVEFEED_COMMENT_EVENTS = [
		EventDictionary::EVENT_SPACE_LIVEFEED_COMMENT_ADD,
		EventDictionary::EVENT_SPACE_LIVEFEED_COMMENT_DEL,
	];

	private const MEMBERSHIP_EVENTS = [
		EventDictionary::EVENT_WORKGROUP_USER_ADD,
		EventDictionary::EVENT_WORKGROUP_USER_UPDATE,
		EventDictionary::EVENT_WORKGROUP_USER_DELETE,
	];

	private static Factory|null $instance = null;

	public static function getInstance(): Factory
	{
		if (!self::$instance)
		{
			self::$instance = new Factory();
		}

		return self::$instance;
	}

	private function __construct()
	{}

	public function createProcessor(
		Event $event,
		Recepient $recipient,
	): ?ProcessorInterface
	{
		$eventType = $event->getType();

		return match (true) {
			in_array($eventType, self::CALENDAR_EVENTS) => new CalendarProcessor($event, $recipient),
			in_array($eventType, self::TASK_EVENTS) => new TaskProcessor($event, $recipient),
			in_array($eventType, self::LIVEFEED_EVENTS) => new LiveFeedProcessor($event, $recipient),
			in_array($eventType, self::MEMBERSHIP_EVENTS) => new MembershipProcessor($event, $recipient),
			in_array($eventType, self::CALENDAR_COMMENT_EVENTS) => new CalendarCommentProcessor($event, $recipient),
			in_array($eventType, self::TASK_COMMENT_EVENTS) => new TaskCommentProcessor($event, $recipient),
			in_array($eventType, self::LIVEFEED_COMMENT_EVENTS) => new LiveFeedCommentProcessor($event, $recipient),
			default => new NullProcessor($event, $recipient),
		};
	}
}