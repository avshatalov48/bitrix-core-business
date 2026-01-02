<?php

namespace Bitrix\Rest\V3\Exceptions\Validation;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\LocalizableMessage;

class RequiredFieldInRequestException extends RequestValidationException
{
	public function __construct(string $field)
	{
		$message = new LocalizableMessage(
			'REST_REQUIRED_FIELD_IN_REQUEST_EXCEPTION', ['#FIELD#' => $field],
		);

		parent::__construct([new Error($message, $field)]);
	}
}
