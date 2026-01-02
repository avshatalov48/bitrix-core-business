<?php

namespace Bitrix\Rest\V3\Exceptions;

class InvalidFilterException extends RestException
{
	public function __construct(
		protected mixed $filter,
	) {
		parent::__construct();
	}

	protected function getMessagePhraseCode(): string
	{
		return 'REST_INVALID_FILTER_EXCEPTION';
	}

	protected function getMessagePhraseReplacement(): ?array
	{
		return [
			'#FILTER#' => is_string($this->filter) ? $this->filter : json_encode($this->filter),
		];
	}
}
