<?php

namespace Bitrix\Calendar\Sync\Google\Builders;

use Bitrix\Calendar\Core;
use Bitrix\Calendar\Core\Event\Properties\AttendeeCollection;
use Bitrix\Calendar\Core\Event\Properties\MeetingDescription;
use Bitrix\Calendar\Core\Event\Properties\RecurringEventRules;
use Bitrix\Calendar\Core\Event\Properties\ExcludedDatesCollection;
use Bitrix\Calendar\Sync;
use Bitrix\Calendar\Util;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type;
use Bitrix\Calendar\Core\Event\Properties\RemindCollection;

class BuilderSyncEventFromExternalData implements Core\Builders\Builder
{
	private Sync\Entities\SyncSection $syncSection;
	/**
	 * @var array
	 */
	private array $item;
	/**
	 * @var Sync\Connection\Connection
	 */
	private Sync\Connection\Connection $connection;

	/**
	 * @param mixed $item
	 * @param Sync\Connection\Connection $connection
	 */
	public function __construct(
		array $item,
		Sync\Connection\Connection $connection,
		Sync\Entities\SyncSection $syncSection
	)
	{
		$this->item = $item;
		$this->connection = $connection;
		$this->syncSection = $syncSection;
	}

	/**
	 * @throws \Bitrix\Main\ObjectException
	 */
	public function build()
	{
		$syncEvent = new Sync\Entities\SyncEvent();

		$syncEvent->setAction(Sync\Google\Dictionary::SYNC_ACTION[$this->item['status']]);

		$event = $this->prepareEvent();

		$syncEvent
			->setEventConnection($this->prepareEventConnection($event))
			->setEvent($event)
		;

		return $syncEvent;
	}

	/**
	 * @return Core\Event\Event
	 * @throws \Bitrix\Main\ObjectException
	 */
	public function prepareEvent(): Core\Event\Event
	{
		$owner = $this->syncSection->getSection()->getOwner();
		$event = (new Core\Event\Event)
			->setName($this->getName())
			->setUid($this->item['iCalUID'])
			->setSection($this->syncSection->getSection())
			->setOwner($owner)
			->setCreator($owner)
			->setEventHost($owner)
			->setAttendeesCollection($this->getAttendeeCollection($owner->getId()))
			->setMeetingDescription($this->getDefaultMeeting($owner->getId()))
			->setDescription($this->getDescription())
			->setLocation($this->getLocation())
			->setColor($this->item['colorId'] ?? null)
		;


		if (!empty($this->item['start']))
		{
			if (!empty($this->item['start']['date']))
			{
				$event->setIsFullDay(true);
				$date = new Type\Date($this->item['start']['date'], \DateTimeInterface::ATOM);
				$event->setStart(new Core\Base\Date($date));
			}
			elseif (!empty($this->item['start']['dateTime']))
			{
				$event->setIsFullDay(false);
				$timeZone = Util::prepareTimezone($this->item['start']['timeZone'] ?? null);
				$date = (new Type\DateTime(
					$this->item['start']['dateTime'],
					\DateTimeInterface::ATOM,
					Util::prepareTimezone())
				);
				$date->setTimeZone($timeZone);

				$event->setStart(new Core\Base\Date($date));
				$event->setStartTimeZone(new Core\Base\DateTimeZone($timeZone));
			}
		}

		if (!empty($this->item['end']))
		{
			if (!empty($this->item['end']['date']))
			{
				$date = new Type\Date($this->item['end']['date'], \DateTimeInterface::ATOM);
				$event->setEnd((new Core\Base\Date($date))->sub('1 day'));
			}
			elseif (!empty($this->item['end']['dateTime']))
			{
				$timeZone = Util::prepareTimezone($this->item['end']['timeZone'] ?? null);
				$date = new Type\DateTime(
					$this->item['end']['dateTime'],
					\DateTimeInterface::ATOM,
					Util::prepareTimezone()
				);
				$date->setTimeZone($timeZone);

				$event->setEnd(new Core\Base\Date($date));
				$event->setEndTimeZone(new Core\Base\DateTimeZone($timeZone));
			}
		}

		if (isset($this->item['transparency']) && ($this->item['transparency'] === 'transparent'))
		{
			$event->setAccessibility('free');
		}

		if (isset($this->item['visibility']) && $this->item['visibility'] === 'private')
		{
			$event->setIsPrivate(true);
		}

		if (!empty($this->item['created']))
		{
			$event->setDateCreate(
				Core\Base\Date::createDateTimeFromFormat($this->item['created'],
					Sync\Google\Helper::DATE_TIME_FORMAT_WITH_MICROSECONDS)
			);
		}

		if (!empty($this->item['updated']))
		{
			$event->setDateModified(
				Core\Base\Date::createDateTimeFromFormat($this->item['updated'],
					Sync\Google\Helper::DATE_TIME_FORMAT_WITH_MICROSECONDS)
			);
		}

		if (!empty($this->item['reminders']))
		{
			$event->setRemindCollection($this->getReminders($event->getStart()));
		}

		$event->setRecurringRule($this->prepareRecurringRule());
		$event->setExcludedDateCollection($this->prepareExcludedDatesCollection());

		$event->setOriginalDateFrom($this->prepareOriginalStart());

		return $event;
	}

	/**
	 * @param Core\Event\Event $event
	 * @return Sync\Connection\EventConnection
	 */
	public function prepareEventConnection(Core\Event\Event $event): Sync\Connection\EventConnection
	{
		return (new Sync\Connection\EventConnection)
			->setConnection($this->connection)
			->setEntityTag($this->item['etag'] ?? null)
			->setVendorEventId($this->item['id'] ?? null)
			// ->setVendorVersionId(($this->item['sequence'] ?? 0))
			->setVendorVersionId($this->item['etag'] ?? null)
			->setVersion(($this->item['sequence'] ?? 0))
			// ->setVersion($event->getVersion())
			->setLastSyncStatus(Sync\Dictionary::SYNC_STATUS['success'])
			->setRecurrenceId($this->item['recurringEventId'] ?? null)
			->setEvent($event)

		;
	}

	/**
	 * @return RecurringEventRules|null
	 */
	private function prepareRecurringRule(): ?RecurringEventRules
	{
		if (empty($this->item['recurrence']))
		{
			return null;
		}
		foreach ($this->item['recurrence'] as $row)
		{
			if (strpos($row, 'RRULE:') === 0)
			{
				$rrule = [];

				$rules = explode(';', substr($row, 6));

				foreach ($rules as $rule)
				{
					if (empty($rule))
					{
						continue;
					}
					[$name, $value] = explode('=', $rule);

					if (empty($name) || empty($value))
					{
						continue;
					}

					$rrule[$name] = $value;
				}

				if (!empty($rrule['FREQ']) && in_array($rrule['FREQ'], RecurringEventRules::FREQUENCY, true))
				{
					$property = new RecurringEventRules($rrule['FREQ']);
				}
				else
				{
					return null;
				}

				if (!empty($rrule['COUNT']))
				{
					$property->setCount((int)$rrule['COUNT']);
				}
				if (!empty($rrule['INTERVAL']))
				{
					$property->setInterval((int)$rrule['INTERVAL']);
				}
				if (!empty($rrule['UNTIL']))
				{
					try
					{
						$phpDate = new \DateTime($rrule['UNTIL']);
						$date = new Core\Base\Date(Type\DateTime::createFromPhp($phpDate));

						$property->setUntil($date);
					}
					catch (\Exception $e)
					{
						return null;
					}
				}
				if (!empty($rrule['BYDAY']) && $rrule['FREQ'] === RecurringEventRules::FREQUENCY_WEEKLY)
				{
					$property->setByDay(explode(',', $rrule['BYDAY']));
				}

				return $property;
			}
		}

		return null;
	}

	/**
	 * @return ExcludedDatesCollection|null
	 * @throws \Bitrix\Main\ObjectException
	 */
	private function prepareExcludedDatesCollection(): ?ExcludedDatesCollection
	{
		if (empty($this->item['recurrence']))
		{
			return null;
		}

		$exDatesCollection = new ExcludedDatesCollection();
		foreach ($this->item['recurrence'] as $row)
		{
			if (strpos($row, 'EXDATE;') === 0)
			{
				$exDate = explode(':', substr($row, 7));

				if ($exDate[0] === 'VALUE=DATE-TIME')
				{
					$date = Core\Base\Date::createDateTimeFromFormat(
						$exDate[1],
						Sync\Google\Helper::EXCLUDED_DATE_TIME_FORMAT
					);

					if ($date)
					{
						$exDatesCollection->add($date);
					}
				}
				else if ($exDate[0] === 'VALUE=DATE')
				{
					$date = Core\Base\Date::createDateTimeFromFormat(
						$exDate[1],
						Sync\Google\Helper::EXCLUDED_DATE_FORMAT
					);

					if ($date)
					{
						$exDatesCollection->add($date);
					}
				}
			}
		}

		if ($exDatesCollection->count())
		{
			return $exDatesCollection;
		}

		return null;
	}

	/**
	 * @return Core\Base\Date|null
	 * @throws \Bitrix\Main\ObjectException
	 */
	public function prepareOriginalStart(): ?Core\Base\Date
	{
		if (!empty($this->item['originalStartTime']))
		{
			if (!empty($this->item['originalStartTime']['dateTime']))
			{
				return Core\Base\Date::createDateTimeFromFormat(
					$this->item['originalStartTime']['dateTime'],
					\DateTimeInterface::ATOM
					);
			}

			if (!empty($this->item['originalStartTime']['date']))
			{
				return Core\Base\Date::createDateFromFormat(
					$this->item['originalStartTime']['date'],
					Sync\Google\Helper::DATE_FORMAT
				);
			}
		}

		return null;
	}

	/**
	 * @return Core\Event\Properties\Location
	 */
	private function getLocation(): ?Core\Event\Properties\Location
	{
		if (!empty($this->item['location']))
		{
			$parsedLocation = \Bitrix\Calendar\Rooms\Util::unParseTextLocation($this->item['location']);

			return new Core\Event\Properties\Location($parsedLocation['NEW']);
		}

		return null;
	}

	/**
	 * @return string
	 */
	private function getDescription(): string
	{
		if (!empty($this->item['description']))
		{
			$languageId = \CCalendar::getUserLanguageId($this->syncSection->getSection()->getOwner()->getId());
			$this->item['description'] = \CCalendar::ParseHTMLToBB($this->item['description']);

			return (new Sync\Util\EventDescription())->prepareAfterImport($this->item['description'], $languageId);
		}

		return '';
	}

	/**
	 * @param Core\Base\Date $start
	 * @return RemindCollection
	 */
	private function getReminders(Core\Base\Date $start): RemindCollection
	{
		$collection = new RemindCollection();
		$collection->setEventStart($start);

		if (!empty($this->item['reminders']['overrides']) && is_array($this->item['reminders']['overrides']))
		{
			foreach ($this->item['reminders']['overrides'] as $remind)
			{
				$collection->add((new Core\Event\Properties\Remind())
					->setTimeBeforeEvent($remind['minutes'])
					->setEventStart($start)
				);
			}
		}

		if (!empty($this->item['reminders']['useDefault']))
		{
			$collection->add((new Core\Event\Properties\Remind())
				->setTimeBeforeEvent(30)
				->setEventStart($start)
			);
		}

		return $collection;
	}

	/**
	 * @return mixed|string|null
	 */
	private function getName()
	{
		if (empty($this->item['summary']))
		{
			IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/calendar/classes/general/calendar_js.php");
			$this->item['summary'] = Loc::getMessage('EC_DEFAULT_ENTRY_NAME');
		}

		return $this->item['summary'];
	}

	/**
	 * @param int $userId
	 *
	 * @return AttendeeCollection
	 */
	private function getAttendeeCollection(int $userId): AttendeeCollection
	{
		return (new AttendeeCollection())
			->setAttendeesCodes(['U' . $userId])
		;
	}

	/**
	 * @param int $userId
	 *
	 * @return MeetingDescription
	 */
	private function getDefaultMeeting(int $userId): MeetingDescription
	{
		return (new MeetingDescription())
			->setHostName(\CCalendar::GetUserName($userId))
			->setIsNotify(true)
			->setReInvite(false)
			->setAllowInvite(false)
			->setMeetingCreator($userId)
			->setHideGuests(true)
			->setLanguageId(\CCalendar::getUserLanguageId($userId))
		;
	}
}
