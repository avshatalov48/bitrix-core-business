<?php

namespace Bitrix\Rest\V3\Exceptions;

use CRestServer;

class MethodNotFoundException extends RestException
{
	protected const STATUS = CRestServer::STATUS_NOT_FOUND;

	public function __construct(protected string $method)
	{
		parent::__construct();
	}

	protected function getMessagePhraseCode(): string
	{
		return 'REST_METHOD_NOT_FOUND_EXCEPTION';
	}

	protected function getMessagePhraseReplacement(): ?array
	{
		return [
			'#METHOD#' => $this->method,
		];
	}
}
