<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Public\Command\Common\Office365;

use Bitrix\Calendar\Synchronization\Internal\Exception\ErrorBuilder;
use Bitrix\Calendar\Synchronization\Internal\Exception\Exception;
use Bitrix\Main\Command\AbstractCommand;
use Bitrix\Main\Result;

class SynchronizeConnectionCommand extends AbstractCommand
{
	public function __construct(public readonly int $userId)
	{
	}

	protected function execute(): Result
	{
		$result = new Result();

		try
		{
			(new SynchronizeConnectionCommandHandler())($this);
		}
		catch (Exception $e)
		{
			$result->addError(ErrorBuilder::buildFromException($e));
		}

		return $result;
	}
}
