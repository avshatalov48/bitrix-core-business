<?php

namespace Bitrix\Calendar\Event\Service;

use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Event\Enum\PushCommandEnum;
use Bitrix\Calendar\Event\Helper\EventWithAttendeesCountForUserBuilder;
use Bitrix\Calendar\EventCategory\Enum\PushTagEnum;
use Bitrix\Calendar\Integration\Pull\PushService;
use Bitrix\Main\Loader;

final class OpenEventPullService
{
	public const EVENT_USER_FIELDS_KEY = 'userFields';

	private static ?self $instance;

	public static function getInstance(): self
	{
		self::$instance ??= new self();

		return self::$instance;
	}

	public function createCalendarEvent(Event $event): void
	{
		$eventCommonParams = [
			'module_id' => PushService::MODULE_ID,
			'command' => PushCommandEnum::OPEN_EVENT_CREATED->name,
			'params' => [
				'fields' => (EventWithAttendeesCountForUserBuilder::buildFromEvent(
					event: $event,
					userId: 0,
					isAttendee: false,
					commentsCount: 0,
				))->toArray(),
			],
		];

		$tag = $this->generateTagByEvent($event);
		$this->sendEvent($eventCommonParams, tag: $tag);
	}

	public function updateCalendarEvent(Event $event, array $userParams = []): void
	{
		$eventCommonParams = [
			'module_id' => PushService::MODULE_ID,
			'command' => PushCommandEnum::OPEN_EVENT_UPDATED->name,
			'params' => [
				'fields' => (EventWithAttendeesCountForUserBuilder::buildFromEvent(
					event: $event,
					userId: 0,
					isAttendee: null,
					commentsCount: null,
				))->toArray(),
			],
		];

		if ($userParams)
		{
			$eventCommonParams['user_params'] = $userParams;
		}

		$tag = $this->generateTagByEvent($event);
		$this->sendEvent($eventCommonParams, tag: $tag);
	}

	public function deleteCalendarEvent(Event $event): void
	{
		$eventCommonParams = [
			'module_id' => PushService::MODULE_ID,
			'command' => PushCommandEnum::OPEN_EVENT_DELETED->name,
			'params' => [
				'fields' => [
					'eventId' => $event->getId(),
					'categoryId' => $event->getEventOption()->getCategoryId()
				],
			],
		];

		$tag = $this->generateTagByEvent($event);
		$this->sendEvent($eventCommonParams, tag: $tag);
	}

	public function addToWatch(int $userId): void
	{
		if (!Loader::includeModule('pull'))
		{
			return;
		}

		\CPullWatch::Add($userId, \Bitrix\Calendar\Event\Enum\PushTagEnum::OPEN_EVENT->name);
	}

	private function sendEvent(array $params, int $userId = null, string $tag = null): void
	{
		if (!Loader::includeModule('pull'))
		{
			return;
		}

		if ($userId)
		{
			PushService::addEvent([$userId], $params);
		}
		else
		{
			PushService::addEventByTag($tag, $params);
		}
	}

	private function generateTagByEvent(Event $event): string
	{
		$isCategoryClosed = $event->getEventOption()->getCategory()->getClosed();
		$categoryId = $event->getEventOption()->getCategory()->getId();

		return $isCategoryClosed ? $this->generateTagByCategoryId($categoryId) : PushTagEnum::EVENT_CATEGORY->name;
	}

	private function generateTagByCategoryId(int $eventCategoryId): string
	{
		return sprintf('%s_%d', PushTagEnum::EVENT_CATEGORY->name, $eventCategoryId);
	}

	private function __construct()
	{
	}
}
