<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Public\Command\Push\Google;

use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Synchronization\Internal\Exception\ApiException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Exception;
use Bitrix\Calendar\Synchronization\Internal\Exception\DtoValidationException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\NotAuthorizedException;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Command\AbstractCommand;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\Repository\Exception\PersistenceException;
use Bitrix\Main\Result;
use Bitrix\Main\SystemException;

class SubscribeToPushCommand extends AbstractCommand
{
	public function __construct(public readonly Connection $connection)
	{
	}

	/**
	 * @return Result
	 *
	 * @throws ApiException
	 * @throws ArgumentException
	 * @throws DtoValidationException
	 * @throws Exception
	 * @throws NotAuthorizedException
	 * @throws ObjectException
	 * @throws ObjectPropertyException
	 * @throws PersistenceException
	 * @throws SystemException
	 */
	protected function execute(): Result
	{
		$result = new Result();

		(new SubscribeToPushCommandHandler())($this);

		return $result;
	}
}
