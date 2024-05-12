<?php

namespace Bitrix\Calendar\ICal\MailInvitation;

use Bitrix\Calendar\Internals\EventTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\UpdateResult;
use Bitrix\Main\Text\Encoding;

class SenderRequestInvitation extends SenderInvitation
{
	public const METHOD = 'request';
	protected function getContent(): array
	{
		$attachmentManager = new AttachmentRequestManager($this->event);
		$this->uid = $attachmentManager->getUid();

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
	 * @throws \Bitrix\Main\ObjectException
	 */
	protected function getTemplateParams(): array
	{
		return [
			'LOC_MEETING_STATUS' => Loc::getMessage('EC_CALENDAR_ICAL_MAIL_YOU_WAS_INVITED'),
			'STATUS' => 'event',
		];
	}

	/**
	 * @return string
	 */
	protected function getSubjectTitle(): string
	{
		if (Loader::includeModule('bitrix24') && \CBitrix24::isFreeLicense())
		{
			return Loc::getMessage("EC_CALENDAR_ICAL_MAIL_METHOD_REQUEST");
		}

		return Loc::getMessage("EC_CALENDAR_ICAL_MAIL_METHOD_REQUEST") . ": {$this->event['NAME']}";
	}

	/**
	 * @return bool
	 * @throws \Exception
	 */
	public function executeAfterSuccessfulInvitation(): bool
	{
		$result = EventTable::update(
			$this->getEventId(),
			[
				'DAV_XML_ID' => $this->getUId(),
			]
		);

		return ($result instanceof UpdateResult) && $result->isSuccess();
	}
}
