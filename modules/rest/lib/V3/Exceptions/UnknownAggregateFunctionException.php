<?php

namespace Bitrix\Rest\V3\Exceptions;

class UnknownAggregateFunctionException extends RestException
{
	public function __construct(
		protected string $function,
	) {
		parent::__construct();
	}

	protected function getMessagePhraseCode(): string
	{
		return 'REST_UNKNOWN_AGGREGATE_FUNCTION_EXCEPTION';
	}

	protected function getMessagePhraseReplacement(): ?array
	{
		return [
			'#FUNCTION#' => $this->function,
		];
	}
}
