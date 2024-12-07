<?php

namespace Bitrix\Calendar\Internals\Counter\Job;

use Bitrix\Calendar\Core\Common;
use Bitrix\Calendar\Core\Event\Event as CalendarEvent;
use Bitrix\Calendar\Core\EventCategory\EventCategory;
use Bitrix\Calendar\Core\Mappers\Factory;
use Bitrix\Calendar\Internals\Counter\Processor;
use Bitrix\Calendar\Internals\Log\Logger;
use Bitrix\Calendar\OpenEvents\Internals\OpenEventCategoryAttendeeTable;
use Bitrix\Calendar\OpenEvents\Provider\CategoryBanProvider;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Update\Stepper;
use Bitrix\Main\UserTable;

/**
 * OpenEventAdded is the stepper that runs right after a new open event is created
 * It is supposed to up counters for all affected users
 */
final class OpenEventAdded extends Stepper
{
	private const LIMIT = 100;
	protected static $moduleId = Common::CALENDAR_MODULE_ID;

	public function execute(array &$option): bool
	{
		$outerParams = $this->getOuterParams();
		$offset = (int)($option['offset'] ?? 0);
		$eventId = (int)($outerParams[0] ?? 0);

		/** @var Factory $mapperFactory */
		$mapperFactory = ServiceLocator::getInstance()->get('calendar.service.mappers.factory');
		/** @var CalendarEvent $event */
		$calendarEvent = $mapperFactory->getEvent()->getById($eventId);

		if (!$calendarEvent)
		{
			return self::FINISH_EXECUTION;
		}

		$userIds = $this->getAffectedUserIds($calendarEvent, $offset);

		if (empty($userIds))
		{
			return self::FINISH_EXECUTION;
		}

		$this
			->upCounter(event: $calendarEvent, userIds: $userIds)
			->setOptions(options: $option, offset: $offset)
		;

		return self::CONTINUE_EXECUTION;
	}

	private function getAffectedUserIds(CalendarEvent $event, int $offset): array
	{
		/** @var EventCategory $eventCategory */
		$eventCategory = $event->getEventOption()->getCategory();

		try
		{
			$categoryId = $eventCategory->getId();

			$userIds = $eventCategory->getClosed()
				? $this->getChannelUsers(categoryId: $categoryId, offset: $offset)
				: $this->getAllUsers(offset: $offset)
			;

			$categoryBanProvider = new CategoryBanProvider(0);
			$usersWhoBannedTheCategory = $categoryBanProvider->getUsersWhoBannedTheCategory($userIds, $categoryId);

			return array_filter($userIds, static fn(int $userId) => !$usersWhoBannedTheCategory[$userId]);
		}
		catch (\Exception $exception)
		{
			(new Logger())->log($exception);
		}

		return [];
	}

	private function upCounter(CalendarEvent $event, array $userIds): self
	{
		(new Processor\OpenEvent())->upCounter(userIds: $userIds, event: $event);

		return $this;
	}

	private function setOptions(array &$options, int $offset): self
	{
		$options['offset'] = $offset + $this->getLimit();

		return $this;
	}

	private function getAllUsers(int $offset): array
	{
		$userIds = [];

		$query = UserTable::query()
			->setSelect(['ID'])
			->where('ACTIVE', 'Y')
			->where('IS_REAL_USER', 'Y')
			->where('UF_DEPARTMENT', '!=', false)
			->setOffset($offset)
			->setLimit($this->getLimit())
			->exec()
		;

		while ($item = $query->fetch())
		{
			$userIds[] = (int)$item['ID'];
		}

		return $userIds;
	}

	private function getChannelUsers(int $categoryId, int $offset): array
	{
		$userIds = [];

		$query = OpenEventCategoryAttendeeTable::query()
			->setSelect(['USER_ID'])
			->where('CATEGORY_ID', '=', $categoryId)
			->addOrder('USER_ID')
			->setLimit($this->getLimit())
			->setOffset($offset)
			->exec()
		;

		while ($item = $query->fetch())
		{
			$userIds[] = (int)$item['USER_ID'];
		}

		return $userIds;
	}

	private function getLimit(): int
	{
		$limit = \COption::GetOptionString('calendar', 'calendarCounterStepperLimit', '');

		return $limit === ''
			? self::LIMIT
			: (int)$limit
		;
	}
}
