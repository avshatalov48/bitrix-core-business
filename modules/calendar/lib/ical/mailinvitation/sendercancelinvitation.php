<?php

namespace Bitrix\Calendar\ICal\MailInvitation;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ObjectException;
use Bitrix\Main\Text\Encoding;

class SenderCancelInvitation extends SenderInvitation
{
	public const METHOD = 'cancel';

	/**
	 * @return array|array[]
	 * @throws ObjectException
	 */
	protected function getContent(): array
	{
		$attachmentManager = new AttachmentCancelManager($this->event);
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
			'LOC_MEETING_STATUS' => Loc::getMessage('EC_CALENDAR_ICAL_MAIL_METHOD_CANCEL'),
			'STATUS' => 'event_cancelled',
		];
	}

	/**
	 * @return string
	 */
	protected function getSubjectTitle(): string
	{
		if (Loader::includeModule('bitrix24') && \CBitrix24::isFreeLicense())
		{
			return Loc::getMessage("EC_CALENDAR_ICAL_MAIL_METHOD_CANCEL");
		}

		return Loc::getMessage("EC_CALENDAR_ICAL_MAIL_METHOD_CANCEL") . ": {$this->event['NAME']}";
	}

	public function executeAfterSuccessfulInvitation()
	{
	}
}
