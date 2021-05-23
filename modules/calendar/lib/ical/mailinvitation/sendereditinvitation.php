<?php


namespace Bitrix\Calendar\ICal\MailInvitation;

use Bitrix\Calendar\Internals\EventTable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ORM\Data\UpdateResult;
use Bitrix\Main\Text\Encoding;
use Exception;

class SenderEditInvitation extends SenderInvitation
{
	public const METHOD = 'edit';
	/**
	 * @var array
	 */
	private $changeFields;

	/**
	 * @return bool
	 * @throws Exception
	 */
	public function executeAfterSuccessfulInvitation(): bool
	{
		$result = EventTable::update(
			$this->getEventId(),
			[
				'MEETING_STATUS' => 'Q',
			]
		);

		return ($result instanceof UpdateResult)
			? $result->isSuccess()
			: false;
	}

	/**
	 * @return array[]
	 * @throws ObjectException
	 */
	protected function getContent(): array
	{
		$attachmentManager = new AttachmentEditManager($this->event);
		$this->uid = $attachmentManager->getUid();

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
			'DATE_FROM' => $this->getDateForTemplate(),
			'NAME' => $this->event['NAME'],
			'DESCRIPTION' => $this->event['DESCRIPTION'],
			'ATTENDEES' => $this->getAttendeesListForTemplate(),
			'ORGANIZER' => $this->context->getAddresser()->getFullNameWithEmail(),
			'LOCATION' => $this->event['TEXT_LOCATION'],
			'FILES_LINK' =>$this->getFilesLink(),
			'METHOD' => SenderRequestInvitation::METHOD,
			'TITLE' => $this->getTemplateTitle(),
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
	protected function getTemplateTitle(): string
	{
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