<?php

namespace Bitrix\Rest\V3\Exceptions\Internal;

use Bitrix\Rest\V3\Exceptions\RestException;
use Throwable;

/**
 * Showed to user as internal error without details.
 * In turn, details are available in the original Exception passed through the constructor.
 */
class InternalException extends RestException
{
	/**
	 * @param Throwable $original Real internal exception for debug
	 */
	public function __construct(Throwable $original, string $status = \CRestServer::STATUS_INTERNAL)
	{
		parent::__construct($original, $status);
	}

	protected function getMessagePhraseCode(): string
	{
		return 'REST_INTERNAL_EXCEPTION';
	}
}
