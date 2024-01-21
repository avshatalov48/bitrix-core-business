<?php

namespace Bitrix\Calendar\Integration\Disk;

use Bitrix\Calendar\Core\Event\Tools\Dictionary;
use Bitrix\Calendar\Internals\EventOriginalRecursionTable;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Type\DateTime;

final class EventConnectorHelper
{
	private int $eventId;
	private ?string $eventOriginalDate;
	private ?DateTime $targetDateTime;

	public function __construct(private int $userId, private string $xmlId)
	{
		$xmlIdExploded = explode('_', $this->xmlId);

		$this->eventId = (int)($xmlIdExploded[1] ?? null);
		$this->eventOriginalDate = $xmlIdExploded[2] ?? null;
		$this->targetDateTime = DateTime::createFromText($this->eventOriginalDate);
	}

	public function canView(int $eventId, int $userId): bool
	{
		return \CCalendarEvent::CanView($eventId, $userId);
	}

	public function findActualEventId(): ?int
	{
		if ($this->eventId <= 0 || empty($this->eventOriginalDate) || empty($this->targetDateTime))
		{
			return null;
		}

		$query = $this->getSearchExceptionsQuery($this->userId);

		$offset = 0;
		while (true)
		{
			$query->setOffset($offset);
			$exceptionEvents = $query->fetchAll();

			$actualEventId = $this->processExceptionEvents($exceptionEvents);

			if ($actualEventId > 0 || count($exceptionEvents) < $query->getLimit())
			{
				break;
			}

			$offset += $query->getLimit();
		}

		return $actualEventId;
	}

	private function getSearchExceptionsQuery(int $userId): Query
	{
		$select = ['ID', 'PARENT_ID', 'DELETED', 'OWNER_ID', 'DATE_FROM_TS_UTC', 'DATE_TO_TS_UTC', 'EXDATE', 'RELATIONS'];

		return \Bitrix\Calendar\Internals\EventTable::query()
			->setSelect($select)
			->registerRuntimeField(
				(new Reference(
					'ORIGINAL_RECURSION',
					EventOriginalRecursionTable::class,
					Join::on('this.PARENT_ID', 'ref.PARENT_EVENT_ID'),
				))
					->configureJoinType(Join::TYPE_LEFT)
			)
			->where('OWNER_ID', $userId)
			->where('CAL_TYPE', Dictionary::CALENDAR_TYPE['user'])
			->where('DELETED', 'N')
			->where('ORIGINAL_RECURSION.ORIGINAL_RECURSION_EVENT_ID', $this->eventId)
			->addOrder('ID', 'DESC')
			->setLimit(50)
		;
	}

	private function processExceptionEvents(array $exceptionEvents): ?int
	{
		foreach ($exceptionEvents as $exceptionEvent)
		{
			//process single exception
			if (!empty($exceptionEvent['RELATIONS']))
			{
				$eventRelations = unserialize($exceptionEvent['RELATIONS'], ['allowed_classes' => false]);
			}
			$commentXmlId = $eventRelations['COMMENT_XML_ID'] ?? '';
			if (!empty($commentXmlId) && $commentXmlId === $this->xmlId)
			{
				return (int)$exceptionEvent['ID'];
			}
			elseif (!empty($commentXmlId))
			{
				continue;
			}

			//process chain exception
			$exceptionDatesParsed = \CCalendarEvent::GetExDate($exceptionEvent['EXDATE']);
			foreach ($exceptionDatesParsed as $exceptionDateParsed)
			{
				if ($exceptionDateParsed === $this->eventOriginalDate)
				{
					continue 2;
				}
			}
			$dateFrom =
				DateTime::createFromTimestamp($exceptionEvent['DATE_FROM_TS_UTC'] + (int)date('Z'))
					->setTime(0,0)
			;
			$dateTo = DateTime::createFromTimestamp($exceptionEvent['DATE_TO_TS_UTC'] + (int)date('Z'));

			$doEventStartsBeforeTargetDate = $dateFrom->getTimestamp() <= $this->targetDateTime->getTimestamp();
			$doEventEndsAfterTargetDate = $dateTo->getTimestamp() >= $this->targetDateTime->getTimestamp();
			if ($doEventStartsBeforeTargetDate && $doEventEndsAfterTargetDate)
			{
				return (int)$exceptionEvent['ID'];
			}
		}

		return null;
	}
}
