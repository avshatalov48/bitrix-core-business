<?php

namespace Bitrix\Im\V2\Sync;

use Bitrix\Im\Model\EO_Log_Collection;
use Bitrix\Im\Model\LogTable;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Sync\Entity\EntityFactory;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Type\DateTime;

class SyncService
{
	use ContextCustomer;

	private const OFFSET_INTERVAL_IN_SECONDS = 5;
	private const MODULE_ID = 'im';
	private const ENABLE_OPTION_NAME = 'sync_logger_enable';

	public static function isEnable(): bool
	{
		return Option::get(self::MODULE_ID, self::ENABLE_OPTION_NAME, 'Y') === 'Y';
	}

	public function getChangesFromDate(DateTime $date, int $limit): array
	{
		if (!self::isEnable())
		{
			return [];
		}

		$date = $this->getDateWithOffset($date);
		$logCollection = LogTable::query()
			->setSelect(['ID'])
			->where('USER_ID', $this->getContext()->getUserId())
			->where('DATE_CREATE', '>=', $date)
			->setLimit($limit)
			->fetchCollection()
		;
		$logCollection->fill();
		Logger::getInstance()->updateDateDelete($logCollection);

		return $this->formatData($logCollection, $limit);
	}

	public function getChangesFromId(int $id, int $limit): array
	{
		if (!self::isEnable())
		{
			return [];
		}

		$logCollection = LogTable::query()
			->setSelect(['ID'])
			->where('USER_ID', $this->getContext()->getUserId())
			->where('ID', '>', $id)
			->setLimit($limit)
			->fetchCollection()
		;
		$logCollection->fill();
		Logger::getInstance()->updateDateDelete($logCollection);

		return $this->formatData($logCollection, $limit);
	}

	private function getDateWithOffset(DateTime $date): DateTime
	{
		$offset = self::OFFSET_INTERVAL_IN_SECONDS;
		$date->add("- {$offset} seconds");

		return $date;
	}

	/**
	 * @param EO_Log_Collection $logCollection
	 * @param int $limit
	 * @return array
	 */
	private function formatData(EO_Log_Collection $logCollection, int $limit): array
	{
		$entities = (new EntityFactory())->createEntities(Event::initByOrmEntities($logCollection));
		$data = [];

		foreach ($entities as $entity)
		{
			foreach ($entity->getData() as $name => $datum)
			{
				$data[$name] = $datum;
			}
		}

		$data['hasMore'] = $logCollection->count() >= $limit;
		$ids = $logCollection->getIdList();
		if (!empty($ids))
		{
			$data['lastId'] = max($ids);
		}

		return $data;
	}
}