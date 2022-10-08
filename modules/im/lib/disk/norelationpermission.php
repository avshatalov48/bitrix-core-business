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
		if (!\Bitrix\Main\ModuleManager::isModuleInstalled('disk'))
		{
			return __METHOD__.'();';
		}

		$connection = \Bitrix\Main\Application::getInstance()->getConnection();

		$relationTbl = RelationTable::getTableName();
		$noRelationPermTbl = NoRelationPermissionDiskTable::getTableName();

		$connection->queryExecute("
			DELETE nrd
			FROM 
				{$noRelationPermTbl} nrd
				inner join {$relationTbl} r 
					on r.CHAT_ID = nrd.CHAT_ID and r.USER_ID = nrd.USER_ID
		");

		$result = NoRelationPermissionDiskTable::getList([
			'select' => ['CHAT_ID', 'USER_ID'],
			'filter' => [
				'<=ACTIVE_TO' => DateTime::createFromTimestamp(time())
			]
		]);
		$pseudoRelation = [];
		while($row = $result->fetch())
		{
			$pseudoRelation[$row['CHAT_ID']][$row['USER_ID']] = $row['USER_ID'];
		}

		$connection->queryExecute("DELETE FROM {$noRelationPermTbl} WHERE ACTIVE_TO <= now()");

		foreach ($pseudoRelation as $chatId => $userDelete)
		{
			\CIMDisk::changeFolderMembers($chatId, $userDelete, false);
		}

		return __METHOD__.'();';
	}
}