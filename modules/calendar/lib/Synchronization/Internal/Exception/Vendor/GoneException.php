<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Exception\Vendor;

use Bitrix\Calendar\Synchronization\Internal\Exception\ApiException;

/**
 * An exception that occurs when the vendor's API return 410 code.
 */
class GoneException extends ApiException
{
	public function __construct(string $message = '', int $code = 410, \Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}
}
