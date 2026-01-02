<?php

declare(strict_types=1);

namespace Bitrix\Main\Command\Exception;

use Bitrix\Main\Command\CommandInterface;
use Bitrix\Main\SystemException;

class CommandException extends SystemException
{
	public function __construct(
		public readonly CommandInterface $command,
		$message = '',
		\Throwable $previous = null
	)
	{
		parent::__construct($message, previous: $previous);
	}
}
