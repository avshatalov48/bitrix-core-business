<?php

namespace Bitrix\Calendar\EventCategory\Service;

use Bitrix\Calendar\Core\EventCategory\EventCategory;
use Bitrix\Calendar\EventCategory\Enum\PushCommandEnum;
use Bitrix\Calendar\EventCategory\Enum\PushTagEnum;
use Bitrix\Calendar\EventCategory\Helper\EventCategoryResponseHelper;
use Bitrix\Calendar\Integration\Pull\PushService;
use Bitrix\Main\Loader;

final class EventCategoryPullService
{
	private static ?self $instance;

	public static function getInstance(): self
	{
		self::$instance ??= new self();

		return self::$instance;
	}

	public function createEvent(EventCategory $eventCategory): void
	{
		$eventCommonParams = [
			'module_id' => PushService::MODULE_ID,
			'command' => PushCommandEnum::EVENT_CATEGORY_CREATED->name,
			'params' => [
				'id' => $eventCategory->getId(),
				'fields' =>  EventCategoryResponseHelper::prepareEventCategoryForUserResponse(
					eventCategory: $eventCategory,
					isMuted: !$eventCategory->getClosed(),
				)->toArray(),
			],
		];

		$this->sendEvent($eventCategory, $eventCommonParams);
	}

	public function updateEvent(EventCategory $eventCategory, array $fields = [], ?int $userId = null): void
	{
		$eventCommonParams = [
			'module_id' => PushService::MODULE_ID,
			'command' => PushCommandEnum::EVENT_CATEGORY_UPDATED->name,
			'params' => [
				'id' => $eventCategory->getId(),
				'fields' => EventCategoryResponseHelper::prepareEventCategoryForUserResponse(
					eventCategory: $eventCategory,
				)->toArray(),
			],
		];

		$this->sendEvent($eventCategory, $eventCommonParams, $userId);
	}

	public function deleteEvent(EventCategory $eventCategory): void
	{
		$eventCommonParams = [
			'module_id' => PushService::MODULE_ID,
			'command' => PushCommandEnum::EVENT_CATEGORY_DELETED->name,
			'params' => [
				'fields' => [
					'id' => $eventCategory->getId(),
				],
			],
		];

		$this->sendEvent($eventCategory, $eventCommonParams);
	}

	private function generateTagByCategoryId(int $eventCategoryId): string
	{
		return sprintf('%s_%d', PushTagEnum::EVENT_CATEGORY->name, $eventCategoryId);
	}

	public function addToWatch(int $userId, ?int $eventCategoryId = null): void
	{
		if (!Loader::includeModule('pull'))
		{
			return;
		}

		$tag = $eventCategoryId
			? $this->generateTagByCategoryId($eventCategoryId)
			: PushTagEnum::EVENT_CATEGORY->name;

		\CPullWatch::Add($userId, $tag);
	}

	private function sendEvent(EventCategory $eventCategory, array $params, ?int $userId = null): void
	{
		if (!Loader::includeModule('pull'))
		{
			return;
		}

		if ($userId !== null)
		{
			PushService::addEvent([$userId], $params);
		}
		else
		{
			$tag = $eventCategory->getClosed()
				? $this->generateTagByCategoryId($eventCategory->getId())
				: PushTagEnum::EVENT_CATEGORY->name;
			PushService::addEventByTag($tag, $params);
		}
	}

	private function __construct()
	{
	}
}
