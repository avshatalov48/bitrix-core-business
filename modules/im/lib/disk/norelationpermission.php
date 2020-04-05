<?php
namespace Bitrix\Im\Disk;

use \Bitrix\Main\Type\DateTime,
	\Bitrix\Im\Model\NoRelationPermissionDiskTable;

use \Bitrix\Im\Model\RelationTable;

class NoRelationPermission
{
	const ACCESS_TIME = 86400;
	const CACHE_TIME = 864000;

	public static function add($chatId, $userId)
	{
		$result =  false;

		$rowRelation = RelationTable::getRow(array(
			'select' => array('ID'),
			'filter' => array(
				'=CHAT_ID' => $chatId,
				'=USER_ID' => $userId
			),
			'cache'=>array('ttl'=>self::CACHE_TIME)
		));
		if(empty($rowRelation))
		{
			if(\CIMDisk::ChangeFolderMembers($chatId, $userId))
			{
				$raw = NoRelationPermissionDiskTable::getList(array(
					'select' => array('ID'),
					'filter' => array('=CHAT_ID' => $chatId, '=USER_ID' => $userId),
					'cache'=>array('ttl'=>self::CACHE_TIME)
				));

				$count = 0;
				while ($row = $raw->fetch())
				{
					$count++;

					if($count>1)
					{
						NoRelationPermissionDiskTable::delete($row['ID']);
					}
					else
					{
						$updateRaw = NoRelationPermissionDiskTable::update($row['ID'], array(
							'ACTIVE_TO' => DateTime::createFromTimestamp(time() + self::ACCESS_TIME)
						));

						if($updateRaw->isSuccess())
							$result = true;
					}
				}

				if($count === 0)
				{
					$addRaw = NoRelationPermissionDiskTable::add(array(
						'CHAT_ID' => $chatId,
						'USER_ID' => $userId,
						'ACTIVE_TO' => DateTime::createFromTimestamp(time() + self::ACCESS_TIME)
					));

					if($addRaw->isSuccess())
						$result = true;
				}
			}
		}

		return $result;
	}

	public static function delete($chatId, $userId, $permissionDisk = true)
	{
		$result =  false;

		if($permissionDisk)
		{
			$rowRelation = RelationTable::getRow(array(
				'select' => array('ID'),
				'filter' => array(
					'=CHAT_ID' => $chatId,
					'=USER_ID' => $userId
				),
				'cache'=>array('ttl'=>self::CACHE_TIME)
			));
			if(empty($rowRelation))
			{
				if(\CIMDisk::ChangeFolderMembers($chatId, $userId, false))
					$result = true;
			}
		}

		$raw = NoRelationPermissionDiskTable::getList(array(
			'select' => array('ID'),
			'filter' => array('=CHAT_ID' => $chatId, '=USER_ID' => $userId),
			'cache'=>array('ttl'=>self::CACHE_TIME)
		));

		while ($row = $raw->fetch())
		{
			if(NoRelationPermissionDiskTable::delete($row['ID'])->isSuccess())
				$result = true;
		}

		return $result;
	}

	public static function cleaningAgent()
	{
		$relation = array();

		$raw = NoRelationPermissionDiskTable::getList(array(
				'select' => array('ID', 'CHAT_ID', 'USER_ID'),
				'filter' => array(
					array('<=ACTIVE_TO' => DateTime::createFromTimestamp(time()))
				),
				'cache'=>array('ttl'=>self::CACHE_TIME)
			));

		$filterRelation = array('LOGIC' => 'OR');
		while($row = $raw->fetch())
		{
			$permissionDisk[$row['CHAT_ID']][$row['USER_ID']] = $row['USER_ID'];
			$deletePermissionDisk[$row['ID']] = $row['ID'];
			$filterRelation[] = array(
				'=CHAT_ID' => $row['CHAT_ID'],
				'=USER_ID' => $row['USER_ID']
			);
		}

		$rawRelation = RelationTable::getList(array(
			'select' => array('CHAT_ID', 'USER_ID'),
			'filter' => $filterRelation,
			'cache'=>array('ttl'=>self::CACHE_TIME)
		));

		while($rowRelation = $rawRelation->fetch())
		{
			$relation[$rowRelation['CHAT_ID']][$rowRelation['USER_ID']] = $rowRelation['USER_ID'];
		}

		if(!empty($deletePermissionDisk))
		{
			foreach ($deletePermissionDisk as $item)
			{
				NoRelationPermissionDiskTable::delete($item);
			}
		}

		if(!empty($permissionDisk))
		{
			foreach ($permissionDisk as $chatId => $userIds)
			{
				$userDelete = array();

				foreach ($userIds as $userId)
				{
					if(empty($relation['$chatId']['$userId']))
						$userDelete[] = $userId;
				}

				if(!empty($userDelete))
				{
					\CIMDisk::ChangeFolderMembers($chatId, $userDelete, false);
				}
			}
		}

		return '\Bitrix\Im\Disk\NoRelationPermission::cleaningAgent();';
	}
}