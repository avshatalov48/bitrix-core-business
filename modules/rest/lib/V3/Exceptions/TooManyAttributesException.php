<?php

namespace Bitrix\Rest\V3\Exceptions;

class TooManyAttributesException extends RestException
{
	public function __construct(
		public string $class,
		public string $attribute,
		public int $expectedCount,
	) {
		return parent::__construct();
	}

	protected function getMessagePhraseCode(): string
	{
		return 'REST_TOO_MANY_ATTRIBUTES_EXCEPTION';
	}

	protected function getMessagePhraseReplacement(): ?array
	{
		return [
			'#CLASS#' => (new \ReflectionClass($this->class))->getShortName(),
			'#ATTRIBUTE#' => (new \ReflectionClass($this->attribute))->getShortName(),
			'#EXPECTED#' => $this->expectedCount,
		];
	}
}
