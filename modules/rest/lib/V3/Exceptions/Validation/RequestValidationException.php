<?php

namespace Bitrix\Rest\V3\Exceptions\Validation;

abstract class RequestValidationException extends ValidationException
{
	protected function getMessagePhraseCode(): string
	{
		return 'REST_REQUEST_VALIDATION_EXCEPTION';
	}

	protected function getClassWithPhrase(): string
	{
		return self::class;
	}
}
