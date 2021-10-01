<?php

namespace Bitrix\Sender\Integration\Sender\Mail;

use Bitrix\Sender\Consent\ConsentMessageBuilderFactory;
use Bitrix\Sender\Transport\iBase;

class TestConsentBuilderMail extends ConsentBuilderMail
{
	const CODE = iBase::CODE_MAIL.ConsentMessageBuilderFactory::TEST_POSTFIX;
	const REQUIRED_FIELDS = ['CONTACT_CODE','SITE_ID'];
	
	protected static function buildLink($fields, $siteId, $type): string
	{
		return parent::buildLink(array_filter($fields),$siteId, $type).'&test=y';
	}
}