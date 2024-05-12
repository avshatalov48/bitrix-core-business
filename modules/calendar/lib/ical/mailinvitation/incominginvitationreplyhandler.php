<?php


namespace Bitrix\Calendar\ICal\MailInvitation;


use Bitrix\Calendar\ICal\Parser\Calendar;
use Bitrix\Calendar\ICal\Parser\Dictionary;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Event;
use Bitrix\Main\IO\File;
use Bitrix\Main\IO\FileNotFoundException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Text\Encoding;
use CCalendarEvent;

class IncomingInvitationReplyHandler extends IncomingInvitationHandler
{
	public const CONTENT_TYPES = ['application/ics', 'text/calendar'];
	/**
	 * @var Calendar
	 */
	private $component;
	/**
	 * @var bool
	 */
	private $handleStatus = false;

	/**
	 * @param Calendar $component
	 * @return IncomingInvitationReplyHandler
	 */
	public static function fromComponent(Calendar $component): IncomingInvitationReplyHandler
	{
		$handler = new self();
		$handler->component = $component;
		return $handler;
	}

	/**
	 * @return IncomingInvitationReplyHandler
	 */
	public static function createInstance(): IncomingInvitationReplyHandler
	{
		return new self();
	}

	/**
	 * IncomingInvitationReplyHandler constructor.
	 * @param Calendar $component
	 */
	public function __construct()
	{
	}

	/**
	 * @return IncomingInvitationReplyHandler
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function handle(): IncomingInvitationReplyHandler
	{
		$localEvent = Helper::getEventByUId($this->component->getEvent()->getUid());
		if (!is_null($localEvent))
		{
			$user = Helper::getUserById((int)$localEvent['OWNER_ID']);
			if (
				$user
				&& $user['EMAIL']
				&& !is_null($attendeeStatus = $this->getAttendeeStatus($user['EMAIL']))
			)
			{
				$this->sendNotificationGuestReaction($localEvent, $attendeeStatus);
				$this->handleStatus = true;
			}
		}

		return $this;
	}

	/**
	 * @param string $userEmail
	 * @return string|null
	 */
	private function getAttendeeStatus(string $userEmail): ?string
	{
		$attendees = $this->component->getEvent()->getAttendees();
		if (is_iterable($attendees))
		{
			foreach ($attendees as $attendee)
			{
				if ($attendee->getParameterValueByName('email') === $userEmail
					|| $this->getMailTo($attendee->getValue()) === $userEmail)
				{
					$attendeeStatus = $attendee->getParameterValueByName('partstat');
					if(array_key_exists($attendeeStatus,Dictionary::ATTENDEES_STATUS))
					{
						return Dictionary::ATTENDEES_STATUS[$attendeeStatus];
					}
				}
			}
		}

		return null;
	}

	/**
	 * @param array $event
	 * @param string $attendeeStatus
	 */
	public function sendNotificationGuestReaction(array $event, string $attendeeStatus): void
	{
		CCalendarEvent::SetMeetingStatusEx([
			'attendeeId' => $event['OWNER_ID'],
			'eventId' => $event['ID'],
			'status' => $attendeeStatus,
			'personalNotification' => $event['MEETING_HOST'],
			'doSendMail' => false,
		]);
	}

	/**
	 * @return bool
	 */
	public function isSuccess(): bool
	{
		return $this->handleStatus;
	}

	/**
	 * @param Event $event
	 * @return bool
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws LoaderException
	 */
	public static function handleFromRequest(Event $event): bool
	{
		$attachments = $event->getParameter('attachments');
		if (is_array($attachments))
		{
			foreach($attachments as $file)
			{
				if (in_array($file['type'], self::CONTENT_TYPES, true))
				{
					try
					{
						$fileObject = new File($file['tmp_name'], $event->getParameter('site_id'));
						$fileContent = Encoding::convertEncoding($fileObject->getContents(), SenderInvitation::CHARSET, SITE_CHARSET);
					}
					catch (FileNotFoundException $e)
					{
						AddMessage2Log('File ics not found', 'calendar', 2);
						die();
					}

					$icalComponent = InboxManager::createInstance($fileContent)
						->parseContent()
						->getComponent();

					if ($icalComponent->getMethod() === Dictionary::METHOD['reply'])
					{
						return self::fromComponent($icalComponent)
							->handle()
							->isSuccess();
					}
				}
			}
		}

		return false;
	}
}