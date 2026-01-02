<?php

namespace Bitrix\Rest\V3\Exceptions\Validation;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\LocalizableMessage;

class InvalidRequestFieldTypeException extends RequestValidationException
{
	public function __construct(string $field, string $type)
	{
		$message = new LocalizableMessage(
			'REST_INVALID_REQUEST_FIELD_TYPE_EXCEPTION', [
				'#FIELD#' => $field,
				'#TYPE#' => class_exists($type) ? (new \ReflectionClass($type))->getShortName() : $type,
			],
		);
		parent::__construct([new Error($message, $field)]);
	}
}
