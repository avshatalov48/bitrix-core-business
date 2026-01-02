<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\ICloud\Processor\Event;

// TODO: Remove API from sync when we switch to the new one
use Bitrix\Calendar\Sync\Util\EventDescription;
use Bitrix\Calendar\Rooms;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use CCalendar;
use CCalendarEvent;

class EventFieldsPreparer
{
	private const RECURRENCE_PERIODS = ['DAILY', 'WEEKLY', 'MONTHLY', 'YEARLY'];
	private const CALENDAR_TYPE = 'user';
	private const BUSY_ACCESSIBILITY = 'busy';

	/**
	 * @throws ObjectException
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function prepare(array $fields, int $sectionId, int $entityId): array
	{
		$preparedEventData = $this->fillDefaultData($fields, $sectionId, $entityId);

		$this
			->prepareAccessibility($fields, $preparedEventData)
			->prepareDates($fields, $preparedEventData)
			->prepareTimezones($fields, $preparedEventData)
			->prepareRecurrenceId($fields, $preparedEventData)
			->prepareMeeting($fields, $preparedEventData, $entityId)
			->prepareAttendees($fields, $preparedEventData)
			->prepareRelations($preparedEventData)
			->prepareRemindSettings($fields, $preparedEventData)
			->prepareImportance($fields, $preparedEventData)
			->prepareLocation($fields, $preparedEventData)
			->prepareDescription($fields, $preparedEventData)
			->prepareRecurrenceRule($fields, $preparedEventData)
		;

		return $preparedEventData;
	}

	private function fillDefaultData(array $fields, int $sectionId, int $entityId): array
	{
		return [
			'ID' => (int)($fields['ID'] ?? null),
			'NAME' => (
				$fields['NAME']
				?? Loc::getMessage('CALENDAR_ICLOUD_EVENT_FIELDS_PREPARER_DEFAULT_EVENT_NAME')
			),
			'CAL_TYPE' => self::CALENDAR_TYPE,
			'OWNER_ID' => $entityId,
			'CREATED_BY' => $entityId,
			'ATTENDEES_CODES' => ['U' . $entityId],
			'SECTIONS' => [$sectionId],
			'IS_MEETING' => ($fields['IS_MEETING'] ?? null) ? true : null,
			'IMPORTANCE' => ($fields['IMPORTANCE'] ?? 'normal'),
			'REMIND' => is_array($fields['REMIND'] ?? null) ? $fields['REMIND'] : [],
			'RRULE' => is_array($fields['RRULE'] ?? null) ? $fields['RRULE'] : [],
			'VERSION' => (int)($fields['VERSION'] ?? 0),
			'PRIVATE_EVENT' => (bool)($fields['PRIVATE_EVENT'] ?? null),
			'SKIP_TIME' => ($fields['SKIP_TIME'] ?? null) ? 'Y' : 'N',
			'ACTIVE' => 'Y',
			'DELETED' => 'N',
			'TIMESTAMP_X' => new DateTime(),
		];
	}

	private function prepareDates(array $fields, array &$preparedEventData): self
	{
		if (!empty($fields['RECURRENCE_ID_DATE']))
		{
			$preparedEventData['ORIGINAL_DATE_FROM'] = $fields['RECURRENCE_ID_DATE'];
		}

		$dateFrom = $fields['DATE_FROM'] ?? null;
		$dateTo = $fields['DATE_TO'] ?? null;

		if (empty($fields['SKIP_TIME']))
		{
			$preparedEventData['DATE_FROM'] = $dateFrom;
			$preparedEventData['DATE_TO'] = $dateTo;
		}
		else
		{
			$preparedEventData['DATE_FROM'] = CCalendar::date(
				CCalendar::timestamp($dateFrom, false),
			);

			$preparedEventData['DATE_TO'] = CCalendar::date(
				CCalendar::timestamp($dateTo) - CCalendar::getDayLen(),
				false,
			);
		}

		return $this;
	}

	private function prepareAccessibility(array $fields, array &$preparedEventData): self
	{
		if (empty($fields['ID']))
		{
			$accessibility = $fields['PROPERTY_ACCESSIBILITY'] ?? self::BUSY_ACCESSIBILITY;
		}
		else
		{
			$accessibility = $fields['ACCESSIBILITY'] ?? self::BUSY_ACCESSIBILITY;
		}

		$preparedEventData['ACCESSIBILITY'] = $accessibility;

		return $this;
	}

	private function prepareTimezones(array $fields, array &$preparedEventData): self
	{
		$timezoneFrom = $fields['TZ_FROM'] ?? null;
		$timezoneTo = $fields['TZ_TO'] ?? null;

		if (empty($timezoneFrom) && $preparedEventData['SKIP_TIME'] === 'N')
		{
			$currentTimezone = (new DateTime())->getTimeZone()->getName();
			$preparedEventData['TZ_TO'] = $currentTimezone;
			$preparedEventData['TZ_FROM'] = $currentTimezone;
		}
		else
		{
			$preparedEventData['TZ_FROM'] = $timezoneFrom;
			$preparedEventData['TZ_TO'] = $timezoneTo;
		}

		return $this;
	}

	private function prepareRecurrenceId(array $fields, array &$preparedEventData): self
	{
		if (empty($fields['RECURRENCE_ID']))
		{
			return $this;
		}

		$preparedEventData['RECURRENCE_ID'] = $fields['RECURRENCE_ID'];

		return $this;
	}

	private function prepareMeeting(array $fields, array &$preparedEventData, int $entityId): self
	{
		if (!empty($fields['MEETING']))
		{
			$preparedEventData['MEETING'] = $fields['MEETING'];
			$preparedEventData['MEETING_HOST'] = $fields['MEETING']['MEETING_CREATOR'] ?? null;

			return $this;
		}

		$preparedEventData['MEETING'] = [
			'HOST_NAME' => CCalendar::GetUserName($entityId),
			'NOTIFY' => true,
			'REINVITE' => false,
			'ALLOW_INVITE' => false,
			'MEETING_CREATOR' => $entityId,
			'HIDE_GUESTS' => true,
			'LANGUAGE_ID' => CCalendar::getUserLanguageId($entityId),
		];
		$preparedEventData['MEETING_HOST'] = $entityId;
		$preparedEventData['MEETING_STATUS'] = 'H';

		return $this;
	}

	private function prepareAttendees(array $fields, array &$preparedEventData): self
	{
		if (empty($fields['ATTENDEES_CODES']))
		{
			return $this;
		}

		$preparedEventData['ATTENDEES_CODES'] = $fields['ATTENDEES_CODES'];

		return $this;
	}

	private function prepareRelations(array &$preparedEventData): self
	{
		if (empty($preparedEventData['ORIGINAL_DATE_FROM']) || empty($preparedEventData['RECURRENCE_ID']))
		{
			return $this;
		}

		$preparedEventData['RELATIONS'] = [
			'COMMENT_XML_ID' => CCalendarEvent::GetEventCommentXmlId($preparedEventData),
		];

		return $this;
	}

	/**
	 * @throws ObjectException
	 */
	private function prepareRemindSettings(array $fields, array &$preparedEventData): self
	{
		if (empty($fields['PROPERTY_REMIND_SETTINGS']))
		{
			return $this;
		}

		if (is_array($fields['PROPERTY_REMIND_SETTINGS']))
		{
			foreach ($fields['PROPERTY_REMIND_SETTINGS'] as $remind)
			{
				$parsedRemind = explode('_', $remind);

				$this->prepareRemind($parsedRemind, $preparedEventData);
			}
		}
		else
		{
			$parsedRemind = explode('_', $fields['PROPERTY_REMIND_SETTINGS']);

			$this->prepareRemind($parsedRemind, $preparedEventData);
		}

		return $this;
	}

	/**
	 * @throws ObjectException
	 */
	private function prepareRemind(array $parsedRemind, array &$preparedEventData): void
	{
		$remindSectionsCount = count($parsedRemind);

		if ($remindSectionsCount === 2 && $parsedRemind[1] === 'date')
		{
			$preparedEventData['REMIND'][] = [
				'type' => $parsedRemind[1],
				'value' => new DateTime($parsedRemind[0], 'Ymd\\THis\\Z'),
			];

			return;
		}

		if ($remindSectionsCount === 2 && $preparedEventData['SKIP_TIME'] === 'Y')
		{
			$preparedEventData['REMIND'][] = [
				'type' => 'daybefore',
				'before' => 1,
				'time' => 1440 - (int)$parsedRemind[0] * 60,
			];

			return;
		}

		if ($remindSectionsCount === 2)
		{
			$preparedEventData['REMIND'][] = [
				'count' => (int)$parsedRemind[0],
				'type' => $parsedRemind[1],
			];

			return;
		}

		if ($remindSectionsCount === 3 && $parsedRemind[2] === 'daybefore')
		{
			$preparedEventData['REMIND'][] = [
				'type' => $parsedRemind[2],
				'before' => 0,
				'time' => (int)$parsedRemind[0] * 60,
			];

			return;
		}

		if ($remindSectionsCount === 4 && $preparedEventData['SKIP_TIME'] === 'Y')
		{
			$preparedEventData['REMIND'][] = [
				'type' => 'daybefore',
				'before' => $parsedRemind[0] + 1,
				'time' => 1440 - (int)$parsedRemind[2] * 60,
			];

			return;
		}

		if ($remindSectionsCount === 4)
		{
			$preparedEventData['REMIND'][] = [
				'type' => $parsedRemind[3],
				'count' => (int)$parsedRemind[0] * 24 + $parsedRemind[2],
			];
		}
	}

	private function prepareImportance(array $fields, array &$preparedEventData): self
	{
		if (empty($fields['PROPERTY_IMPORTANCE']))
		{
			return $this;
		}

		$preparedEventData['IMPORTANCE'] = $fields['PROPERTY_IMPORTANCE'];

		return $this;
	}

	/**
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	private function prepareLocation(array $fields, array &$preparedEventData): self
	{
		if (empty($fields['PROPERTY_LOCATION']))
		{
			return $this;
		}

		$preparedEventData['LOCATION'] = Rooms\Util::unParseTextLocation($fields['PROPERTY_LOCATION']);

		return $this;
	}

	private function prepareDescription(array $fields, array &$preparedEventData): self
	{
		$preparedEventData['DESCRIPTION'] = $fields['DESCRIPTION'] ?? '';

		if (empty($fields['DETAIL_TEXT']))
		{
			return $this;
		}

		if (isset($fields['MEETING']) && !empty($fields['MEETING']['LANGUAGE_ID']))
		{
			$languageId = $fields['MEETING']['LANGUAGE_ID'];
		}
		else
		{
			$languageId = CCalendar::getUserLanguageId((int)$preparedEventData['OWNER_ID']);
		}

		$preparedEventData['DESCRIPTION'] = (new EventDescription())->prepareAfterImport($fields['DETAIL_TEXT'], $languageId);

		return $this;
	}

	private function prepareRecurrenceRule(array $fields, array &$preparedEventData): self
	{
		if (
			empty($fields['PROPERTY_PERIOD_TYPE'])
			|| !in_array($fields['PROPERTY_PERIOD_TYPE'], self::RECURRENCE_PERIODS, true)
		)
		{
			return $this;
		}

		$preparedEventData['RRULE']['FREQ'] = $fields['PROPERTY_PERIOD_TYPE'];
		$preparedEventData['RRULE']['INTERVAL'] = $fields['PROPERTY_PERIOD_COUNT'] ?? null;

		if (empty($preparedEventData['DT_LENGTH']) && !empty($fields['PROPERTY_EVENT_LENGTH']))
		{
			$preparedEventData['DT_LENGTH'] = (int)$preparedEventData['PROPERTY_EVENT_LENGTH'];
		}
		elseif (isset($fields['DT_TO_TS'], $fields['DT_FROM_TS']))
		{
			$preparedEventData['DT_LENGTH'] = $fields['DT_TO_TS'] - $fields['DT_FROM_TS'];
		}
		else
		{
			$preparedEventData['DT_LENGTH'] = null;
		}

		if ($preparedEventData['RRULE']['FREQ'] === 'WEEKLY' && !empty($fields['PROPERTY_PERIOD_ADDITIONAL']))
		{
			$preparedEventData['RRULE']['BYDAY'] = [];

			$days = explode(',', (string)$fields['PROPERTY_PERIOD_ADDITIONAL']);

			foreach ($days as $day)
			{
				$day = CCalendar::weekDayByInd($day, false);

				if ($day !== false)
				{
					$preparedEventData['RRULE']['BYDAY'][] = $day;
				}
			}

			$preparedEventData['RRULE']['BYDAY'] = implode(',', $preparedEventData['RRULE']['BYDAY']);
		}

		if (!empty($fields['PROPERTY_RRULE_COUNT']))
		{
			$preparedEventData['RRULE']['COUNT'] = $fields['PROPERTY_RRULE_COUNT'];
		}
		elseif (!empty($fields['PROPERTY_PERIOD_UNTIL']))
		{
			$preparedEventData['RRULE']['UNTIL'] = $fields['PROPERTY_PERIOD_UNTIL'];
		}
		else
		{
			$preparedEventData['RRULE']['UNTIL'] = $fields['DT_TO_TS'] ?? null;
		}

		if (!empty($fields['EXDATE']))
		{
			$preparedEventData['EXDATE'] = $fields['EXDATE'];
		}

		return $this;
	}
}
