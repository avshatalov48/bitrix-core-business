<?php

namespace Bitrix\Rest\V3\Exceptions;

class AccessDeniedException extends RestException
{
	protected const STATUS = \CRestServer::STATUS_FORBIDDEN;

	protected function getMessagePhraseCode(): string
	{
		return 'REST_ACCESS_DENIED';
	}
}
