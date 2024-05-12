<?php
namespace Bitrix\Im\Disk;

use Bitrix\Im\Access\ChatAuthProvider;
use \Bitrix\Main\Type\DateTime,
	\Bitrix\Im\Model\NoRelationPermissionDiskTable;

use \Bitrix\Im\Model\RelationTable;

class NoRelationPermission
{
	const ACCESS_TIME = 86400;

	public static function add($chatId, $userId)
	{
		$result =  false;

		$rowRelation = RelationTable::getRow([
			'select' => ['ID'],
			'filter' => [
				'=CHAT_ID' => $chatId,
				'=USER_ID' => $userId
			],
		]);
		if (empty($rowRelation))
		{
			$provider = new ChatAuthProvider();
			if ($provider->isCodeAlreadyExists($chatId, $userId))
			{
				return $result;
			}

			if(\CIMDisk::ChangeFolderMembers($chatId, $userId))
			{
				$raw = NoRelationPermissionDiskTable::getList([
					'select' => ['ID'],
					'filter' => [
						'=CHAT_ID' => $chatId,
						'=USER_ID' => $userId
					],
				]);

				$count = 0;
				while ($row = $raw->fetch())
				{
					$count++;

					if ($count > 1)
					{
						NoRelationPermissionDiskTable::delete($row['ID']);
					}
					else
					{
						$updateRaw = NoRelationPermissionDiskTable::update($row['ID'], [
							'ACTIVE_TO' => DateTime::createFromTimestamp(time() + self::ACCESS_TIME)
						]);

						if ($updateRaw->isSuccess())
						{
							$result = true;
						}
					}
				}

				if ($count === 0)
				{
					$addRaw = NoRelationPermissionDiskTable::add([
						'CHAT_ID' => $chatId,
						'USER_ID' => $userId,
						'ACTIVE_TO' => DateTime::createFromTimestamp(time() + self::ACCESS_TIME)
					]);

					if ($addRaw->isSuccess())
					{
						$result = true;
					}
				}
			}
		}

		return $result;
	}

	public static function delete($chatId, $userId, $permissionDisk = true)
	{
		$result =  false;

		if ($permissionDisk)
		{
			$rowRelation = RelationTable::getRow([
				'select' => ['ID'],
				'filter' => [
					'=CHAT_ID' => $chatId,
					'=USER_ID' => $userId
				],
			]);
			if (empty($rowRelation))
			{
				if (\CIMDisk::ChangeFolderMembers($chatId, $userId, false))
				{
					$result = true;
				}
			}
		}

		$raw = NoRelationPermissionDiskTable::getList([
			'select' => ['ID'],
			'filter' => ['=CHAT_ID' => $chatId, '=USER_ID' => $userId],
		]);

		while ($row = $raw->fetch())
		{
			if (NoRelationPermissionDiskTable::delete($row['ID'])->isSuccess())
			{
				$result = true;
			}
		}

		return $result;
	}

	public static function cleaningAgent(): string
	{
		if (!\Bitrix\Main\ModuleManager::isModuleInstalled('disk'))
		{
			return __METHOD__.'();';
		}

		$connection = \Bitrix\Main\Application::getInstance()->getConnection();

		$relationTbl = RelationTable::getTableName();
		$noRelationPermTbl = NoRelationPermissionDiskTable::getTableName();

		$connection->queryExecute("
			DELETE 
			FROM 
				{$noRelationPermTbl}
			WHERE (CHAT_ID, USER_ID) in (
				select CHAT_ID, USER_ID FROM {$relationTbl}
			)
		");

		$result = NoRelationPermissionDiskTable::getList([
			'select' => ['CHAT_ID', 'USER_ID'],
			'filter' => [
				'<=ACTIVE_TO' => DateTime::createFromTimestamp(time())
			]
		]);
		$pseudoRelation = [];
		while ($row = $result->fetch())
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