<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\ICloud\Processor\EventConnection;

// TODO: Remove API from sync when we switch to the new one
use Bitrix\Calendar\Core\Builders\EventBuilderFromArray;
use Bitrix\Calendar\Sync\Dictionary;
use Bitrix\Calendar\Synchronization\Internal\Entity\EventConnection;
use Bitrix\Calendar\Synchronization\Internal\Entity\SectionConnection;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\ICloud\Dto\EventResponse;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\ICloud\Processor\Event\EventFieldsPreparer;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;

class EventConnectionBuilder
{
	public function __construct(private readonly EventFieldsPreparer $eventFieldsPreparer)
	{
	}

	/**
	 * @throws ArgumentException
	 * @throws ObjectException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function build(
		EventResponse $item,
		SectionConnection $sectionConnection,
	): EventConnection
	{
		$section = $sectionConnection->getSection();

		if (!$section)
		{
			throw new ArgumentException(
				sprintf('Section for section connection "%s" not found', $sectionConnection->getId()),
			);
		}

		$sectionId = (int)$section->getId();
		$ownerId = (int)$section->getOwner()?->getId();

		$eventFields = $this->eventFieldsPreparer->prepare(
			$item->eventData,
			$sectionId,
			$ownerId,
		);

		$event = (new EventBuilderFromArray($eventFields))->build();

		$data = $this->buildData($eventFields);

		return
			(new EventConnection())
				->setEvent($event)
				->setConnection($sectionConnection->getConnection())
				->setVendorEventId($item->id)
				->setRecurrenceId($item->eventData['RECURRING_EVENT_ID'] ?? null)
				->setLastSyncStatus(Dictionary::SYNC_STATUS['success'])
				->setEntityTag($item->etag)
				->setVendorVersionId($item->getVersion())
				->setData($data)
		;
	}

	private function buildData(array $eventFields): array
	{
		$data = [];

		if (!empty($eventFields['ATTENDEE']) || !empty($eventFields['ORGANIZER_ENTITY']))
		{
			$this->parseInvitedAttendees($eventFields, $data);
		}

		if (!empty($eventFields['ATTACH']))
		{
			$this->parseAttachments($eventFields, $data);
		}

		if (!empty($eventFields['URL']))
		{
			$data['URL'] = $eventFields['URL'];
		}

		return $data;
	}

	private function parseInvitedAttendees(array $eventFields, array &$data): void
	{
		$attendees = $eventFields['ATTENDEE'];

		if (!empty($attendees))
		{
			foreach ($attendees as $attendee)
			{
				$attendeeData = [];

				if ($attendee->parameter('CN'))
				{
					$attendeeData['CN'] = $attendee->parameter('CN');
				}

				if ($attendee->parameter('CUTYPE'))
				{
					$attendeeData['CUTYPE'] = $attendee->parameter('CUTYPE');
				}

				if ($attendee->parameter('PARTSTAT'))
				{
					$attendeeData['PARTSTAT'] = $attendee->parameter('PARTSTAT');
				}

				if ($attendee->parameter('ROLE'))
				{
					$attendeeData['ROLE'] = $attendee->parameter('ROLE');
				}

				if ($attendee->parameter('EMAIL'))
				{
					$attendeeData['EMAIL'] = $attendee->parameter('EMAIL');
				}

				if ($attendee->parameter('SCHEDULE-STATUS'))
				{
					$attendeeData['SCHEDULE-STATUS'] = $attendee->parameter('SCHEDULE-STATUS');
				}

				if ($attendee->value())
				{
					$attendeeData['VALUE'] = $attendee->value();
				}

				$data['ATTENDEE'][] = $attendeeData;
			}
		}

		$organizer = $eventFields['ORGANIZER_ENTITY'][0] ?? null;

		if ($organizer)
		{
			if ($organizer->parameter('EMAIL'))
			{
				$data['ORGANIZER']['EMAIL'] = $organizer->parameter('EMAIL');
			}

			if ($organizer->parameter('CN'))
			{
				$data['ORGANIZER']['CN'] = $organizer->parameter('CN');
			}

			if ($organizer->value())
			{
				$data['ORGANIZER']['VALUE'] = $organizer->value();
			}
		}
	}

	private function parseAttachments(array $eventFields, array &$data): void
	{
		$attachments = $eventFields['ATTACH'];

		foreach ($attachments as $attachment)
		{
			$attachmentData = [];

			if ($attachment->parameter('FMTTYPE'))
			{
				$attachmentData['FMTTYPE'] = $attachment->parameter('FMTTYPE');
			}

			if ($attachment->parameter('SIZE'))
			{
				$attachmentData['SIZE'] = $attachment->parameter('SIZE');
			}

			if ($attachment->parameter('FILENAME'))
			{
				$attachmentData['FILENAME'] = $attachment->parameter('FILENAME');
			}

			if ($attachment->parameter('MANAGED-ID'))
			{
				$attachmentData['MANAGED-ID'] = $attachment->parameter('MANAGED-ID');
			}

			if ($attachment->value())
			{
				$attachmentData['VALUE'] = $attachment->value();
			}

			$data['ATTACH'][] = $attachmentData;
		}
	}
}
