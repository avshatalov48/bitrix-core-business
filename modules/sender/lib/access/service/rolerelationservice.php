<?php
namespace Bitrix\Sender\Access\Service;

use Bitrix\Main\Access\Exception\RoleRelationSaveException;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Sender\Access\Role\RoleRelationTable;
use Bitrix\Sender\Access\Role\RoleUtil;

class RoleRelationService implements RoleRelationServiceInterface
{
	/**
	 * @inheritDoc
	 * @throws RoleRelationSaveException
	 */
	public function saveRoleRelation(array $settings): void
	{
		foreach ($settings as $setting)
		{
			$roleId = $setting['id'];
			$accessCodes = $setting['accessCodes'] ?? [];

			if($roleId === false)
			{
				continue;
			}

			(new RoleUtil($roleId))->updateRoleRelations($accessCodes);
		}
	}

	/**
	 * @inheritDoc
	 */
	public function getRolesByRelations(array $relations): ?array
	{
		$senderRoleRelations = RoleRelationTable::query()
				->setSelect(['ROLE_ID'])
				->whereIn('RELATION', $relations)
				->exec()
				->fetchAll();

		$roles = [];
		foreach($senderRoleRelations as $relation)
		{
			$roles[] = $relation["ROLE_ID"];
		}

		return $roles;
	}

	/**
	 * @inheritDoc
	 * @throws SqlQueryException
	 */
	public function deleteRoleRelations(int $roleId): void
	{
		if (!RoleRelationTable::deleteList(["=ROLE_ID" => $roleId]))
		{
			throw new SqlQueryException();
		}
	}

	/**
	 * @inheritDoc
	 */
	public function getRelationList(array $parameters = []): ?array
	{
		return RoleRelationTable::getList($parameters)->fetchAll();
	}
}