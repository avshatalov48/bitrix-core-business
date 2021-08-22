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
		Locker::lock(self::LOCK_KEY, $entityId);
		
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
		$entities = GroupQueueTable::query()
			->setSelect([
				'ID',
				'DATE_INSERT',
			])
			->where('GROUP_ID', $groupId)
			->exec()
			->fetchAll();
		
		foreach ($entities as $key =>$entity)
		{
			$dateTime = DateTime::createFromPhp(new \DateTime());
			
			if (!$entity['DATE_INSERT'] || abs($dateTime->getTimestamp() - $entity['DATE_INSERT']->getTimestamp()) > self::LIFETIME)
			{
				try
				{
					GroupQueueTable::delete($entity['ID']);
				} catch (\Exception $e)
				{
				}
				unset($entities[$key]);
			}
		}
		
		if (empty($entities))
		{
			GroupTable::update($groupId, [
				'fields' => ['STATUS' => GroupTable::STATUS_DONE]
			]);
		}
		
		return empty($entities);
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