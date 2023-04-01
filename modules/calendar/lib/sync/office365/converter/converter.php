<?php

namespace Bitrix\Calendar\Sync\Office365\Converter;

use Bitrix\Calendar\Core\Base\Date;
use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Core\Event\Properties\AttendeeCollection;
use Bitrix\Calendar\Core\Event\Properties\Location;
use Bitrix\Calendar\Core\Event\Properties\MeetingDescription;
use Bitrix\Calendar\Core\Event\Properties\RecurringEventRules;
use Bitrix\Calendar\Core\Event\Properties\Remind;
use Bitrix\Calendar\Core\Event\Properties\RemindCollection;
use Bitrix\Calendar\Core\Section\Section;
use Bitrix\Calendar\Sync\Office365\Dto\EventDto;
use Bitrix\Calendar\Sync\Office365\Dto\SectionDto;
use Bitrix\Calendar\Sync\Office365;
use Bitrix\Calendar\Sync;
use Bitrix\Calendar\Sync\Office365\Helper;
use Bitrix\Calendar\Util;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ObjectException;
use Bitrix\Main\Type\DateTime;
use CCalendar;
use DateTimeZone;
use Exception;

class Converter
{
	use Sync\Internals\HasContextTrait;

	private const CALENDAR_TYPE = 'user';

	/**
	 * @param Office365\Office365Context $context
	 */
	public function __construct(Sync\Office365\Office365Context $context)
	{
		$this->context = $context;
	}

	/**
	 * @param EventDto $eventData
	 * @param Section $section
	 * @return Event
	 *
	 * @throws ObjectException
	 */
	public function convertEvent(EventDto $eventData, Section $section): Event
	{
		$start = $this->prepareDate($eventData->start, $eventData->originalStartTimeZone);
		$reminders = $this->makeReminders(
			$eventData->reminderMinutesBeforeStart,
			$eventData->isReminderOn,
			$start,
		);

		$event = (new Event())
//			->setId($this->getId())
			->setName($this->prepareName($eventData->subject))
			->setOwner($section->getOwner())
			->setCreator($section->getOwner())
			->setEventHost($section->getOwner())
			->setLocation($this->prepareLocation($eventData->location))
			->setStart($start)
			->setEnd($this->prepareDate($eventData->end, $eventData->originalEndTimeZone))
			->setStartTimeZone($this->prepareDateTimezone($eventData->start, $eventData->originalStartTimeZone))
			->setEndTimeZone($this->prepareDateTimezone($eventData->end, $eventData->originalEndTimeZone))
			->setIsFullDay($eventData->isAllDay)
			->setAttendeesCollection($this->prepareAttendeeCollection($section->getOwner()->getId()))
			->setRemindCollection($reminders)
			->setSection($section)
			->setDescription($this->prepareBody($eventData->body, $section->getOwner()->getId()))
			->setMeetingDescription($this->prepareDefaultMeeting($section->getOwner()->getId()))
//			->setTransparent($this->getTransparency())
			->setAccessibility(EventConverter::ACCESSIBILITY_IMPORT_MAP[$eventData->showAs] ?? null)
			->setDateModified($this->makeDateFromString($eventData->lastModifiedDateTime))
			->setDateCreate($this->makeDateFromString($eventData->createdDateTime))
			->setImportance($eventData->importance)
//			->setIsPrivate($eventData->sensitivity ) // TODO: need converter
//			->setVersion($this->getVersion())
			->setCalendarType(self::CALENDAR_TYPE)
//			->setUid($this->getUid())
			->setIsActive(!$eventData->isCancelled && !$eventData->isDraft)
			->setIsDeleted($eventData->isCancelled)
//			->setRecurrenceId($this->getRecurrenceId())
//			->setDateCreate($this->getDateCreate())
//			->setDateModified($this->getDateModified())
// 			->setOriginalDateFrom()
//			->setExcludedDateCollection()
			->setRecurringRule($this->makeRecurringRule($eventData->recurrence))
		;
		if (!empty($eventData->originalStart))
		{
			$originalDto = new Office365\Dto\DateTimeDto([
				'dateTime' => $eventData->originalStart,
				'timeZone' => $eventData->originalStartTimeZone,
			]);
			$event->setOriginalDateFrom($this->prepareDate($originalDto, $eventData->originalStartTimeZone));
		}

		// dependence from specific of office all-day events
		if ($event->isFullDayEvent())
		{
			$event->setEnd($event->getEnd()->add("-1 day"));
		}
		return $event;
	}

	/**
	 * @param SectionDto $data
	 *
	 * @return Section
	 */
	public function convertSection(SectionDto $data): Section
	{
		return (new Section())
			->setName($data->name)
			->setColor($this->getOurColor($data->color, $data->hexColor))
		;
	}

	/**
	 * @param Office365\Dto\LocationDto $location
	 *
	 * @return Location|null
	 */
	private function prepareLocation(Office365\Dto\LocationDto $location): ?Location
	{
		$parsedLocation = \Bitrix\Calendar\Rooms\Util::unParseTextLocation($location->displayName);

		return new Location($parsedLocation['NEW']);
	}

	/**
	 * @param Office365\Dto\DateTimeDto $dateDto
	 * @param string|null $originalTZ
	 *
	 * @return Date
	 *
	 * @throws ObjectException
	 * @throws Exception
	 */
	private function prepareDate(Office365\Dto\DateTimeDto $dateDto, string $originalTZ = null): Date
	{
		$tz = Util::isTimezoneValid($dateDto->timeZone ?? '')
			? $dateDto->timeZone
			: $this->getDefaultTimezone();

		$phpDateTime = new \DateTime($dateDto->dateTime, new DateTimeZone($tz));
		$eventDateTime = DateTime::createFromPhp($phpDateTime);

		if ($originalTZ)
		{
			$original = Util::prepareTimezone($originalTZ);
			$eventDateTime->setTimeZone($original);
		}

		return new Date($eventDateTime);
	}

	/**
	 * @return string
	 */
	private function getDefaultTimezone(): string
	{
		return 'UTC';
	}

	/**
	 * @param Office365\Dto\DateTimeDto $dateDto
	 * @param string|null $originalTZ
	 *
	 * @return \Bitrix\Calendar\Core\Base\DateTimeZone
	 */
	private function prepareDateTimezone(Office365\Dto\DateTimeDto $dateDto, string $originalTZ = null): \Bitrix\Calendar\Core\Base\DateTimeZone
	{
		if ($originalTZ)
		{
			$original = Util::prepareTimezone($originalTZ);

			return new \Bitrix\Calendar\Core\Base\DateTimeZone($original);
		}
		$tz = Util::isTimezoneValid($dateDto->timeZone ?? '')
			? $dateDto->timeZone
			: $this->getDefaultTimezone();

		return new \Bitrix\Calendar\Core\Base\DateTimeZone(
			new DateTimeZone($tz)
		);
	}

	/**
	 * @param int $minutes
	 * @param bool $isReminderOn
	 * @param Date $start
	 *
	 * @return RemindCollection
	 */
	private function makeReminders(int $minutes, bool $isReminderOn, Date $start): RemindCollection
	{
		$collection = new RemindCollection();
		$collection->setEventStart($start)->setSingle(true);
		if ($isReminderOn)
		{
			if ($minutes < 0)
			{
				$hours = '+'. abs($minutes) / 60 . ' hour';
				$specificTime = (clone $start)->add($hours);
				$reminder = (new Remind())
					->setSpecificTime($specificTime)
					->setDaysBefore(0)
				;
			}
			else
			{
				$reminder = (new Remind())->setTimeBeforeEvent($minutes, 'minutes');
			}
			$reminder->setEventStart($start);
			$collection->add($reminder);
		}

		return $collection;
	}

	/**
	 * @param Office365\Dto\RecurrenceDto|null $recurrenceDto
	 * @return RecurringEventRules|null
	 *
	 * @throws ObjectException
	 */
	private function makeRecurringRule(?Office365\Dto\RecurrenceDto $recurrenceDto = null): ?RecurringEventRules
	{
		if (!$recurrenceDto)
		{
			return null;
		}
		switch ($recurrenceDto->pattern->type)
		{
			case Helper::RECURRENCE_TYPES['daily']:
				$result = new RecurringEventRules(
					RecurringEventRules::FREQUENCY['daily'],
					$recurrenceDto->pattern->interval
				);

				break;
			case Helper::RECURRENCE_TYPES['weekly']:
				$result = new RecurringEventRules(
					RecurringEventRules::FREQUENCY['weekly'],
					$recurrenceDto->pattern->interval
				);
				if ($recurrenceDto->pattern->daysOfWeek)
				{
					$byDay = array_map(function ($value)
					{
						return strtoupper(substr($value, 0, 2));
					}, $recurrenceDto->pattern->daysOfWeek);
					$result->setByDay($byDay);
				}

				break;
			case Helper::RECURRENCE_TYPES['absoluteMonthly']:
				$result = new RecurringEventRules(
					RecurringEventRules::FREQUENCY['monthly'],
					$recurrenceDto->pattern->interval
				);

				break;
			case Helper::RECURRENCE_TYPES['absoluteYearly']:
				$result = new RecurringEventRules(
					RecurringEventRules::FREQUENCY['yearly'],
					$recurrenceDto->pattern->interval
				);

				break;
			default:
				return null;
		}

		if (!empty($recurrenceDto->range->numberOfOccurrences))
		{
			$result->setCount($recurrenceDto->range->numberOfOccurrences);
		}
		if ($recurrenceDto->range->endDate >= $recurrenceDto->range->startDate)
		{
			$until = new \Bitrix\Main\Type\Date($recurrenceDto->range->endDate, 'Y-m-d');
			$result->setUntil(new Date($until));
		}
		else
		{
			$result->setUntil($this->getFarFarAwayDate());
		}

		return $result;
	}

	/**
	 * @return Date
	 *
	 * @throws ObjectException
	 */
	private function getFarFarAwayDate(): Date
	{
		return new Date(Util::getDateObject('01.01.2038'));
	}

	/**
	 * @param string $time
	 *
	 * @return Date
	 *
	 * @throws ObjectException
	 * @throws Exception
	 */
	private function makeDateFromString(string $time): Date
	{
		return new Date(DateTime::createFromPhp(new \DateTime($time)));
	}

	/**
	 * @param string $color
	 * @param string|null $hexColor
	 *
	 * @return string
	 */
	private function getOurColor(string $color, ?string $hexColor = null): ?string
	{
		return ColorConverter::fromOffice($color, $hexColor);
	}

	/**
	 * @param Office365\Dto\RichTextDto $body
	 * @param int $userId
	 *
	 * @return string
	 */
	private function prepareBody(Office365\Dto\RichTextDto $body, int $userId): string
	{
		if ($body->contentType === 'html')
		{
			$text = CCalendar::ParseHTMLToBB($body->content);
		}
		else
		{
			$text = $body->content;
		}

		$text = html_entity_decode($text, ENT_QUOTES | ENT_XML1);
		$text = html_entity_decode($text, ENT_QUOTES | ENT_XML1);
		$languageId = CCalendar::getUserLanguageId($userId);

		return (new Sync\Util\EventDescription())->prepareAfterImport($text, $languageId);
	}

	/**
	 * @param string|null $name
	 *
	 * @return string|null
	 */
	private function prepareName(?string $name): string
	{
		if (!$name)
		{
			IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/calendar/classes/general/calendar_js.php");
			$name = Loc::getMessage('EC_DEFAULT_ENTRY_NAME');
		}

		return $name;
	}

	/**
	 * @param int $userId
	 *
	 * @return MeetingDescription
	 */
	private function prepareDefaultMeeting(int $userId): MeetingDescription
	{
		return (new MeetingDescription())
			->setHostName(CCalendar::GetUserName($userId))
			->setIsNotify(true)
			->setReInvite(false)
			->setAllowInvite(false)
			->setMeetingCreator($userId)
			->setHideGuests(true)
			->setLanguageId(CCalendar::getUserLanguageId($userId))
		;
	}

	/**
	 * @param int $userId
	 *
	 * @return AttendeeCollection
	 */
	private function prepareAttendeeCollection(int $userId): AttendeeCollection
	{
		return (new AttendeeCollection())
			->setAttendeesCodes(['U' . $userId])
		;
	}
}
