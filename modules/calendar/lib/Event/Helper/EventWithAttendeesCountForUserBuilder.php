<?php

namespace Bitrix\Calendar\Event\Helper;

use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\EventOption\Dto\EventOptionsDto;
use Bitrix\Calendar\Internals\Counter;
use Bitrix\Calendar\OpenEvents\Item\OpenEvent;
use Bitrix\Calendar\OpenEvents\Provider;
use Bitrix\Main\Type\DateTime;

final class EventWithAttendeesCountForUserBuilder
{
	public static function buildFromEvent(
		Event $event,
		int $userId,
		?bool $isAttendee,
		?int $commentsCount,
	): OpenEvent
	{
		$eventOptions = $event->getEventOption();
		$category = $eventOptions->getCategory();
		$count = $userId === 0
			? null
			: Counter::getInstance($userId)
				->get(Counter\CounterDictionary::COUNTER_NEW_EVENT, $event->getId());

		$dateFrom = $event->getStart()->format('d.m.Y');
		$dateTo = $event->getEnd()->format('d.m.Y');
		$dateFromTs = (new DateTime($dateFrom, 'd.m.Y', new \DateTimeZone('UTC')))->getTimestamp();
		$dateToTs = (new DateTime($dateTo, 'd.m.Y', new \DateTimeZone('UTC')))->getTimestamp();
		if (!$event->isFullDayEvent())
		{
			$dateFromTs = $event->getStart()->getTimestamp();
			$dateToTs = $event->getEnd()->getTimestamp();
		}

		return new OpenEvent(
			id: $event->getId(),
			name: $event->getName(),
			dateFromTs: $dateFromTs,
			dateToTs: $dateToTs,
			isFullDay: $event->isFullDayEvent(),
			isAttendee: $isAttendee,
			attendeesCount: $eventOptions->getAttendeesCount(),
			creatorId: $event->getCreator()->getId(),
			eventOptions: EventOptionsDto::fromArray($eventOptions->getOptions()->toArray()),
			categoryId: $eventOptions->getCategoryId(),
			categoryName: (new Provider\CategoryProvider())->prepareCategoryName($category->getName()),
			categoryChannelId: $category->getChannelId(),
			color: $event->getColor() ?: $event->getSection()->getColor(),
			commentsCount: $commentsCount,
			threadId: $eventOptions->getThreadId(),
			isNew: (bool)$count,
			rrule: $event->getRecurringRule()?->toString(),
			rruleDescription: \CCalendarEvent::GetRRULEDescription([
				'RRULE' => $event->getRecurringRule()?->toArray(),
				'DATE_FROM' => $event->getStart()->toString(),
				'DT_SKIP_TIME' => $event->isFullDayEvent() ? 'Y' : 'N',
			]),
			exdate: $event->getExcludedDateCollection()?->toString(),
		);
	}
}
