<?php

namespace Bitrix\Calendar\ICal;

use Bitrix\Calendar\Core;
use Bitrix\Calendar\ICal\Builder\Attendee;
use Bitrix\Calendar\ICal\Builder\AttendeesCollection;
use Bitrix\Calendar\ICal\MailInvitation\Helper;
use Bitrix\Calendar\ICal\MailInvitation\IncomingInvitationRequestHandler;
use Bitrix\Calendar\Public;
use Bitrix\Calendar\Sharing;
use Bitrix\Calendar\Util;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\Encoding;
use Bitrix\Security\LogicException;
use Bitrix\Calendar\ICal\Basic\{Dictionary, ICalUtil};

IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/calendar/lib/ical/mailinvitation/senderrequestinvitation.php');

class OutcomingEventManager
{
	const ATTACHMENT_NAME = 'invite.ics';
	const CHARSET = 'utf-8';
	const CONTENT_TYPE = 'text/calendar';
	const MAX_SENT = 3;

	/**
	 * @var string
	 */
	private $method;
	/**
	 * @var string
	 */
	private $uid;
	/**
	 * @var string
	 */
	private $status;
	/**
	 * @var array
	 */
	private $eventFields;
	private AttendeesCollection $attendees;
	/**
	 * @var array
	 */
	private $receiver;
	/**
	 * @var array
	 */
	private $sender;
	private $answer;
	/**
	 * @var mixed
	 */
	private $changeFields;
	private $counterInvitations;

	public static function createInstance(array $params): OutcomingEventManager
	{
		return new self($params);
	}

	public function __construct(array $params)
	{
		$this->method = $params['icalMethod'];
		$this->eventFields = $params['arFields'];
		$this->attendees = $params['userIndex'];
		$this->receiver = $params['receiver'];
		$this->sender = $params['sender'];
		$this->changeFields = $params['changeFields'] ?? null;
		$this->counterInvitations = 0;
		$this->answer = $params['answer'] ?? null;
	}

	public function __serialize(): array
	{
		return [
			'method' => $this->method,
			'eventFields' => $this->eventFields,
			'attendees' => $this->attendees,
			'receiver' => $this->receiver,
			'sender' => $this->sender,
			'changeFields' => $this->changeFields,
		];
	}

	public function __unserialize(array $data): void
	{
		$this->method = $data['method'];
		$this->eventFields = $data['eventFields'];
		$this->attendees = $data['attendees'];
		$this->receiver = $data['receiver'];
		$this->sender = $data['sender'];
		$this->changeFields = $data['changeFields'];
	}

	public function inviteUser(): OutcomingEventManager
	{
		$this->checkOrganizerEmail();
		$filesContent = $this->getRequestContent();
		$mailEventFields = $this->getRequestMailEventFields();
		$files = $this->getFiles();
		$this->status = \CEvent::sendImmediate('SEND_ICAL_INVENT', SITE_ID, $mailEventFields, "Y", "", $files, '', $filesContent);

		return $this;
	}

	public function replyInvitation(): OutcomingEventManager
	{
		$this->prepareEventFields();

		$filesContent = $this->getReplyContent();
		$mailEventFields = $this->getReplyMailEventFields();
		$files = $this->getFiles();
		$this->status = \CEvent::sendImmediate('SEND_ICAL_INVENT', SITE_ID, $mailEventFields, "Y", "", $files, '', $filesContent);

		return $this;
	}

	public function cancelInvitation(): OutcomingEventManager
	{
		$filesContent = $this->getCancelContent();
		$mailEventFields = $this->getCancelMailEventFields();
		$files = [];
		$this->status = \CEvent::sendImmediate('SEND_ICAL_INVENT', SITE_ID, $mailEventFields, "Y", "", $files, '', $filesContent);

		return $this;
	}

	public function getUId(): string
	{
		return $this->uid;
	}

	public function incrementCounterInvitations()
	{
		$this->counterInvitations++;
	}

	public function getEventId()
	{
		return $this->eventFields['ID'];
	}

	public function getEvent()
	{
		return $this->eventFields;
	}

	public function getMethod()
	{
		return $this->method;
	}

	public function getCountInvitations()
	{
		return $this->counterInvitations;
	}

	public function getStatus(): string
	{
		return $this->status;
	}

	public function getReceiver()
	{
		return $this->receiver;
	}

	private function getSenderAddress(): string
	{
		return  $this->eventFields['MEETING']['MAIL_FROM'] ?? $this->sender['EMAIL'];
	}

	private function getReceiverAddress(): string
	{
		if (isset($this->receiver['MAILTO']))
		{
			return $this->receiver['MAILTO'];
		}

		return $this->receiver['EMAIL'];
	}

	private function getBodyMessage(): string
	{
		return 'ical body message';
	}

	private function getSubjectMessage(): string
	{
		$result = '';
		$siteName = \COption::GetOptionString("main", "site_name", '');
		if ($siteName !== '')
		{
			$result = "[".$siteName."]";
		}

		$result .= ' ' . $this->getLocMeetingStatus();

		$result .= ": ".$this->eventFields['NAME'];

		return $result;
	}

	protected function getLocMeetingStatus(): string
	{
		return match ($this->method)
		{
			'request' => Loc::getMessage('EC_CALENDAR_ICAL_MAIL_METHOD_REQUEST'),
			'edit' => Loc::getMessage('EC_CALENDAR_ICAL_MAIL_METHOD_EDIT'),
			'cancel' => Loc::getMessage('EC_CALENDAR_ICAL_MAIL_METHOD_CANCEL'),
			'reply' => match ($this->answer)
			{
				IncomingInvitationRequestHandler::MEETING_STATUS_ACCEPTED_CODE => Loc::getMessage('EC_CALENDAR_ICAL_MAIL_METHOD_REPLY_ACCEPTED'),
				IncomingInvitationRequestHandler::MEETING_STATUS_DECLINED_CODE => Loc::getMessage('EC_CALENDAR_ICAL_MAIL_METHOD_REPLY_DECLINED'),
				default => '',
			},
			default => '',
		};
	}

	private function getFiles(): array
	{
		return [];
	}

	private function getRequestContent(): array
	{
		$attachmentManager = new OutcomingAttachmentManager ($this->eventFields, $this->attendees, $this->method);
		$attachmentManager->prepareRequestAttachment();
		$this->uid = $attachmentManager->getUid();
		$fileContent = Encoding::convertEncoding($attachmentManager->getAttachment(), SITE_CHARSET, "utf-8");
		return [[
			'CONTENT' => $fileContent,
			'CONTENT_TYPE' => self::CONTENT_TYPE,
			'METHOD' => Dictionary::METHODS[$this->method],
			'CHARSET' => self::CHARSET,
			'NAME' => self::ATTACHMENT_NAME,
			'ID' => Helper::getUniqId(),
		]];
	}

	private function getRequestMailEventFields(): array
	{
		return [];
	}

	private function getReplyMailEventFields(): array
	{
		return [
			'EMAIL_FROM' => $this->getSenderAddress(),
			'EMAIL_TO' => $this->getReceiverAddress(),
			'MESSAGE_SUBJECT' => $this->getSubjectMessage(),
			'MESSAGE_PHP' => $this->getReplyBodyMessage(),

			'LOC_MEETING_STATUS' => $this->getLocMeetingStatus(),
			'STATUS' => 'event',
			'EVENT_NAME' => $this->eventFields['NAME'],
			'DATE_FROM' => $this->eventFields['DATE_FROM'],
			'DATE_TO' => $this->eventFields['DATE_TO'],
			'IS_FULL_DAY' => $this->eventFields['DT_SKIP_TIME'] === 'Y',
			'TZ_FROM' => $this->eventFields['TZ_FROM'],
			'TZ_TO' => $this->eventFields['TZ_TO'],
			'AVATARS' => $this->eventFields['AVATARS'],
			'OWNER_STATUS' => $this->eventFields['OWNER_STATUS'],
			'RRULE' => $this->getRRuleString(),
			'BITRIX24_LINK' => Sharing\Helper::getBitrix24Link(),
		];
	}

	protected function getRRuleString(): string
	{
		$rrule = \CCalendarEvent::ParseRRULE($this->eventFields['RRULE']);
		if (is_array($rrule))
		{
			return Helper::getIcalTemplateRRule(
				$rrule,
				[
					'DATE_FROM' => $this->eventFields['DATE_FROM'],
				],
			);
		}

		return '';
	}

	private function getCancelMailEventFields()
	{
		return [
			"=Reply-To" => $this->getOrganizerName().' <'.$this->getReceiverAddress().'>',
			"=From" => $this->getOrganizerName().' <'.$this->getSenderAddress().'>',
			"=Message-Id" => $this->getMessageId(),
			"=In-Reply-To" => $this->getMessageReplyTo(),
			'EMAIL_FROM' => $this->getSenderAddress(),
			'EMAIL_TO' => $this->getReceiverAddress(),
			'MESSAGE_SUBJECT' => $this->getSubjectMessage(),
			'MESSAGE_PHP' => $this->getBodyMessage(),
			'DATE_FROM' => $this->eventFields['DATE_FROM'],
			'DATE_TO' => $this->eventFields['DATE_TO'],
			'NAME' => $this->eventFields['NAME'],
			'DESCRIPTION' => str_replace("\r\n", "#$&#$&#$&", $this->eventFields['DESCRIPTION']),
			'ATTENDEES' => $this->getAttendeesList(),
			'ORGANIZER' => $this->getOrganizerName(),
			'LOCATION' => $this->eventFields['LOCATION'],
			'FILES_LINK' =>$this->getFilesLink(),
			'METHOD' => $this->method,
		];
	}

	private function getAttendeesList(): string
	{
		if ($this->eventFields['MEETING']['HIDE_GUESTS'] ?? false)
		{
			return (string)Loc::getMessage('EC_CALENDAR_ICAL_MAIL_HIDE_GUESTS_INFORMATION');
		}

		$attendees = [];

		/** @var Attendee $attendee */
		foreach ($this->attendees as $attendee)
		{
			if (!empty($attendee->getFullName()))
			{
				$attendees[] = $attendee->getFullName();
			}
		}

		return implode(", ", $attendees);
	}

	private function getOrganizerName(): string
	{
		/** @var Attendee $organizer */
		$organizer = $this->eventFields['ICAL_ORGANIZER'];
		return $organizer->getFullName() . ' (' . $organizer->getEmail() .')';
	}

	private function getFilesLink()
	{
		if (empty($this->eventFields['ATTACHES']))
		{
			return '';
		}

		$attaches = [];

		foreach ($this->eventFields['ATTACHES'] as $attach)
		{
			$attaches[] = '<a href="'.$attach['link'].'">'.$attach['name'].'</a><br />' ;
		}

		return implode(" ", $attaches);
	}

	private function getMessageId(): string
	{
		return "<CALENDAR_EVENT_".$this->eventFields['PARENT_ID']."@".$GLOBALS["SERVER_NAME"].">";
	}

	private function getMessageReplyTo(): string
	{
		return $this->getMessageId();
	}

	private function getReplyContent(): array
	{
		$attachmentManager = new OutcomingAttachmentManager ($this->eventFields, $this->attendees, $this->method);
		$attachmentManager->prepareReplyAttachment();
		$fileContent = Encoding::convertEncoding($attachmentManager->getAttachment(), SITE_CHARSET, "utf-8");
		return [[
			'CONTENT' => $fileContent,
			'CONTENT_TYPE' => self::CONTENT_TYPE,
			'METHOD' => Dictionary::METHODS[$this->method],
			'CHARSET' => self::CHARSET,
			'NAME' => self::ATTACHMENT_NAME,
			'ID' => Helper::getUniqId(),
		]];
	}

	private function getReplyBodyMessage()
	{
		return 'reply body message';
	}

	private function getCancelContent(): array
	{
		$attachmentManager = new OutcomingAttachmentManager ($this->eventFields, $this->attendees, $this->method);
		$attachmentManager->prepareCancelAttachment();
		$fileContent = Encoding::convertEncoding($attachmentManager->getAttachment(), SITE_CHARSET, "utf-8");
		return [[
			'CONTENT' => $fileContent,
			'CONTENT_TYPE' => self::CONTENT_TYPE,
			'METHOD' => Dictionary::METHODS[$this->method],
			'CHARSET' => self::CHARSET,
			'NAME' => self::ATTACHMENT_NAME,
			'ID' => ICalUtil::getUniqId(),
		]];
	}

	private function getDateForTemplate()
	{
		$res = Util::getIcalTemplateDate([
			'DATE_FROM' => $this->eventFields['DATE_FROM'],
			'DATE_TO' => $this->eventFields['DATE_TO'],
			'TZ_FROM' => $this->eventFields['TZ_FROM'],
			'TZ_TO' => $this->eventFields['TZ_TO'],
			'FULL_DAY' => $this->eventFields['SKIP_TIME'],
		]);
		$offset = (Util::getDateObject(null, false, $this->eventFields['TZ_FROM']))->format('P');
		$res .= ' (' . $this->eventFields['TZ_FROM'] . ', ' . 'UTC' . $offset . ')';

		if (isset($this->eventFields['RRULE']['FREQ']) && $this->eventFields['RRULE']['FREQ'] !== 'NONE')
		{
			$rruleString = Util::getIcalTemplateRRule($this->eventFields['RRULE'],
				[
					'DATE_FROM' => $this->eventFields['DATE_FROM'],
					'DATE_TO' => $this->eventFields['DATE_TO'],
					'TZ_FROM' => $this->eventFields['TZ_FROM'],
					'TZ_TO' => $this->eventFields['TZ_TO'],
					'FULL_DAY' => $this->eventFields['SKIP_TIME'],
				]
			);
			$res .= ', (' . $rruleString . ')';
		}

		return $res;
	}

	private function getEditTitle()
	{
		if ($this->method !== 'edit')
		{
			return null;
		}

		if (count($this->changeFields) === 1)
		{
			switch ($this->changeFields[0]['fieldKey'])
			{
				case 'DATE_FROM':
					return Loc::getMessage('EC_CALENDAR_ICAL_MAIL_CHANGE_FIELD_TITLE_DATE');
				case 'LOCATION':
					return Loc::getMessage('EC_CALENDAR_ICAL_MAIL_CHANGE_FIELD_TITLE_LOCATION');
				case 'ATTENDEES':
					return Loc::getMessage('EC_CALENDAR_ICAL_MAIL_CHANGE_FIELD_TITLE_ATTENDEES');
				case 'RRULE':
					return Loc::getMessage('EC_CALENDAR_ICAL_MAIL_CHANGE_FIELD_TITLE_RRULE');
				case 'NAME':
					return Loc::getMessage('EC_CALENDAR_ICAL_MAIL_CHANGE_FIELD_TITLE_NAME');
				default:
					return Loc::getMessage('EC_CALENDAR_ICAL_MAIL_METHOD_EDIT');
			}
		}

		return Loc::getMessage('EC_CALENDAR_ICAL_MAIL_METHOD_EDIT');
	}

	private function getChangeFieldsString()
	{
		$res = [];
		if (!empty($this->changeFields))
		{
			foreach ($this->changeFields as $changeField)
			{
				$res[] = $changeField['fieldKey'];
			}
		}
		return implode(';', $res);
	}

	private function checkOrganizerEmail()
	{
		if (Loader::includeModule('mail'))
		{
			if (empty($this->sender['EMAIL']))
			{
				$boxes = \Bitrix\Mail\MailboxTable::getUserMailboxes($this->eventFields['MEETING_HOST']);
				$email = array_shift($boxes)['EMAIL'];
				$this->sender['EMAIL'] = $email;
				$this->attendees[$this->eventFields['MEETING_HOST']]['EMAIL'] = $email;
			}
		}
	}

	protected function prepareEventFields(): void
	{
		$event = (new Core\Mappers\Event())->getByArray($this->eventFields);
		$this->eventFields['DESCRIPTION'] = Public\PublicEvent::prepareEventDescriptionForIcs($event);
		$this->eventFields['OWNER_STATUS'] = $this->answer === 'accepted' ? 'Y' : 'N';
		$this->eventFields['AVATARS'] = [];

		/** @var $parentEvent Core\Event\Event */
		$parentEvent = (new Core\Mappers\Event())->getById($this->eventFields['PARENT_ID']);
		$this->eventFields['DAV_XML_ID'] = $parentEvent->getUid();
	}
}
