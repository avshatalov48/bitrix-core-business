<?php

namespace Bitrix\Rest\V3\Exceptions;

class UnknownDtoPropertyException extends RestException
{
	public function __construct(
		public string $dtoClass,
		public string $propertyName,
	) {
		return parent::__construct();
	}

	protected function getMessagePhraseCode(): string
	{
		return 'REST_UNKNOWN_DTO_PROPERTY_EXCEPTION';
	}

	protected function getMessagePhraseReplacement(): ?array
	{
		return [
			'#DTO#' => (new \ReflectionClass($this->dtoClass))->getShortName(),
			'#FIELD#' => $this->propertyName,
		];
	}
}
