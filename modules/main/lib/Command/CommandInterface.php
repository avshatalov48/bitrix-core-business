<?php

declare(strict_types=1);

namespace Bitrix\Main\Command;

use Bitrix\Main\Command\Exception\CommandException;
use Bitrix\Main\Command\Exception\CommandValidationException;
use Bitrix\Main\Result;

interface CommandInterface
{
	/**
	 * @throws CommandException
	 * @throws CommandValidationException
	 */
	public function run(): Result;
}
