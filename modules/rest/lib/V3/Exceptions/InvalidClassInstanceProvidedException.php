<?php

namespace Bitrix\Rest\V3\Exceptions;

class InvalidClassInstanceProvidedException extends RestException
{
	public function __construct(
		public string $provided,
		public string $required,
	) {
		return parent::__construct();
	}

	protected function getMessagePhraseCode(): string
	{
		return 'REST_INVALID_CLASS_INSTANCE_PROVIDED_EXCEPTION';
	}

	protected function getMessagePhraseReplacement(): ?array
	{
		return [
			'#PROVIDED#' => (new \ReflectionClass($this->provided))->getShortName(),
			'#REQUIRED#' => (new \ReflectionClass($this->required))->getShortName(),
		];
	}
}
