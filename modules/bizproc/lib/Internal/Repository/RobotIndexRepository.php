<?php

namespace Bitrix\Bizproc\Internal\Repository;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\Date;
use Bitrix\Bizproc\Internal\Model\RobotVersionIndexTable;

class RobotIndexRepository
{
	private const OLD_ROBOTS_DATE_OFFSET = '1 year';

	/**
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function fetchRobotsChangedSinceThreshold(Date $threshold): array
	{
		return RobotVersionIndexTable::query()
			->setSelect(['ID', 'ROBOT_CODE', 'VERSION', 'DATE_CHANGED'])
			->where('DATE_CHANGED', '>=', $threshold)
			->setOrder(['DATE_CHANGED' => 'DESC'])
			->fetchAll();
	}

	private static function getDateForNewRobots(): Date
	{
		return new Date();
	}

	private static function getDateForOldRobots(): Date
	{
		return (new Date())->add('- ' . self::OLD_ROBOTS_DATE_OFFSET);
	}

	/**
	 * @param array<array{ROBOT_CODE: string, VERSION: int}> $robotList
	 * @param int $batchSize
	 * @param bool $ignoreOrmEvents
	 */
	public function upsertRobotVersions(array $robotList, int $batchSize = 100, bool $ignoreOrmEvents = true): void
	{
		if (empty($robotList))
		{
			return;
		}

		$allCodes = array_column($robotList, 'ROBOT_CODE');
		$existingMap = [];
		if (!empty($allCodes))
		{
			$existingRecords = RobotVersionIndexTable::getList([
				'select' => ['ID', 'ROBOT_CODE', 'VERSION'],
				'filter' => ['@ROBOT_CODE' => $allCodes],
			])->fetchAll();

			foreach ($existingRecords as $record)
			{
				$existingMap[$record['ROBOT_CODE']] = $record;
			}
		}

		foreach (array_chunk($robotList, $batchSize) as $batch)
		{
			[$toInsert, $toUpdate] = $this->prepareBatchDataForOrm($batch, $existingMap);

			if (!empty($toInsert))
			{
				RobotVersionIndexTable::addMulti($toInsert, $ignoreOrmEvents);
			}

			if (!empty($toUpdate))
			{
				$groupedUpdates = [];
				foreach ($toUpdate as $updateItem)
				{
					$id = $updateItem['ID'];
					$version = $updateItem['VERSION'];
					$dateChanged = $updateItem['DATE_CHANGED'];
					$groupKey = $version . '|' . $dateChanged->format('Y-m-d');

					if (!isset($groupedUpdates[$groupKey]))
					{
						$groupedUpdates[$groupKey] = [
							'ids' => [],
							'data' => [
								'VERSION' => $version,
								'DATE_CHANGED' => $dateChanged,
							]
						];
					}
					$groupedUpdates[$groupKey]['ids'][] = $id;
				}

				foreach ($groupedUpdates as $group)
				{
					$idsToUpdate = $group['ids'];
					$updateFields = $group['data'];
					RobotVersionIndexTable::updateMulti($idsToUpdate, $updateFields, $ignoreOrmEvents);
				}
			}
		}
	}

	/**
	 * @param array $batch
	 * @param array $existingMap
	 * @return array [toInsert, toUpdate]
	 */
	private function prepareBatchDataForOrm(array $batch, array $existingMap): array
	{
		$toInsert = [];
		$toUpdate = [];

		$dateForNewRobots = self::getDateForNewRobots();
		$dateForOldRobots = self::getDateForOldRobots();

		foreach ($batch as $item)
		{
			$code = $item['ROBOT_CODE'];
			$version = (int)$item['VERSION'];
			$dateChanged = $dateForNewRobots;

			if (isset($existingMap[$code]))
			{
				$existingRecord = $existingMap[$code];
				if ($version > (int)$existingRecord['VERSION'])
				{
					$toUpdate[] = [
						'ID' => (int)$existingRecord['ID'],
						'VERSION' => $version,
						'DATE_CHANGED' => $dateChanged,
					];
				}
			}
			else
			{
				if ($version === 0)
				{
					$dateChanged = $dateForOldRobots;
				}
				$toInsert[] = [
					'ROBOT_CODE' => $code,
					'VERSION' => $version,
					'DATE_CHANGED' => $dateChanged,
				];
			}
		}

		return [$toInsert, $toUpdate];
	}
}
