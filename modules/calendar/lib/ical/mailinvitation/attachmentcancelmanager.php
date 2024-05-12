<?php


namespace Bitrix\Calendar\ICal\MailInvitation;


use Bitrix\Calendar\ICal\Builder\Calendar;
use Bitrix\Calendar\ICal\Builder\Dictionary;
use Bitrix\Calendar\ICal\Builder\Event;
use Bitrix\Calendar\Internals\EventTable;
use Bitrix\Calendar\Util;
use Bitrix\Main\ObjectException;

/**
 * Class AttachmentCancelManager
 * @package Bitrix\Calendar\ICal\MailInvitation
 */
class AttachmentCancelManager extends AttachmentManager
{
	/**
	 * AttachmentCancelManager constructor.
	 * @param array $event
	 */
	public function __construct(array $event)
	{
		parent::__construct($event);
		$this->uid = $event['DAV_XML_ID'];
	}

	public function getUid(): ?string
	{
		if ($this->uid)
		{
			return $this->uid;
		}

		if ($this->event['ID'])
		{
			$eventFromDb = EventTable::getById($this->event['ID'])->fetch();

			if ($eventFromDb && $eventFromDb['DAV_XML_ID'])
			{
				$this->uid = $eventFromDb['DAV_XML_ID'];

				return $this->uid;
			}
		}

		return null;
	}

	/**
	 * @return string
	 * @throws ObjectException
	 */
	public function getContent(): string
	{
		$event = $this->event;
		$event['SKIP_TIME'] ??= null;
		$event['CREATED'] ??= null;
		$event['MODIFIED'] ??= null;

		$icalEvent = Event::createInstance($this->uid)
			->setName($event['NAME'])
			->setStartsAt(Util::getDateObject($event['DATE_FROM'], $event['SKIP_TIME'], $event['TZ_FROM']))
			->setEndsAt(Util::getDateObject($event['DATE_TO'], $event['SKIP_TIME'], $event['TZ_TO']))
			->setCreatedAt(Util::getDateObject($event['DATE_CREATE'], false, $event['TZ_FROM']))
			->setDtStamp(Util::getDateObject($event['DATE_CREATE'], false, $event['TZ_FROM']))
			->setModified(Util::getDateObject($event['TIMESTAMP_X'], false, $event['TZ_FROM']))
			->setWithTimezone(!$event['SKIP_TIME'])
			->setWithTime(!$event['SKIP_TIME'])
			->setRRule($this->prepareRecurrenceRule($event['RRULE']))
			->setSequence((int)$event['VERSION'])
			->setStatus(Dictionary::EVENT_STATUS['cancelled'])
		;

		return Calendar::createInstance()
			->setMethod('CANCEL')
			->addEvent($icalEvent)
			->get();
	}
}