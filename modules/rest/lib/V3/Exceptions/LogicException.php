<?php

namespace Bitrix\Rest\V3\Exceptions;

class LogicException extends RestException
{
	protected function getMessagePhraseCode(): string
	{
		return 'REST_LOGIC_EXCEPTION';
	}
}
