<?php

namespace Bitrix\Calendar\OpenEvents\Provider;

use Bitrix\Calendar\Core\Common;
use Bitrix\Calendar\Core\Event\Tools\Dictionary;
use Bitrix\Calendar\EventOption\Dto\EventOptionsDto;
use Bitrix\Calendar\Internals\Counter;
use Bitrix\Calendar\Internals\EO_Event_Collection;
use Bitrix\Calendar\Internals\EventAttendeeTable;
use Bitrix\Calendar\Internals\EventTable;
use Bitrix\Calendar\Internals\SectionTable;
use Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventOption;
use Bitrix\Calendar\OpenEvents\Internals\OpenEventCategoryAttendeeTable;
use Bitrix\Calendar\OpenEvents\Internals\OpenEventOptionTable;
use Bitrix\Calendar\OpenEvents\Item\OpenEvent;
use Bitrix\Calendar\OpenEvents\Provider\Event\Filter;
use Bitrix\Calendar\Util;
use Bitrix\Calendar\Integration\Im;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Text\Emoji;
use Bitrix\Main\Type\DateTime;

final class EventProvider
{
	protected int $userId;

	public function __construct(?int $userId = null)
	{
		$this->userId = $userId ?? (int)CurrentUser::get()->getId();
	}

	public function getTsRange(Filter $filter): array
	{
		$filter->fromDate = null;
		$filter->toDate = null;

		$query = EventTable::query()
			->setSelect([
				Query::expr('MIN_TS')->min('DATE_FROM_TS_UTC'),
				Query::expr('MAX_TS')->max('DATE_TO_TS_UTC'),
			])
		;

		$this->prepareFilter($query, $filter);

		$result = $query->fetch();

		if (empty($result['MAX_TS']))
		{
			return [
				'from' => gmmktime(0, 0, 0, 1, 1, 2038),
				'to' => 0,
			];
		}

		return [
			'from' => $result['MIN_TS'],
			'to' => $result['MAX_TS'],
		];
	}

	public function list(Filter $filter): array
	{
		$query = EventTable::query();

		$this->prepareSelect($query);
		$this->prepareFilter($query, $filter);

		$eventCollection = $query->fetchCollection();

		return $this->prepareResult($eventCollection, $filter);
	}

	protected function prepareSelect(Query $query): void
	{
		$query->setSelect([
			'ID',
			'NAME',
			'DATE_FROM',
			'DATE_TO',
			'TZ_FROM',
			'TZ_TO',
			'DT_LENGTH',
			'DT_SKIP_TIME',
			'COLOR',
			'SECTION_ID',
			'RRULE',
			'EXDATE',
			'CREATED_BY',
			'EVENT_OPTIONS.*',
		]);
	}

	protected function prepareFilter(Query $query, Filter $filter): void
	{
		$query->where('CAL_TYPE', Dictionary::CALENDAR_TYPE['open_event']);

		if (!empty($filter->categoriesIds))
		{
			$query->registerRuntimeField(
				(new ReferenceField(
					'EVENT_OPTIONS',
					OpenEventOptionTable::getEntity(),
					Join::on('this.ID', 'ref.EVENT_ID')
						->whereIn('ref.CATEGORY_ID', $filter->categoriesIds),
				))
				->configureJoinType(Join::TYPE_INNER),
			);
		}
		else
		{
			$query->registerRuntimeField(
				new ReferenceField(
					'EVENT_OPTIONS',
					OpenEventOptionTable::getEntity(),
					Join::on('this.ID', 'ref.EVENT_ID'),
				),
			);
		}

		$query->registerRuntimeField(
			(new ReferenceField(
				'CATEGORY_ATTENDEE',
				OpenEventCategoryAttendeeTable::getEntity(),
				Join::on('this.EVENT_OPTIONS.CATEGORY_ID', 'ref.CATEGORY_ID')
					->whereIn('ref.USER_ID', [Common::SYSTEM_USER_ID, $this->userId])
			))
			->configureJoinType(Join::TYPE_INNER)
		);

		if (!empty($filter->fromDate))
		{
			$query->where(
				'DATE_TO_TS_UTC',
				'>=',
				(int)\CCalendar::Timestamp($filter->fromDate, false) - $this->getUserOffset()
			);
		}

		if (!empty($filter->toDate))
		{
			$query->where(
				'DATE_FROM_TS_UTC',
				'<=',
				(int)\CCalendar::Timestamp($filter->toDate, false) - $this->getUserOffset()
			);
		}

		if (!empty($filter->creatorId))
		{
			$query->where('CREATED_BY', $filter->creatorId);
		}

		if ($filter->iAmAttendee === true)
		{
			$query->registerRuntimeField(
				(new ReferenceField(
					'ATTENDEE',
					EventAttendeeTable::getEntity(),
					Join::on('this.ID', 'ref.EVENT_ID')
						->where('ref.OWNER_ID', $this->userId)
						->where('ref.MEETING_STATUS', 'Y')
					,
				))
					->configureJoinType(Join::TYPE_INNER)
				,
			);
		}

		if ($filter->iAmAttendee === false)
		{
			$query->registerRuntimeField(
				new ReferenceField(
					'ATTENDEE',
					EventAttendeeTable::getEntity(),
					Join::on('this.ID', 'ref.EVENT_ID')
						->where('ref.OWNER_ID', $this->userId)
						->where('ref.MEETING_STATUS', 'Y')
					,
				),
			);
			$query->whereNull('ATTENDEE.EVENT_ID');
		}

		if (!empty($filter->query))
		{
			$value = \CCalendarEvent::prepareToken(Emoji::encode($filter->query));

			if (\CCalendarEvent::isFullTextIndexEnabled())
			{
				$searchText = \Bitrix\Main\ORM\Query\Filter\Helper::matchAgainstWildcard($value);
				$query->whereMatch('SEARCHABLE_CONTENT', $searchText);
			}
			else
			{
				$query->whereLike('SEARCHABLE_CONTENT', '%' . $value . '%');
			}
		}

		$query->where('DELETED', $filter->deleted ? 'Y' : 'N');
	}

	protected function getUserOffset(): int
	{
		return Util::getTimezoneOffsetUTC(\CCalendar::GetUserTimezoneName($this->userId));
	}

	protected function prepareResult(EO_Event_Collection $eventCollection, Filter $filter): array
	{
		if ($eventCollection->isEmpty())
		{
			return [];
		}

		$eventIds = $eventCollection->getIdList();
		$sectionIds = array_unique($eventCollection->getSectionIdList());
		$categoryIds = array_unique(
			array_map(fn ($event) => $event->get('EVENT_OPTIONS')?->getCategoryId(), $eventCollection->getAll()),
		);
		$threadIds = array_unique(
			array_map(fn ($event) => $event->get('EVENT_OPTIONS')?->getThreadId(), $eventCollection->getAll()),
		);

		$attendedEventIds = $this->getAttendedEventIds($eventIds, $filter);

		$sectionColors = $this->getSectionColors($sectionIds);

		$categories = (new CategoryProvider())->getCategoryCollection($categoryIds);
		$categoriesChannelIds = array_combine($categories->getIdList(), $categories->getChannelIdList());
		$categoriesNames = array_combine($categories->getIdList(), $categories->getNameList());

		$commentsCounts = Im\Comments::getCounts($threadIds);

		$counter = Counter::getInstance($this->userId);

		$events = [];
		foreach ($eventCollection as $event)
		{
			$eventId = $event->getId();

			/**
			 * @var OpenEventOption $eventOptions
			 */
			$eventOptions = $event->get('EVENT_OPTIONS');
			if (!$eventOptions)
			{
				continue;
			}

			$eventCommentsCount = isset($commentsCounts[$eventOptions->getThreadId()])
				? (int)$commentsCounts[$eventOptions->getThreadId()] - 1
				: 0
			;

			$dateFrom = $event->getDateFrom()->format('d.m.Y');
			$dateTo = $event->getDateTo()->format('d.m.Y');
			$dateFromTs = (new DateTime($dateFrom, 'd.m.Y', new \DateTimeZone('UTC')))->getTimestamp();
			$dateToTs = (new DateTime($dateTo, 'd.m.Y', new \DateTimeZone('UTC')))->getTimestamp();
			if (!$event->getDtSkipTime())
			{
				$dateFromTs = Util::getDateTimestampUtc($event->getDateFrom(), $event->getTzFrom());
				$dateToTs = Util::getDateTimestampUtc($event->getDateTo(), $event->getTzTo());
			}

			$events[] = new OpenEvent(
				id: $eventId,
				name: $event->getName(),
				dateFromTs: $dateFromTs,
				dateToTs: $dateToTs,
				isFullDay: $event->getDtSkipTime(),
				isAttendee: in_array($eventId, $attendedEventIds, true),
				attendeesCount: $eventOptions->getAttendeesCount(),
				creatorId: $event->getCreatedBy(),
				eventOptions: EventOptionsDto::fromArray(json_decode($eventOptions->getOptions(), true)),
				categoryId: $eventOptions->getCategoryId(),
				categoryName: $categoriesNames[$eventOptions->getCategoryId()],
				categoryChannelId: $categoriesChannelIds[$eventOptions->getCategoryId()],
				color: $event->getColor() ?: $sectionColors[$event->getSectionId()] ?: null,
				commentsCount: $eventCommentsCount,
				threadId: $eventOptions->getThreadId(),
				isNew: (bool)$counter->get(Counter\CounterDictionary::COUNTER_NEW_EVENT, $eventId),
				rrule: $event->getRrule(),
				rruleDescription: \CCalendarEvent::GetRRULEDescription([
					'RRULE' => $event->getRrule(),
					'DATE_FROM' => $event->getDateFrom()->toString(),
					'DT_SKIP_TIME' => $event->getDtSkipTime() ? 'Y' : 'N',
				]),
				exdate: $event->getExdate(),
			);
		}

		return $events;
	}

	protected function getAttendedEventIds(array $eventIds, Filter $filter): array
	{
		if ($filter->iAmAttendee === true)
		{
			return $eventIds;
		}

		if ($filter->iAmAttendee === false)
		{
			return [];
		}

		$attendees = EventAttendeeTable::query()
			->setSelect(['OWNER_ID', 'EVENT_ID'])
			->where('OWNER_ID', $this->userId)
			->whereIn('EVENT_ID', $eventIds)
			->where('MEETING_STATUS', 'Y')
			->fetchCollection()
		;

		return $attendees->getEventIdList();
	}

	protected function getSectionColors(array $sectionIds): array
	{
		$sectionsQuery = SectionTable::query()
			->setSelect(['ID', 'COLOR'])
			->whereIn('ID', $sectionIds)
		;

		$sections = $sectionsQuery->fetchCollection();

		return array_combine($sections->getIdList(), $sections->getColorList());
	}
}
