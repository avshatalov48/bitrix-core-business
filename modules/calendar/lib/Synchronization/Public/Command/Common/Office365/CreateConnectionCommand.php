<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Public\Command\Common\Office365;

use Bitrix\Calendar\Synchronization\Internal\Exception\ErrorBuilder;
use Bitrix\Calendar\Synchronization\Internal\Exception\Exception;
use Bitrix\Main\Command\AbstractCommand;
use Bitrix\Main\Result;

class CreateConnectionCommand extends AbstractCommand
{
	public function __construct(public readonly int $userId)
	{
	}

	protected function execute(): Result
	{
		$result = new Result();

		try
		{
			if ($connection = (new CreateConnectionCommandHandler())($this))
			{
				$result->setData(
					[
						'status' => 'success',
						'message' => 'CONNECTION_CREATED',
						'connectionId' => $connection->getId(),
					]
				);

				return $result;
			}
		}
		catch (Exception $e)
		{
			$result->addError(ErrorBuilder::buildFromException($e));

			if ($e->getPrevious())
			{
				$e = $e->getPrevious();
			}

			// @todo for backward compatibility
			$result->setData(
				[
					'status' => 'error',
					'message' => 'Could not finish sync: ' . $e->getMessage()
				]
			);
		}

		return $result;
	}
}
