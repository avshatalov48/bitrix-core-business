<?php

namespace Bitrix\Calendar\Service\Command\Event;

use Bitrix\Calendar\Access\ActionDictionary;
use Bitrix\Calendar\Access\Model\EventModel;
use Bitrix\Calendar\Internals\Exception\LocationBusy;
use Bitrix\Calendar\Internals\Exception\PermissionDenied;
use Bitrix\Calendar\Rooms\AccessibilityManager;
use Bitrix\Calendar\Service\Command\Result;
use Bitrix\Calendar\Util;

class Update extends Base
{
	private bool $canEdit;
	private bool $canEditAttendies;
	private bool $canEditLocation;

	public function checkPermissions(): void
	{
		$model = $this->getAccessEventModel($this->getId());
		$controller = $this->getAccessController();

		$this->canEdit = $controller->check(ActionDictionary::ACTION_EVENT_EDIT, $model);
		$this->canEditAttendies = $controller->check(ActionDictionary::ACTION_EVENT_EDIT_ATTENDEES, $model);
		$this->canEditLocation = $controller->check(ActionDictionary::ACTION_EVENT_EDIT_LOCATION, $model);

		if (!($this->canEdit || $this->canEditAttendies || $this->canEditLocation))
		{
			throw new PermissionDenied();
		}
	}

	public function execute(): Result
	{
		$params = $this->getInitialParams();
		$section = $this->getSection();

		$entryFields = [
			'ID' => $this->getParentEventId(),
		];

		if ($this->canEdit)
		{
			$meetingHost = $params['meeting_host'] ?? \CCalendar::GetUserId();

			$entryFields = array_merge($entryFields, [
				'DATE_FROM' => $params['dates']['date_from'],
				'DATE_TO' => $params['dates']['date_to'],
				'SKIP_TIME' => $params['dates']['skip_time'],
				'TZ_FROM' => $params['timezones']['timezone_from'],
				'TZ_TO' => $params['timezones']['timezone_to'],
				'NAME' => $params['name'],
				'DESCRIPTION' => $params['description'],
				'SECTIONS' => [$this->getSectionId()],
				'COLOR' => $params['color'],
				'ACCESSIBILITY' => $params['accessibility'],
				'IMPORTANCE' => $params['importance'],
				'PRIVATE_EVENT' => $params['private_event'],
				'RRULE' => $params['rrule'],
				'REMIND' => $params['remind'],
				'SECTION_CAL_TYPE' => $section['CAL_TYPE'],
				'SECTION_OWNER_ID' => $section['OWNER_ID'],
				'MEETING_HOST' => $meetingHost,
				'MEETING' => [
					'HOST_NAME' => \CCalendar::GetUserName($meetingHost),
					'NOTIFY' => $params['meeting_notify'] === 'Y',
					'REINVITE' => $params['meeting_reinvite'] === 'Y',
					'ALLOW_INVITE' => $params['allow_invite'] === 'Y',
					'MEETING_CREATOR' => $meetingHost,
					'HIDE_GUESTS' => $params['hide_guests'] === 'Y',
					'CHAT_ID' => $params['chat_id'] ?? null,
				],

			]);
		}

		// Attendees
		if ($this->canEdit || $this->canEditAttendies)
		{
			$entryFields['ATTENDEES_CODES'] = $this->getAttendeeAccessCodes();
			$entryFields['ATTENDEES'] = \CCalendar::GetDestinationUsers($entryFields['ATTENDEES_CODES']);
			$entryFields['IS_MEETING'] = $this->isMeeting($entryFields['ATTENDEES_CODES']);
			$entryFields = $this->excludeAttendees($entryFields);
			$this->checkBusyAttendies($entryFields);
		}

		// Location
		if ($this->canEdit || $this->canEditLocation)
		{
			$entryFields['LOCATION'] = $params['location'];

			if (!AccessibilityManager::checkAccessibility($entryFields['LOCATION'], ['fields' => $entryFields]))
			{
				throw new LocationBusy();
			}
		}

		$newId = \CCalendar::SaveEvent([
			'arFields' => $entryFields,
			'UF' => $params['uf_fields'],
			'silentErrorMode' => false,
			'recursionEditMode' => $params['rec_edit_mode'],
			'currentEventDateFrom' => $params['current_date_from'],
			'sendInvitesToDeclined' => $params['send_invites_again'],
			'requestUid' => $params['request_uid'],
			'checkLocationOccupancy' => $params['check_location_occupancy'],
			'checkPermission' => false,
		]);

		$errors = \CCalendar::GetErrors();

		return new Result($newId, $errors);
	}

	private function getAccessEventModel(int $eventId): EventModel
	{
		return \CCalendarEvent::getEventModelForPermissionCheck($eventId, [], $this->getCurrentUserId());
	}

	private function getParentEventId(): int
	{
		$event = Util::getEventById($this->getId());
		return (int)($event['PARENT_ID'] ?? $this->getId());
	}
}