<?php

declare(strict_types=1);

namespace Bitrix\Rest\Exceptions;

use Bitrix\Rest\RestException;

final class InvalidOperationException extends RestException
{
	public function __construct(string $message = '', \Exception $previous = null)
	{
		parent::__construct($message, 160,
			\CRestServer::STATUS_WRONG_REQUEST,
			$previous
		);
	}
}