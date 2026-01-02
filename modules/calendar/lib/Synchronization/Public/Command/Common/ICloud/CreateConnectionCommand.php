<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Public\Command\Common\ICloud;

use Bitrix\Calendar\Synchronization\Internal\Exception\ErrorBuilder;
use Bitrix\Calendar\Synchronization\Internal\Exception\Exception;
use Bitrix\Main\Command\AbstractCommand;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Result;

class CreateConnectionCommand extends AbstractCommand
{
	private CreateConnectionCommandHandler $handler;

	public function __construct(
		public readonly int $userId,
		public readonly string $appleId,
		public readonly string $applicationPassword,
	)
	{
		/** @noinspection PhpUnhandledExceptionInspection */
		$this->handler = ServiceLocator::getInstance()->get(CreateConnectionCommandHandler::class);
	}

	protected function execute(): Result
	{
		$result = new Result();

		try
		{
			if ($connection = $this->handler->__invoke($this))
			{
				$result->setData(
					[
						'status' => 'success',
						'connectionId' => $connection->getId(),
					]
				);

				return $result;
			}
		}
		catch (Exception $e)
		{
			$result->addError(ErrorBuilder::buildFromException($e));
		}

		return $result;
	}

	public function toArray(): array
	{
		return [
			'userId' => $this->userId,
			'appleId' => $this->appleId,
			'applicationPassword' => $this->applicationPassword,
		];
	}
}
