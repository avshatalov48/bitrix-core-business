<?php


namespace Bitrix\Calendar\ICal\MailInvitation;


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
		$localEvent = Helper::getEventByUId($this->icalComponent->getEvent()->getUid());
		if ((int)$localEvent['OWNER_ID'] === $this->userId)
		{
			CCalendar::DeleteEvent($localEvent['ID'], true, ['sendNotification' => true]);
			return true;
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
}