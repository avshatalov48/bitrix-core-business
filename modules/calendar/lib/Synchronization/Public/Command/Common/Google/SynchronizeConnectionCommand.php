<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Public\Command\Common\Google;

use Bitrix\Calendar\Synchronization\Internal\Exception\ErrorBuilder;
use Bitrix\Calendar\Synchronization\Internal\Exception\Exception;
use Bitrix\Main\Command\AbstractCommand;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Result;

class SynchronizeConnectionCommand extends AbstractCommand
{
	private SynchronizeConnectionCommandHandler $handler;

	public function __construct(public readonly int $userId)
	{
		$this->handler = ServiceLocator::getInstance()->get(SynchronizeConnectionCommandHandler::class);
	}

	protected function execute(): Result
	{
		$result = new Result();

		try
		{
			$this->handler->__invoke($this);
		}
		catch (Exception $e)
		{
			$result->addError(ErrorBuilder::buildFromException($e));
		}

		return $result;
	}
}
