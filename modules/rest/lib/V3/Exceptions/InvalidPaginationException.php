<?php

namespace Bitrix\Rest\V3\Exceptions;

class InvalidPaginationException extends RestException
{
	public function __construct(
		protected mixed $page,
	) {
		parent::__construct();
	}

	protected function getMessagePhraseCode(): string
	{
		return 'REST_INVALID_PAGINATION_EXCEPTION';
	}

	protected function getMessagePhraseReplacement(): ?array
	{
		return [
			'#PAGE#' => is_string($this->page) ? $this->page : json_encode($this->page),
		];
	}
}
