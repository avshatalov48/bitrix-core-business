<?php

namespace Bitrix\Calendar\ICal;

use Bitrix\Calendar\Core\Base\BaseException;
use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Util;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/calendar/lib/ical/incomingeventmanager.php');
Loc::loadMessages(__FILE__);

class IcsManager
{
	private static ?IcsManager $instance = null;

	private const FILE_TYPE = 'text/calendar';
	private const FILE_NAME = 'event';
	private const FILE_EXTENSION = '.ics';
	private const MODULE_ID = 'calendar';

	protected function __construct()
	{
	}

	public static function getInstance(): IcsManager
	{
		if (!self::$instance)
		{
			self::$instance = new IcsManager();
		}

		return self::$instance;
	}

	/**
	 * @throws BaseException
	 */
	public function createIcsFile(Event $event, array $params): int
	{
		$fileData = $this->getIcsFileData($event, $params);
		$fileId = \CFile::SaveFile($fileData, 'calendar');

		if (!$this->checkIcsFileExistence($fileId))
		{
			throw new BaseException('Error saving ICS file');
		}

		return $fileId;
	}

	private function getIcsFileData(Event $event, array $params): array
	{
		$fileContent = $this->getIcsFileContent($event, $params);
		return [
			'name' => self::FILE_NAME . self::FILE_EXTENSION,
			'type' => self::FILE_TYPE,
			'MODULE_ID' => self::MODULE_ID,
			'content' => $fileContent,
		];
	}

	private function checkIcsFileExistence(int $fileId): bool
	{
		return is_array(\CFile::GetFileArray($fileId));
	}

	public function getIcsFileContent(Event $event, array $params): string
	{
		$dtEnd = Util::getTimestamp($event->getEnd());
		if ($event->isFullDayEvent())
		{
			$dtEnd += \CCalendar::GetDayLen();
		}
		$icsBuilder = new \Bitrix\Calendar\ICal\IcsBuilder(
			[
				'summary' => $event->getName() ?? '',
				'description' => $this->prepareEventDescription($event, $params),
				'dtstart' => Util::getTimestamp($event->getStart()),
				'dtend' => $dtEnd,
				'dtstamp' => Util::getTimestamp($event->getDateCreate()),
				'location' => $this->prepareLocationField($event),
				'uid' => $event->getUid() ?? uniqid('', true),
				'sequence' => $event->getVersion(),
				'last-modified' => $event->getDateModified()->getTimestamp(),
				'priority' => $event->getImportance(),
			],
		);
		$icsBuilder->setFullDayMode($event->isFullDayEvent());
		if (isset($params['organizer']))
		{
			$organizer = $params['organizer'];
			$icsBuilder->setOrganizer($organizer['name'], $organizer['email'] ?? null, $organizer['phone'] ?? null);
		}

		if (!empty($params['attendees']))
		{
			$icsBuilder->setAttendees($params['attendees']);
		}

		if (!$event->isFullDayEvent())
		{
			$icsBuilder->setConfig(
				[
					'timezoneFrom' => $event->getStartTimeZone(),
					'timezoneTo' => $event->getEndTimeZone() ?? $event->getStartTimeZone(),
				]
			);
		}

		if ($event->isRecurrence() && $event->getRecurringRule() !== null)
		{
			$icsBuilder->setRrule($event->getRecurringRule());
		}

		if ($event->isRecurrence() && $event->getExcludedDateCollection()->count() > 0)
		{
			$icsBuilder->setExclude($event->getExcludedDateCollection());
		}

		return $icsBuilder->render();
	}

	public function prepareEventDescription(Event $event, array $params): string
	{
		$languageId = \CCalendar::getUserLanguageId($event->getOwner()?->getId());
		$eventDescription = '';

		if (
			$event->getAttendeesCollection()
			&& ($attendeesCodes = $event->getAttendeesCollection()->getAttendeesCodes())
			&& count($attendeesCodes) > 1
		)
		{
			$eventDescription =
				$this->formatAttendeesDescription($attendeesCodes, $event->getParentId(), $languageId)
				. ';'
			;
		}

		if (!empty($params['eventUrl']))
		{
			if (!empty($eventDescription))
			{
				$eventDescription .= '\\n\\n';
			}

			$eventDescription .= Loc::getMessage('EC_EVENT_LINK') . $params['eventUrl'];
		}

		if (!empty($params['conferenceUrl']))
		{
			if (!empty($eventDescription))
			{
				$eventDescription .= '\\n\\n';
			}

			$eventDescription .= Loc::getMessage('EC_CONFERENCE_LINK') . $params['conferenceUrl'];
		}

		if (!empty($event->getDescription()))
		{
			if (!empty($eventDescription))
			{
				$eventDescription .= '\\n\\n';
			}

			$eventDescription .= Loc::getMessage('EC_CALENDAR_ICS_COMMENT') . ': '. $event->getDescription();
		}

		return $eventDescription;
	}

	private function formatAttendeesDescription(array $codes, ?int $parentId, string $languageId): string
	{
		$users = \CCalendar::GetDestinationUsers($codes, true);

		$names = array_map(static function($user) {
			return $user['FORMATTED_NAME'];
		}, $users);

		$result = Loc::getMessage('EC_ATTENDEES_LIST_TITLE') . ": ";
		if ($names)
		{
			$result .= implode(", ", $names);
		}

		return $result;
	}

	private function prepareLocationField(Event $event): string
	{
		$locationProperty = $event->getLocation();

		return $locationProperty ? \CCalendar::getTextLocation($locationProperty->getActualLocation()) : '';
	}
}
