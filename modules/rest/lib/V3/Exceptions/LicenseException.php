<?php

namespace Bitrix\Rest\V3\Exceptions;

class LicenseException extends RestException
{
	protected function getMessagePhraseCode(): string
	{
		return 'REST_LICENSE_EXCEPTION';
	}
}
