<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Exception\Vendor;

use Bitrix\Calendar\Synchronization\Internal\Exception\ApiException;

/**
 * An exception that occurs when the vendor's API return code 400.
 */
class BadRequestException extends ApiException
{
	public function __construct(string $message = 'Bad request', int $code = 400, \Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}
}
