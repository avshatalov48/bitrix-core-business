<?php

namespace Bitrix\Im\V2\Controller;

use Bitrix\Im\V2\Error;
use Bitrix\Im\V2\Sync\SyncService;
use Bitrix\Main\ObjectException;
use Bitrix\Main\Type\DateTime;
use DateTimeInterface;

class Sync extends BaseController
{
	private const LIMIT = 50;

	private const DATE_FORMAT = DateTimeInterface::RFC3339;

	public function listAction(string $lastDate, ?int $lastId = null, int $limit = self::LIMIT): ?array
	{
		if ($limit <= 0 || $limit > 10000)
		{
			$limit = self::LIMIT;
		}

		try
		{
			$lastDate = new DateTime($lastDate, self::DATE_FORMAT);
		}
		catch (ObjectException $exception)
		{
			$this->addError(new Error($exception->getCode(), $exception->getMessage()));

			return null;
		}

		return (new SyncService())->getChangesFromDate($lastDate, $lastId, $limit);
	}
}
