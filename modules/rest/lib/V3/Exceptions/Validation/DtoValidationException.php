<?php

namespace Bitrix\Rest\V3\Exceptions\Validation;


class DtoValidationException extends ValidationException
{
	protected function getMessagePhraseCode(): string
	{
		return 'REST_DTO_VALIDATION_EXCEPTION';
	}
}
