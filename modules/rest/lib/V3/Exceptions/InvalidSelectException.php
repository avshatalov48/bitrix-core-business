<?php

namespace Bitrix\Rest\V3\Exceptions;

class InvalidSelectException extends RestException
{
	public function __construct(
		protected mixed $select,
	) {
		parent::__construct();
	}

	protected function getMessagePhraseCode(): string
	{
		return 'REST_INVALID_SELECT_EXCEPTION';
	}

	protected function getMessagePhraseReplacement(): ?array
	{
		return [
			'#SELECT#' => is_string($this->select) ? $this->select : json_encode($this->select),
		];
	}
}
