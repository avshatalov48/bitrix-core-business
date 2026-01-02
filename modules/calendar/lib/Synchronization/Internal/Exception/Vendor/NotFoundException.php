<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Exception\Vendor;

use Bitrix\Calendar\Synchronization\Internal\Exception\ApiException;

class NotFoundException extends ApiException
{
	public function __construct(string $message = '', int $code = 404, \Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}
}
