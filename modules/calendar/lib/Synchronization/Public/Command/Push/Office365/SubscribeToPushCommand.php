<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Public\Command\Push\Office365;

use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Command\AbstractCommand;
use Bitrix\Main\ObjectException;
use Bitrix\Main\Result;
use Bitrix\Main\SystemException;

class SubscribeToPushCommand extends AbstractCommand
{
	public function __construct(public readonly Connection $connection)
	{
	}

	/**
	 * @throws ArgumentException
	 * @throws ObjectException
	 * @throws SystemException
	 */
	protected function execute(): Result
	{
		$result = new Result();

		(new SubscribeToPushCommandHandler())($this);

		return $result;
	}
}
