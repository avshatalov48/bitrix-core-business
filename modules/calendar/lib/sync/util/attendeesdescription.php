<?php

namespace Bitrix\Calendar\Sync\Util;

use Bitrix\Calendar\Core\Event\Event;
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

		return trim($description);
	}

	/**
	 * @param Event $event
	 *
	 * @return string|null
	 */
	public static function makeDescription(Event $event): ?string
	{
		$description = '';
		$attendees = $event->getAttendeesCollection()->getFields()['attendeesCodesCollection'];
		if ($attendees && count($attendees) > 1)
		{
			$languageId = \CCalendar::getUserLanguageId($event->getOwner()->getId());
			$description = (new AttendeesDescription($languageId))
				->addAttendeesToDescription($attendees, $event->getDescription())
			;
		}
		else if ($event->getDescription())
		{
			$description = $event->getDescription();
		}

		return $description;
	}

	/**
	 * @param array $attendeesCodes
	 * @param ?string $description
	 *
	 * @return string
	 */
	private function addAttendeesToDescription(array $attendeesCodes, ?string $description): string
	{
		$result = Loc::getMessage('CAL_SYNC_UTIL_ATTENDEES', false, $this->languageId)
			. ': '
			. $this->prepareUserNames($attendeesCodes)
			. ';'
			. "\r\n\r\n"
		;

		if ($description)
		{
			$result .= $description;
		}

		return $result;
	}

	/**
	 * @param array $codes
	 *
	 * @return string
	 */
	private function prepareUserNames(array $codes): string
	{
		$users = \CCalendar::GetDestinationUsers($codes, true);

		$names = array_map(function($userName) {
			return $userName['FORMATTED_NAME'];
		}, $users);

		return implode(', ', $names);
	}
}