<?php

namespace Bitrix\Calendar\OpenEvents\Service;

use Bitrix\Calendar\Application\AttendeeService;
use Bitrix\Calendar\Core\Common;
use Bitrix\Calendar\Core\Event\Event as CalendarEvent;
use Bitrix\Calendar\Core\EventCategory\EventCategory;
use Bitrix\Calendar\Core\Mappers\Factory;
use Bitrix\Calendar\EventCategory\Event\AfterEventCategoryAttendeesDelete;
use Bitrix\Calendar\OpenEvents\Internals\OpenEventCategoryAttendeeTable;
use Bitrix\Main\DI\ServiceLocator;

final class CategoryAttendeeService
{
	private static array $cache = [];

	private static ?self $instance;

	public static function getInstance(): self
	{
		self::$instance ??= new self();

		return self::$instance;
	}

	public function processAttendeesForNewCategory(
		int $userId,
		EventCategory $eventCategory,
		array $attendeeEntities
	): array
	{
		// TODO: fix or remove attendee codes field
//		$attendeeService = new AttendeeService();
//		$attendeeCodes = $attendeeService->getAttendeeAccessCodes($attendeeEntities, $userId);
//		$attendeeUserIds = \CCalendar::GetDestinationUsers($attendeeCodes);
//		$eventCategory->setAccessCodes($attendeeCodes);
//		$eventCategory->getAttendees()->setAttendeesId($attendeeUserIds);
		$attendeeIds = array_map('intval', $attendeeEntities);
		$eventCategory->getAttendees()->setAttendeesId($attendeeIds);

		return $attendeeIds;
	}

	public function addAttendeesToCategory(int $eventCategoryId, array $attendeeUserIds): void
	{
		$rowsToAdd = [];
		foreach ($attendeeUserIds as $attendee)
		{
			$rowsToAdd[] = [
				'CATEGORY_ID' => $eventCategoryId,
				'USER_ID' => $attendee,
			];
		}
		if ($rowsToAdd)
		{
			OpenEventCategoryAttendeeTable::insertIgnoreMulti($rowsToAdd);
		}
	}

	public function addAttendeesToCategoryByChunk(
		int $eventCategoryId,
		array $attendeeUserIds,
		int $chunkSize = 200
	): void
	{
		if (count($attendeeUserIds) > $chunkSize * 1.1)
		{
			$chunks = array_chunk($attendeeUserIds, $chunkSize);
		}
		else
		{
			$chunks = [$attendeeUserIds];
		}

		foreach ($chunks as $chunk)
		{
			$this->addAttendeesToCategory($eventCategoryId, $chunk);
		}
	}

	public function processAttendeesForExistCategory(
		int $userId,
		EventCategory $eventCategory,
		array $attendeeEntities
	): void
	{
		$attendeeService = new AttendeeService();
		$newAccessCodes = $attendeeService->getAttendeeAccessCodes($attendeeEntities, $userId);
		$oldAccessCodes = $eventCategory->getAccessCodes();
		if ($oldAccessCodes === $newAccessCodes)
		{
			return;
		}

		$eventCategory->setAccessCodes($newAccessCodes);

		$oldAttendeeIds = \CCalendar::GetDestinationUsers($oldAccessCodes);
		$newAttendeeIds = \CCalendar::GetDestinationUsers($newAccessCodes);

		$excluded = array_diff($oldAttendeeIds, $newAttendeeIds);
		if ($excluded)
		{
			$this->deleteAttendeesFromCategory($eventCategory->getId(), $excluded);
		}

		$included = array_diff($newAttendeeIds, $oldAttendeeIds);
		if ($included)
		{
			$this->addAttendeesToCategory($eventCategory->getId(), $included);
		}
	}

	public function deleteAttendeesFromCategory(int $eventCategoryId, array $excludedUserIds): void
	{
		OpenEventCategoryAttendeeTable::deleteByFilter([
			'CATEGORY_ID' => $eventCategoryId,
			'USER_ID' => $excludedUserIds,
		]);

		(new AfterEventCategoryAttendeesDelete($eventCategoryId, $excludedUserIds))->emit();
	}

	public function createSystem(int $categoryId): void
	{
		/** @var Factory $mapperFactory */
		$mapperFactory = ServiceLocator::getInstance()->get('calendar.service.mappers.factory');
		/** @var CalendarEvent $event */
		$eventCategory = $mapperFactory->getEventCategory()->getById($categoryId);
		if ($eventCategory->getClosed())
		{
			return;
		}

		OpenEventCategoryAttendeeTable::add([
			'CATEGORY_ID' => $eventCategory->getId(),
			'USER_ID' => Common::SYSTEM_USER_ID,
		]);
	}

	public function isAttendee(int $categoryId, int $userId): bool
	{
		$key = $this->getKey($categoryId, $userId);

		if (!isset(self::$cache[$key]))
		{
			self::$cache[$key] = (bool)OpenEventCategoryAttendeeTable::query()
				->addSelect('ID')
				->where('CATEGORY_ID', $categoryId)
				->where('USER_ID', $userId)
				->setLimit(1)
				->fetch();
		}

		return self::$cache[$key];
	}

	private function getKey(int $categoryId, int $userId): string
	{
		return sprintf('is_attendee:%d:%d', $categoryId, $userId);
	}

	private function __construct()
	{
	}
}
