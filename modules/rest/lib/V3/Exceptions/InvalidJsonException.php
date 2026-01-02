<?php

namespace Bitrix\Rest\V3\Exceptions;

class InvalidJsonException extends RestException
{
	protected function getMessagePhraseCode(): string
	{
		return 'REST_INVALID_JSON_EXCEPTION';
	}

	protected function getClassWithPhrase(): string
	{
		return self::class;
	}
}
