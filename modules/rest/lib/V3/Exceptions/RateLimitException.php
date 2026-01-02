<?php

namespace Bitrix\Rest\V3\Exceptions;

class RateLimitException extends RestException
{
	protected const STATUS = \CRestServer::STATUS_TO_MANY_REQUESTS;

	protected function getMessagePhraseCode(): string
	{
		return 'REST_RATE_LIMIT_EXCEPTION';
	}
}
