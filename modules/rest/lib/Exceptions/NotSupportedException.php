<?php

declare(strict_types=1);

namespace Bitrix\Rest\Exceptions;

use Bitrix\Rest\RestException;

final class NotSupportedException extends RestException
{
	public function __construct(string $message = '', \Exception $previous = null)
	{
		parent::__construct($message, 150,
			\CRestServer::STATUS_WRONG_REQUEST,
			$previous
		);
	}
}