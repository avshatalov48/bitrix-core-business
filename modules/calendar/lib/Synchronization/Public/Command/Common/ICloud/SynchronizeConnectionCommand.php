<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Public\Command\Common\ICloud;

use Bitrix\Calendar\Synchronization\Internal\Exception\ErrorBuilder;
use Bitrix\Calendar\Synchronization\Internal\Exception\Exception;
use Bitrix\Main\Command\AbstractCommand;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Result;
use Bitrix\Main\SystemException;

class SynchronizeConnectionCommand extends AbstractCommand
{
	private SynchronizeConnectionCommandHandler $handler;

	public function __construct(public readonly int $userId, public readonly int $connectionId)
	{
		/** @noinspection PhpUnhandledExceptionInspection */
		$this->handler = ServiceLocator::getInstance()->get(SynchronizeConnectionCommandHandler::class);
	}

	protected function execute(): Result
	{
		$result = new Result();

		try
		{
			if ($this->handler->__invoke($this))
			{
				$result->setData(
					[
						'status' => 'success',
					]
				);

				return $result;
			}
		}
		catch (Exception|SystemException $e)
		{
			$result->addError(ErrorBuilder::buildFromException($e));
		}

		return $result;
	}

	public function toArray(): array
	{
		return [
			'userId' => $this->userId,
			'connectionId' => $this->connectionId,
		];
	}
}
