<?php

namespace Bitrix\Calendar\Event;

use Bitrix\Calendar\Internals\EventAttendeeTable;
use Bitrix\Calendar\Internals\EventTable;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Query\Query;

final class EventRepository
{
	public static function getEventAttendeesCount(int $eventId): int
	{
		$eventsQuery = EventTable::query();
		$eventsQuery->registerRuntimeField(
			new ReferenceField(
				'ATTENDEES',
				EventAttendeeTable::getEntity(),
				Join::on('this.ID', 'ref.EVENT_ID')->where('ref.DELETED', 'N')
			)
		);

		$eventsQuery->addSelect(Query::expr()->count('ATTENDEES.ID'), 'ATTENDEES_COUNT');
		$eventsQuery->addGroup('ID');
		$eventsQuery->where('ID', $eventId);

		$result = $eventsQuery->fetch();

		return $result ? (int)$result['ATTENDEES_COUNT'] : 0;
	}
}
