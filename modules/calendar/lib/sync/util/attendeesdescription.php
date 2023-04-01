<?php

namespace Bitrix\Calendar\Sync\Util;

use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Internals\EventTable;
use Bitrix\Main\Localization\Loc;

IncludeModuleLangFile(__FILE__);

class AttendeesDescription
{
	/** @var string $languageId */
	private string $languageId;

	public function __construct(string $languageId)
	{
		$this->languageId = $languageId;
	}

	public function cutAttendeesFromDescription(?string $description): string
	{
		if (!$description)
		{
			return '';
		}

		$pattern = "|"
			. Loc::getMessage('CAL_SYNC_UTIL_ATTENDEES', false, $this->languageId)
			. "(.+?)"
			. "\D+(.?);[\r\n]*|is"
		;
		$description = preg_replace($pattern, "", $description);

		$acceptedAttendeePattern = "|"
			. Loc::getMessage('CAL_SYNC_UTIL_ATTENDEES_STATUS_Y', false, $this->languageId)
			. "(.+?)"
			. "\D+(.?);[\r\n]*|is"
		;
		$description = preg_replace($acceptedAttendeePattern, "", $description);

		return trim($description);
	}

	/**
	 * @param array $attendeesCodes
	 * @param string|null $description
	 * @param int|null $parentId
	 *
	 * @return string
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function addAttendeesToDescription(array $attendeesCodes, ?string $description, ?int $parentId): string
	{
		$result = $this->prepareUserNames($attendeesCodes, $parentId)
			. ';'
		;

		if ($description)
		{
			$result .= "\r\n\r\n" . $description;
		}

		return $result;
	}

	/**
	 * @param array $codes
	 * @param int|null $parentId
	 *
	 * @return string
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function prepareUserNames(array $codes, ?int $parentId): string
	{
		$result = '';
		$declined = [];
		$accepted = [];
		$invited = [];
		$users = \CCalendar::GetDestinationUsers($codes, true);
		$usersMeetingStatus = [];

		if ($parentId)
		{
			$usersMeetingStatus = $this->getUsersMeetingStatus($parentId);
		}

		if (!$usersMeetingStatus)
		{
			$names = array_map(static function($user) {
				return $user['FORMATTED_NAME'];
			}, $users);

			return implode(', ', $names);
		}

		foreach ($users as $user)
		{
			$userName = $user['FORMATTED_NAME'];
			if (isset($usersMeetingStatus[$user['ID']]) && $usersMeetingStatus[$user['ID']] === 'Y')
			{
				$accepted[] = $userName;
			}
			else if (isset($usersMeetingStatus[$user['ID']]) && $usersMeetingStatus[$user['ID']] === 'H')
			{
				$accepted[] = $userName;
			}
			else if (isset($usersMeetingStatus[$user['ID']]) && $usersMeetingStatus[$user['ID']] === 'N')
			{
				$declined[] = $userName;
			}
			else
			{
				$invited[] = $userName;
			}
		}

		if ($accepted)
		{
			$result .= Loc::getMessage('CAL_SYNC_UTIL_ATTENDEES_STATUS_Y', false, $this->languageId)
				. ' '
				. implode(', ', $accepted);
		}
		if ($invited)
		{
			$result .= "\r\n"
				. Loc::getMessage('CAL_SYNC_UTIL_ATTENDEES_STATUS_Q', false, $this->languageId)
				. ' '
				. implode(', ', $invited);
		}
		if ($declined)
		{
			$result .= "\r\n"
				. Loc::getMessage('CAL_SYNC_UTIL_ATTENDEES_STATUS_N', false, $this->languageId)
				. ' '
				. implode(', ', $declined);
		}

		return $result;
	}

	/**
	 * @param int $eventId
	 *
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function getUsersMeetingStatus(int $eventId): array
	{
		$result = [];
		$events = EventTable::query()
			->setSelect(['OWNER_ID', 'MEETING_STATUS'])
			->where('PARENT_ID', $eventId)
			->exec()
		;
		while ($event = $events->fetchObject())
		{
			$result[$event->getOwnerId()] = $event->getMeetingStatus();
		}

		return $result;
	}
}