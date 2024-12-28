<?php

namespace Bitrix\Calendar\ICal;

use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\ICal\Basic\Dictionary;
use Bitrix\Calendar\ICal\Basic\RecurrenceRuleProperty;
use Bitrix\Calendar\ICal\Builder\Alarm;
use Bitrix\Calendar\ICal\Builder\Calendar;
use Bitrix\Calendar\ICal\Builder\Event as IcalEvent;
use Bitrix\Calendar\ICal\Builder\StandardObservances;
use Bitrix\Calendar\ICal\Builder\Timezone;
use Bitrix\Calendar\ICal\helper\ReminderHelper;
use Bitrix\Calendar\ICal\MailInvitation\AttachmentManager;
use Bitrix\Calendar\ICal\MailInvitation\Helper;
use Bitrix\Calendar\Util;

final class IcalIcsBuilder extends AttachmentManager
{
	private Event $eventObject;

	public function getContent(): string
	{
		$icalEvent = $this->buildIcalEvent();
		$icalCalendar = $this->buildIcalCalendar();
		$icalCalendar->addEvent($icalEvent);

		return $icalCalendar->get();
	}

	public static function buildFromEvent(Event $eventObject): self
	{
		$eventFields = self::prepareArrayFields($eventObject);
		$manager = self::createInstance($eventFields);
		$manager->eventObject = $eventObject;

		return $manager;
	}

	private static function prepareArrayFields(Event $eventObject): array
	{
		$fields = [
			'DATE_FROM' => $eventObject->getStart()->format(IcsBuilder::DEFAULT_DATETIME_FORMAT),
			'TZ_FROM' => $eventObject->getStartTimeZone(),
			'DATE_TO' => $eventObject->getEnd()->format(IcsBuilder::DEFAULT_DATETIME_FORMAT),
			'TZ_TO' => $eventObject->getEndTimeZone(),
			'CREATED' => $eventObject->getDateCreate()->format(IcsBuilder::DEFAULT_DATETIME_FORMAT),
			'MODIFIED' => $eventObject->getDateModified()->format(IcsBuilder::DEFAULT_DATETIME_FORMAT),
		];
		$parentId = $eventObject->getParentId();
		$hostId = $eventObject->getEventHost()?->getId();
		$fields['ICAL_ORGANIZER'] = Helper::getAttendee(
			$eventObject->getEventHost()?->getId(),
			$parentId,
			false
		);
		$fields['ICAL_ORGANIZER']->setEmail(null);
		$fields['ICAL_ORGANIZER']->setMailto(null);
		if (!$eventObject->getMeetingDescription()?->getHideGuests())
		{
			$fields['ICAL_ATTENDEES'] = Helper::getAttendeesByEventParentId($parentId);
		}

		$fields['ICAL_ATTACHES'] = Helper::getMailAttaches(null, $hostId, $parentId);

		return $fields;
	}

	private function buildIcalEvent(): IcalEvent
	{
		$location = $this->eventObject->getLocation()?->toString()
			? \Bitrix\Calendar\Rooms\Util::getTextLocation($this->eventObject->getLocation()->toString())
			: null;
		$excludeDates = array_map(
			fn($ed) => $ed->getDate(),
			$this->eventObject->getExcludedDateCollection()->getCollection()
		);
		$rrule = $this->eventObject->getRecurringRule()?->toArray();

		// 0199387:
		// Google Calendar not support both fields, so delete UNTIL if COUNT presented
		// From documentation: You can use either COUNT or UNTIL to specify the end of the event recurrence. Don't use both in the same rule.
		// @see: https://developers.google.com/calendar/api/concepts/events-calendars#recurrence_rule
		if (($rrule['COUNT'] ?? null) && ($rrule['UNTIL'] ?? null))
		{
			unset($rrule['UNTIL']);
		}

		$rrule = new RecurrenceRuleProperty($rrule);

		$skipTime = $this->eventObject->isFullDayEvent();
		$priority = Dictionary::PRIORITY_MAP[$this->eventObject->getImportance()] ?? Dictionary::PRIORITY_MAP['default'];

		$icalEvent = (new IcalEvent($this->eventObject->getUid()))
			->setName($this->eventObject->getName())
			->setStartsAt(Util::getDateObject($this->event['DATE_FROM'], $skipTime, $this->event['TZ_FROM']))
			->setEndsAt(Util::getDateObject($this->event['DATE_TO'], $skipTime, $this->event['TZ_TO']))
			->setCreatedAt(Util::getDateObject($this->event['CREATED'], false, $this->event['TZ_FROM']))
			->setDtStamp(Util::getDateObject($this->event['CREATED'], false, $this->event['TZ_FROM']))
			->setModified(Util::getDateObject($this->event['MODIFIED'], false, $this->event['TZ_FROM']))
			->setWithTimezone(!$skipTime)
			->setWithTime(!$skipTime)
			->setTransparent(
				Dictionary::TRANSPARENT[$this->eventObject->getAccessibility()] ?? Dictionary::TRANSPARENT['busy']
			)
			->setRRule($rrule)
			->setExdates($excludeDates)
			->setLocation($location)
			->setSequence($this->eventObject->getVersion())
			->setStatus(Dictionary::INVITATION_STATUS['confirmed'])
			->setPriority($priority)
		;

		if ($this->eventObject->getDescription())
		{
			$icalEvent->setDescription($this->prepareDescription($this->eventObject->getDescription()));
		}

		if ($reminders = $this->eventObject->getRemindCollection()?->getCollection())
		{
			$icalEvent
				->setAlerts(
					array_map(
						function ($r) use ($skipTime) {
							[$value, $valueType] = ReminderHelper::prepareReminderValue($r, $skipTime);

							return new Alarm(type: $valueType, value: $value);
						},
						$reminders
					)
				)
			;
		}

		if (!empty($event['ICAL_ATTENDEES']) && !($event['MEETING']['HIDE_GUESTS'] ?? true))
		{
			$icalEvent->setAttendees($event['ICAL_ATTENDEES']);
		}

		if (!empty($event['ICAL_ORGANIZER']))
		{
			$icalEvent->setOrganizer($event['ICAL_ORGANIZER'], '');
		}

		return $icalEvent;
	}

	private function buildIcalCalendar(): Calendar
	{
		return Calendar::createInstance()
			->setMethod('REQUEST')
			->setTimezones(Timezone::createInstance()
				->setTimezoneId(Helper::getTimezoneObject($this->eventObject->getStartTimeZone()))
				->setObservance(StandardObservances::createInstance()
					->setOffsetFrom(Helper::getTimezoneObject($this->eventObject->getStartTimeZone()))
					->setOffsetTo(Helper::getTimezoneObject($this->eventObject->getEndTimeZone()))
					->setDTStart()
				)
			)
		;
	}
}
