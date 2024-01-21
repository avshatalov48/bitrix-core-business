<?php


namespace Bitrix\Calendar\ICal\MailInvitation;


use Bitrix\Calendar\Core\Event\Properties\ExcludedDatesCollection;
use Bitrix\Calendar\ICal\Parser\Calendar;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use CCalendar;

class IncomingInvitationCancelHandler extends IncomingInvitationHandler
{
	/**
	 * @var int
	 */
	private $userId;
	/**
	 * @var Calendar
	 */
	private $icalComponent;

	/**
	 * IncomingInvitationCancelHandler constructor.
	 */
	public function __construct()
	{
	}

	/**
	 * @param int $userId
	 * @param Calendar $icalComponent
	 * @return IncomingInvitationCancelHandler
	 */
	public static function createWithComponent(int $userId, Calendar $icalComponent): IncomingInvitationCancelHandler
	{
		$handler = new self();
		$handler->userId = $userId;
		$handler->icalComponent = $icalComponent;

		return $handler;
	}

	/**
	 * @return bool
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function handle(): bool
	{
		$icalEvent = $this->icalComponent->getEvent();
		$event = Helper::getEventByUId($icalEvent->getUid());

		if ($event)
		{
			if ($icalEvent->getRecurrenceId() !== null)
			{
				$date = $this->getExdateFromRecurrenceId($icalEvent->getRecurrenceId());
				if ($date !== null)
				{
					\CCalendarEvent::ExcludeInstance(
						$event['ID'],
						$date->format(ExcludedDatesCollection::EXCLUDED_DATE_FORMAT)
					);

					return true;
				}
			}
			else
			{
				return CCalendar::DeleteEvent($event['ID'], true, [
					'sendNotification' => true,
					'userId' => (int)$event['OWNER_ID'],
				]);
			}
		}

		return false;
	}

	/**
	 * @param mixed $userId
	 * @return IncomingInvitationCancelHandler
	 */
	public function setUserId($userId): IncomingInvitationCancelHandler
	{
		$this->userId = $userId;

		return $this;
	}

	private function getExdateFromRecurrenceId(?\Bitrix\Calendar\ICal\Parser\ParserPropertyType $property)
	{
		if ($property->getParameterValueByName('value') === 'DATE')
		{
			return Helper::getIcalDate($property->getValue());
		}
		elseif ($tz = $property->getParameterValueByName('tzid'))
		{
			return Helper::getIcalDateTime($property->getValue(), $tz);
		}

		return null;
	}
}
