<?php

declare(strict_types=1);

namespace Bitrix\Main\Command;

use Bitrix\Main\Command\Exception\CommandException;
use Bitrix\Main\Command\Exception\CommandValidationException;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Result;
use Bitrix\Main\Validation\ValidationResult;

abstract class AbstractCommand implements CommandInterface
{
	/**
	 * {@inheritDoc}
	 */
	final public function run(): Result
	{
		$validationResult = $this->validate();
		if (!$validationResult->isSuccess())
		{
			throw new CommandValidationException($validationResult->getErrors());
		}

		if ($errorResult = $this->beforeRun())
		{
			return $errorResult;
		}

		try
		{
			$result = $this->execute();
		}
		catch (\Exception $e)
		{
			throw new CommandException(
				$this,
				sprintf('Command has unprocessed exception: "%s". Code: "%s"', $e->getMessage(), $e->getCode()),
				previous: $e
			);
		}

		$this->afterRun();

		return $result;
	}

	abstract protected function execute(): Result;

	protected function validate(): ValidationResult
	{
		return ServiceLocator::getInstance()->get('main.validation.service')->validate($this);
	}

	/**
	 * If the method returns a value then the command should not be executed
	 */
	protected function beforeRun(): ?Result
	{
		return null;
	}

	protected function afterRun(): void
	{
	}
}
