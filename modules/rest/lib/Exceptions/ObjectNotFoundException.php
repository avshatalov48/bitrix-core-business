<?php

declare(strict_types=1);

namespace Bitrix\Rest\Exceptions;

use Bitrix\Rest\RestException;

final class ObjectNotFoundException extends RestException
{
	public function __construct(string $message = '', \Exception $previous = null)
	{
		parent::__construct($message, 510,
			\CRestServer::STATUS_WRONG_REQUEST,
			$previous
		);
	}
}