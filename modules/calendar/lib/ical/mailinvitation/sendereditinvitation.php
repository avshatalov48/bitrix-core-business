<?php


namespace Bitrix\Calendar\ICal\MailInvitation;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ObjectException;
use Bitrix\Main\Text\Encoding;

class SenderEditInvitation extends SenderInvitation
{
	public const METHOD = 'edit';
	public const DECISION_CHANGE = 'C';
	/**
	 * @var array
	 */
	private $changeFields;


	/**
	 *
	 */
	public function executeAfterSuccessfulInvitation(): void
	{
	}

	/**
	 * @return array|array[]
	 * @throws ObjectException
	 */
	protected function getContent(): array
	{
		$attachmentManager = new AttachmentEditManager($this->event);
		$this->uid = $attachmentManager->getUid();

		if (!$this->uid)
		{
			return [];
		}

		return [[
			'CONTENT' => Encoding::convertEncoding($attachmentManager->getContent(), SITE_CHARSET, "utf-8"),
			'CONTENT_TYPE' => self::CONTENT_TYPE,
			'METHOD' => mb_strtolower(self::METHOD),
			'CHARSET' => self::CHARSET,
			'NAME' => self::ATTACHMENT_NAME,
			'ID' => Helper::getUniqId(),
		]];
	}

	/**
	 * @return array
	 * @throws ObjectException
	 */
	protected function getMailEventField(): array
	{
		return [
			"=Reply-To" => "{$this->context->getAddresser()->getFullName()} <{$this->context->getAddresser()->getEmail()}>",
			"=From" => "{$this->context->getAddresser()->getFullName()} <{$this->context->getAddresser()->getEmail()}>",
			"=Message-Id" => $this->getMessageId(),
			"=In-Reply-To" => $this->getMessageReplyTo(),
			'EMAIL_FROM' => $this->context->getAddresser()->getEmail(),
			'EMAIL_TO' => $this->context->getReceiver()->getEmail(),
			'MESSAGE_SUBJECT' => $this->getSubjectMessage(),
			'MESSAGE_PHP' => $this->getBodyMessage(),
			'CONFIRM_CODE' => 'TRUE',
			'NAME' => $this->event['NAME'],
			'METHOD' => self::METHOD,
			'CHANGE_FIELDS_TITLE' => $this->getChangeFieldsTitle(),
			'DETAIL_LINK' => Helper::getDetailLink(
				$this->getEventId(),
				$this->getEventOwnerId(),
				$this->getEventDateCreateTimestamp()
			),
			'DECISION_YES_LINK' => Helper::getPubEventLinkWithParameters(
				$this->getEventId(),
				$this->getEventOwnerId(),
				$this->getEventDateCreateTimestamp(),
				self::DECISION_YES
			),
			'DECISION_NO_LINK' => Helper::getPubEventLinkWithParameters(
				$this->getEventId(),
				$this->getEventOwnerId(),
				$this->getEventDateCreateTimestamp(),
				self::DECISION_NO
			),
			'CHANGE_DECISION_LINK' => Helper::getPubEventLinkWithParameters(
				$this->getEventId(),
				$this->getEventOwnerId(),
				$this->getEventDateCreateTimestamp(),
				self::DECISION_CHANGE
			),
			'REQUEST_DECISION' => $this->event['MEETING']['REINVITE'] ? 'Y' : 'N',
			'DATE_FROM' => $this->event['DATE_FROM'],
			'DATE_TO' => $this->event['DATE_TO'],
			'TZ_FROM' => $this->event['TZ_FROM'],
			'TZ_TO' => $this->event['TZ_TO'],
			'FULL_DAY' => $this->event['SKIP_TIME'] ? 'Y' : 'N',
			'CHANGE_FIELDS' => $this->getChangeFieldsString(),
		];
	}

	/**
	 * @return string
	 */
	protected function getMessageReplyTo(): string
	{
		return $this->getMessageId();
	}

	/**
	 * @return string
	 */
	protected function getChangeFieldsTitle(): string
	{
		$fields = $this->context->getChangeFields();
		if (count($fields) === 1)
		{
			switch ($fields[0]['fieldKey'])
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

	/**
	 * @return string
	 */
	protected function getSubjectTitle(): string
	{
		return Loc::getMessage("EC_CALENDAR_ICAL_MAIL_METHOD_EDIT") . ": {$this->event['NAME']}";
	}

	protected function getChangeFieldsString(): string
	{
		$res = [];
		if (count($this->context->getChangeFields()) > 0)
		{
			foreach ($this->context->getChangeFields() as $changeField)
			{
				$res[] = $changeField['fieldKey'];
			}
		}
		return implode(';', $res);
	}
}
