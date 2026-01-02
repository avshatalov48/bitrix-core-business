<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Exception;

/**
 * Base synchronization exception
 */
interface ExceptionInterface extends \Throwable
{
	public function isPublic(): bool;
}
