<?php
namespace Bitrix\Calendar\Core\Queue\Exception;

use Throwable;

class InvalidRuleException extends Exception
{
	/**
	 * @param int $code
	 * @param Throwable|null $previous
	 *
	 * @return static
	 */
    public static function classIsInvalid(int $code = 0, Throwable $previous = null): self
    {
        return new static('The class is not exists', $code, $previous);
    }

	/**
	 * @param int $code
	 * @param Throwable|null $previous
	 *
	 * @return static
	 */
    public static function classIsNotRule(int $code = 0, Throwable $previous = null): self
    {
        return new static('The class is not rule', $code, $previous);
    }
}
