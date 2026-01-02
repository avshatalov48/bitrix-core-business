<?php

namespace Bitrix\Im\V2\Sync;

use Bitrix\Im\Model\LogTable;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Sync\Entity\EntityFactory;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Type\DateTime;

class SyncService
{
	use ContextCustomer;

	private const MODULE_ID = 'im';
	private const ENABLE_OPTION_NAME = 'sync_logger_enable';

	public static function isEnable(): bool
	{
		return Option::get(self::MODULE_ID, self::ENABLE_OPTION_NAME, 'Y') === 'Y';
	}

	public function getChangesFromDate(DateTime $lastDate, ?int $lastId, int $limit): array
	{
		if (!self::isEnable())
		{
			return [];
		}

		$query = LogTable::query()
			->setSelect(['ID', 'USER_ID', 'ENTITY_TYPE', 'ENTITY_ID', 'EVENT', 'DATE_CREATE'])
			->where('USER_ID', $this->getContext()->getUserId())
			->setLimit($limit)
			->setOrder(['USER_ID' => 'ASC','DATE_CREATE' => 'ASC', 'ID' => 'ASC'])
		;

		if (isset($lastId))
		{
			$filter = [[
					'LOGIC' => 'OR',
					[
						'>DATE_CREATE' => $lastDate,
					],
					[
						'=DATE_CREATE' => $lastDate,
						'>=ID' => $lastId,
					],
			]];
			$query->setFilter($filter);
		}
		else
		{
			$query->where('DATE_CREATE', '>=', $lastDate);
		}

		return $this->formatData($query->fetchAll(), $limit);
	}

	private function formatData(array $logEntities, int $limit): array
	{
		$entities = (new EntityFactory())->createEntities(Event::initByArray($logEntities));
		$rest = $entities->getRestData();
		$rest['navigationData'] = $this->getNavigationData($logEntities, $limit);

		return $rest;
	}

	protected function getNavigationData(array $logEntities, int $limit): array
	{
		$maxDateTime = null;
		$maxTimestamp = 0;
		$lastId = 0;
		foreach ($logEntities as $logEntity)
		{
			$dateCreate = $logEntity['DATE_CREATE'];
			$entityId = (int)$logEntity['ID'];

			if (!$dateCreate instanceof DateTime)
			{
				continue;
			}

			if ($dateCreate->getTimestamp() > $maxTimestamp)
			{
				$maxTimestamp = $dateCreate->getTimestamp();
				$maxDateTime = $dateCreate;
				$lastId = $entityId;
			}
			elseif ($dateCreate->getTimestamp() === $maxTimestamp && $entityId > $lastId)
			{
				$lastId = $entityId;
			}
		}

		return [
			'lastServerDate' => $maxDateTime,
			'hasMore' => count($logEntities) >= $limit,
			'lastId' => $lastId,
		];
	}
}
