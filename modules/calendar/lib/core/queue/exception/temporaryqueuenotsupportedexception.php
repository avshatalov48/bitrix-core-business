<?php
namespace Bitrix\Calendar\Core\Queue\Exception;

use Throwable;

class TemporaryQueueNotSupportedException extends Exception
{
	/**
	 * @param int $code
	 * @param Throwable|null $previous
	 *
	 * @return static
	 */
    public static function providerDoestNotSupportIt(int $code = 0, Throwable $previous = null): self
    {
        return new static('The provider does not support temporary queue feature', $code, $previous);
    }
}
