<?php

namespace Bitrix\Rest\V3\Exceptions;

class EntityNotFoundException extends RestException
{
	public function __construct(
		protected int $id,
	) {
		parent::__construct();
	}

	protected function getMessagePhraseCode(): string
	{
		return 'REST_ENTITY_NOT_FOUND';
	}

	protected function getMessagePhraseReplacement(): ?array
	{
		return [
			'#ID#' => $this->id,
		];
	}
}
