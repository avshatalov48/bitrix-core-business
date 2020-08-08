<?php

namespace Bitrix\Sender\Access\Map;

use Bitrix\Sender\Access\ActionDictionary;
use Bitrix\Sender\Message\iBase;

class MailingAction
{
	/**
	* legacy action map
	* @return array
	*/
	public static function getMap(): array
	{
		return [
			iBase::CODE_AUDIO_CALL => ActionDictionary::ACTION_MAILING_AUDIO_CALL_EDIT,
			iBase::CODE_CALL => ActionDictionary::ACTION_MAILING_INFO_CALL_EDIT,
			iBase::CODE_MAIL => ActionDictionary::ACTION_MAILING_EMAIL_EDIT,
			iBase::CODE_IM => ActionDictionary::ACTION_MAILING_MESSENGER_EDIT,
			iBase::CODE_SMS => ActionDictionary::ACTION_MAILING_SMS_EDIT
		];
	}
}