<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Public\Command\Common\Google;

use Bitrix\Calendar\Synchronization\Internal\Exception\ErrorBuilder;
use Bitrix\Calendar\Synchronization\Internal\Exception\Exception;
use Bitrix\Main\Command\AbstractCommand;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Result;
use Bitrix\Main\SystemException;

class CreateConnectionCommand extends AbstractCommand
{
	public function __construct(
		public readonly int $userId
	)
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
		catch (Exception|SystemException|LoaderException $e)
		{
			$result->addError(ErrorBuilder::buildFromException($e));
		}

		return $result;
	}
}
