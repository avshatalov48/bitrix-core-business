<?php

namespace Bitrix\Calendar\ICal\MailInvitation;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ObjectException;
use Bitrix\Main\Text\Encoding;

class SenderEditInvitation extends SenderInvitation
{
	public const METHOD = 'edit';

	public function executeAfterSuccessfulInvitation(): void
	{
	}

	protected function getContent(): array
	{
		$attachmentManager = new AttachmentEditManager($this->event);
		$this->uid = $attachmentManager->getUid();

		if (!$this->uid)
		{
			return [];
		}

		return [[
			'CONTENT' => Encoding::convertEncoding($attachmentManager->getContent(), SITE_CHARSET, 'utf-8'),
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
	protected function getTemplateParams(): array
	{
		return [
			'LOC_MEETING_STATUS' => $this->getChangeFieldsTitle(),
			'STATUS' => 'event',
		];
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
				case 'DESCRIPTION':
					return Loc::getMessage('EC_CALENDAR_ICAL_MAIL_CHANGE_FIELD_TITLE_DESCRIPTION');
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
		if (Loader::includeModule('bitrix24') && \CBitrix24::isFreeLicense())
		{
			return Loc::getMessage("EC_CALENDAR_ICAL_MAIL_METHOD_EDIT");
		}

		return Loc::getMessage("EC_CALENDAR_ICAL_MAIL_METHOD_EDIT") . ": {$this->event['NAME']}";
	}
}
