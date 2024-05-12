<?php

namespace Bitrix\Calendar\Sync\Icloud;

use Bitrix\Calendar\Core\Base\DateTimeZone;
use Bitrix\Calendar\Core\Base\SingletonTrait;
use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Core\Event\Properties\Remind;
use Bitrix\Calendar\ICal\IcsBuilder;
use Bitrix\Calendar\Rooms;
use Bitrix\Calendar\Sync\Util\EventDescription;
use Bitrix\Calendar\Util;
use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;

class EventBuilder
{
	use SingletonTrait;

	/**
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws SystemException
	 * @throws \Exception
	 */
	public function getContent(Event $event, ?array $data = null): ?array
	{
		if (!Loader::includeModule('dav'))
		{
			return null;
		}

		$content = [
			'TYPE' => 'VEVENT',
			'CREATED' => date('Ymd\\THis\\Z', $event->getDateCreate()->getTimestamp()),
			'LAST-MODIFIED' => date('Ymd\\THis\\Z', $event->getDateModified()->getTimestamp()),
			'DTSTAMP' => date('Ymd\\THis\\Z', $event->getDateModified()->getTimestamp()),
			'UID' => $event->getUid(),
			'SUMMARY' => $event->getName(),
		];

		if ($event->isFullDayEvent())
		{
			$content['DTSTART'] = [
				'VALUE' => $event->getStart()->format('Ymd'),
				'PARAMETERS' => ['VALUE' => 'DATE'],
			];
			$content['DTEND'] = [
				'VALUE' => $event->getEnd()->add('1 day')->format('Ymd'),
				'PARAMETERS' => ['VALUE' => 'DATE'],
			];
		}
		else
		{
			$content['DTSTART'] = [
				'VALUE' => $event->getStart()->format('Ymd\\THis'),
				'PARAMETERS' => ['TZID' => $this->prepareTimeZone($event->getStartTimeZone())],
			];
			$content['DTEND'] = [
				'VALUE' => $event->getEnd()->format('Ymd\\THis'),
				'PARAMETERS' => ['TZID' => $this->prepareTimeZone($event->getEndTimeZone())],
			];
		}

		if ($event->getOriginalDateFrom())
		{
			if ($event->isFullDayEvent())
			{
				$content['RECURRENCE-ID'] = [
					'VALUE' => $event->getOriginalDateFrom()->format('Ymd'),
					'PARAMETERS' => ['VALUE' => 'DATE'],
				];
			}
			else
			{
				$content['RECURRENCE-ID'] = [
					'VALUE' => $event->getOriginalDateFrom()->format('Ymd\\THis'),
					'PARAMETERS' => ['TZID' => $this->prepareTimeZone($event->getStartTimeZone())],
				];
			}
		}

		if ($event->getAccessibility() === 'free')
		{
			$content['TRANSP'] = 'TRANSPARENT';
		}
		else
		{
			$content['TRANSP'] = 'OPAQUE';
		}

		if ($event->getLocation() && $event->getLocation()->getActualLocation())
		{
			$content['LOCATION'] = Rooms\Util::getTextLocation($event->getLocation()->getActualLocation());
		}

		$importance = $event->getImportance();
		if ($importance === 'low')
		{
			$content['PRIORITY'] = 9;
		}
		else if ($importance === 'high')
		{
			$content['PRIORITY'] = 1;
		}
		else
		{
			$content['PRIORITY'] = 5;
		}

		$content['DESCRIPTION'] = $this->prepareDescription($event);
		if (!$content['DESCRIPTION'])
		{
			unset($content['DESCRIPTION']);
		}

		if ($event->getRemindCollection() && $event->getRemindCollection()->getCollection())
		{
			$content['@VALARM'] = $this->prepareReminders($event);
		}

		if ($event->isRecurrence())
		{
			$content['RRULE'] = IcsBuilder::prepareRecurrenceRule($event->getRecurringRule(), $event->getStartTimeZone());
		}

		$content['SEQUENCE'] = $event->getVersion();

		if ($event->getExcludedDateCollection() && $event->isRecurrence())
		{
			$content['EXDATE'] = $this->prepareExcludedDates($event);
		}

		$this->prepareOuterParams($data, $content);

		return $content;
	}

	/**
	 * @param Event $event
	 *
	 * @return string
	 */
	private function prepareDescription(Event $event): string
	{
		return (new EventDescription())->prepareForExport($event);
	}

	/**
	 * @param Remind $remind
	 * @param bool $isFullDay
	 *
	 * @return array|null[]
	 * @throws \Bitrix\Calendar\Core\Event\Tools\PropertyException
	 */
	private function prepareReminderValue(
		Remind $remind,
		bool $isFullDay
	): array
	{
		$valueType = '';
		$value = '';

		if ($remind->getUnits() === 'minutes')
		{
			$valueType = 'DURATION';
			if ($remind->getTime() === 60 || $remind->getTime() === 120)
			{
				$value = '-PT' . $remind->getTime() / 60 . 'H';
			}
			else if ($remind->getTime() === 0)
			{
				$value = 'PT' . $remind->getTime() . 'S';
			}
			else
			{
				$value = '-PT' . $remind->getTime() . 'M';
			}
		}
		else if ($remind->getSpecificTime() && $remind->getDaysBefore() !== null)
		{
			$valueType = 'DURATION';
			$diff = $remind->getTimeBeforeStartInMinutes();
			$parsedDiff = Util::minutesToDayHoursMinutes(abs($diff));
			if ($isFullDay  && $remind->getDaysBefore() === 0)
			{
				$value = 'PT' . $parsedDiff['hours'] . 'H';
			}
			else if (
				($remind->getDaysBefore() === 0 && !$isFullDay && $diff > 0)
				|| ($remind->getDaysBefore() === 1 && $parsedDiff['days'] === 0)
			)
			{
				$hours = '';
				$minutes = '';
				if ($parsedDiff['hours'])
				{
					$hours = $parsedDiff['hours'] . 'H';
				}
				if ($parsedDiff['minutes'])
				{
					$minutes = $parsedDiff['minutes'] . 'M';
				}
				$value = '-PT' . $hours . $minutes;
			}
			else if ($parsedDiff['days'] > 0)
			{
				$hours = '';
				$minutes = '';
				$value = '-P' . $parsedDiff['days'] . 'D';
				if ($parsedDiff['hours'])
				{
					$hours = $parsedDiff['hours'] . 'H';
				}
				if ($parsedDiff['minutes'])
				{
					$minutes = $parsedDiff['minutes'] . 'M';
				}
				if ($hours || $minutes)
				{
					$value .= 'T' . $hours . $minutes;
				}
			}
			else
			{
				return [null, null];
			}
		}
		else if ($remind->getSpecificTime())
		{
			$valueType = 'DATE-TIME';
			$value = date('Ymd\\THis\\Z', $remind->getSpecificTime()->getTimestamp());
		}

		return [$value, $valueType];
	}

	/**
	 * @param DateTimeZone|null $timeZone
	 *
	 * @return string
	 */
	private function prepareTimeZone(?DateTimeZone $timeZone): string
	{
		if ($timeZone)
		{
			return $timeZone->getTimeZone()->getName();
		}

		return 'UTC';
	}

	/**
	 * @param Event $event
	 *
	 * @return array
	 * @throws \Bitrix\Calendar\Core\Event\Tools\PropertyException
	 */
	private function prepareReminders(Event $event): array
	{
		$result = [];
		/** @var Remind $remind */
		foreach ($event->getRemindCollection()->getCollection() as $remind)
		{
			[$value, $valueType] = $this->prepareReminderValue(
				$remind,
				$event->isFullDayEvent()
			);

			if (!$value || !$valueType)
			{
				continue;
			}

			$uuId = VendorSyncService::generateUuid();
			$result[] = [
				'X-WR-ALARMUID' => $uuId,
				'UID' => $uuId,
				'TYPE' => 'VALARM',
				'ACTION' => 'DISPLAY',
				'TRIGGER' => [
					'PARAMETERS' => ['VALUE' => $valueType],
					'VALUE' => $value,
				],
			];
		}

		return $result;
	}

	/**
	 * @param Event $event
	 *
	 * @return array
	 */
	private function prepareExcludedDates(Event $event): array
	{
		$result = [];
		$exDate = $event->getExcludedDateCollection()->getCollection();
		foreach ($exDate as $date)
		{
			$fields = $date->getFields();
			if ($event->isFullDayEvent())
			{
				$result[] = [
					'VALUE' => date('Ymd', MakeTimeStamp($fields['date'])),
					'PARAMETERS' => ['VALUE' => 'DATE'],
				];
			}
			else
			{
				$result[] = [
					'VALUE' => date('Ymd', MakeTimeStamp($fields['date']))
						. 'T' . $event->getStart()->format('His'),
					'PARAMETERS' => ['TZID' => $this->prepareTimeZone($event->getStartTimeZone())],
				];
			}
		}

		return $result;
	}

	/**
	 * @param array|null $data
	 * @param array $content
	 *
	 * @return void
	 */
	private function prepareOuterParams(?array $data, array &$content): void
	{
		if (!$data)
		{
			return;
		}

		if ($data['ATTENDEE'])
		{
			foreach ($data['ATTENDEE'] as $attendee)
			{
				$value = $attendee['VALUE'];
				unset($attendee['VALUE']);

				$content['ATTENDEE'][] = [
					'PARAMETERS' => $attendee,
					'VALUE' => $value,
				];
			}
		}

		if ($data['ATTACH'])
		{
			foreach ($data['ATTACH'] as $attachment)
			{
				$value = $attachment['VALUE'];
				unset($attachment['VALUE']);

				$content['ATTACH'][] = [
					'PARAMETERS' => $attachment,
					'VALUE' => $value,
				];
			}
		}

		if ($data['ORGANIZER'])
		{
			$value = $data['ORGANIZER']['VALUE'];
			unset($data['ORGANIZER']['VALUE']);

			$content['ORGANIZER'] = [
				'PARAMETERS' => $data['ORGANIZER'],
				'VALUE' => $value,
			];
		}

		if ($data['URL'])
		{
			$content['URL'] = $data['URL'];
		}
	}
}