<?php

namespace Bitrix\Sender\Integration\Sender\Mail;

use Bitrix\Sender\Consent\ConsentMessageBuilderFactory;
use Bitrix\Sender\Transport\iBase;

final class TestConsentResponseMail extends ConsentResponseMail
{
	const CODE = iBase::CODE_MAIL.ConsentMessageBuilderFactory::TEST_POSTFIX;
	private $fields;

	/**
	 * @param $apply
	 * @return bool
	 */
	public function updateContact($apply): bool
	{
		return $apply === true;
	}

	public function reject()
	{
		return true;
	}
}
