<?php

namespace Bitrix\Sender\Service;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sender\GroupTable;
use Bitrix\Sender\Internals\Model\GroupQueueTable;
use Bitrix\Sender\Posting\Locker;

class GroupQueueService implements GroupQueueServiceInterface
{
	private const LOCK_KEY = 'group_queue';
	private const LIFETIME = 9800;
	
	/**
	 * Add current process to database
	 * @param int $type
	 * @param int $entityId
	 * @param int $groupId
	 * @throws \Exception
	 */
	public function addToDB(int $type, int $entityId, int $groupId)
	{
		if (!in_array($type, GroupQueueTable::TYPE))
		{
			return;
		}
		if (!Locker::lock(self::LOCK_KEY, $entityId))
		{
			return;
		}
		
		$current = $this->getCurrentRow($type, $entityId, $groupId);
		if (isset($current['ID']) || isset($current[0]['ID']))
		{
			Locker::unlock(self::LOCK_KEY, $entityId);
			return;
		}

		GroupQueueTable::add([
			'TYPE' => $type,
			'ENTITY_ID' => $entityId,
			'GROUP_ID' => $groupId,
			'DATE_INSERT' => (new DateTime())->disableUserTime(),
		]);

		Locker::unlock(self::LOCK_KEY, $entityId);
	}
	
	/**
	 * release current process by entity and type
	 * @param int $type
	 * @param int $entityId
	 * @param int $groupId
	 * @throws \Exception
	 */
	public function releaseGroup(int $type, int $entityId, int $groupId)
	{
		$current = $this->getCurrentRow($type, $entityId, $groupId)[0];
		if (!isset($current['ID']))
		{
			return;
		}
		
		GroupQueueTable::delete($current['ID']);
		
		$this->isReleased($groupId);
	}
	
	/**
	 * check that group is released
	 * @param int $groupId
	 * @return bool
	 * @throws ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function isReleased(int $groupId): bool
	{
		$dateTime = (new DateTime())
			->disableUserTime()
			->getTimestamp();

		$entitiesToDelete = GroupQueueTable::getList([
			'select' => ['ID', 'DATE_INSERT'],
			'filter' => [
				'GROUP_ID' => $groupId,
				[
					'LOGIC' => 'OR',
					['DATE_INSERT' => false],
					['<DATE_INSERT' => DateTime::createFromTimestamp($dateTime - self::LIFETIME)],
				],
			],
		])->fetchAll();

		foreach ($entitiesToDelete as $entity)
		{
			try
			{
				GroupQueueTable::delete($entity['ID']);
			}
			catch (\Exception $e)
			{}
		}

		$remainingRecords = GroupQueueTable::query()
			->setSelect(['ID'])
			->where('GROUP_ID', $groupId)
			->exec()
			->fetch()
		;

		if (!$remainingRecords)
		{
			GroupTable::update(
				$groupId,
				[
					'fields' => ['STATUS' => GroupTable::STATUS_DONE],
				],
			);
		}

		return !$remainingRecords;
	}
	
	private function getCurrentRow(int $type, int $entityId, int $groupId): array
	{
		return GroupQueueTable::query()
			->setSelect(['ID'])
			->where('TYPE', $type)
			->where('ENTITY_ID', $entityId)
			->where('GROUP_ID', $groupId)
			->exec()
			->fetchAll();
	}

	public function isEntityProcessed(int $type, int $entityId)
	{
		return GroupQueueTable::query()
			->setSelect([
				'ID',
				'DATE_INSERT',
			])
			->where('ENTITY_ID', $entityId)
			->where('TYPE', $type)
			->exec()
			->fetch();
	}
}