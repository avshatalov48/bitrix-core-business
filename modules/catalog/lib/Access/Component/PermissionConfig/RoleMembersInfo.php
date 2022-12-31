<?php

namespace Bitrix\Catalog\Access\Component\PermissionConfig;

use Bitrix\Catalog\Access\Role\RoleRelationTable;
use Bitrix\Main\Access\AccessCode;
use Bitrix\Main\UI\AccessRights\DataProvider;
use Bitrix\Main\UI\AccessRights\Entity\User;

/**
 * An object for working with information about role members.
 */
class RoleMembersInfo
{
	/**
	 * Members meta data for all roles.
	 *
	 * @return array in format `[roleId => [accessCode => metaData]]`
	 */
	public function getMemberInfos(): array
	{
		$result = [];

		$rows = RoleRelationTable::getList([
			'select' => [
				'ROLE_ID',
				'RELATION',
			],
		]);
		foreach ($rows as $row)
		{
			$roleId = $row['ROLE_ID'];
			$accessCode = $row['RELATION'];

			$result[$roleId][$accessCode] = true;
		}

		$result = $this->fillMembersInfo($result);

		return $result;
	}

	/**
	 * Fill members meta data.
	 *
	 * @param array $rolesAccessCodes
	 *
	 * @return array
	 */
	private function fillMembersInfo(array $rolesAccessCodes): array
	{
		$this->preloadProviderUserModels($rolesAccessCodes);

		$provider = new DataProvider();
		foreach ($rolesAccessCodes as $roleId => $accessCodes)
		{
			foreach ($accessCodes as $accessCode => $value)
			{
				$accessCodeObject = new AccessCode($accessCode);
				$entity = $provider->getEntity(
					$accessCodeObject->getEntityType(),
					$accessCodeObject->getEntityId()
				);

				$rolesAccessCodes[$roleId][$accessCode] = $entity->getMetaData();
			}
		}

		return $rolesAccessCodes;
	}

	/**
	 * Preload user entities.
	 *
	 * @param array $rolesAccessCodes
	 *
	 * @return void
	 */
	private function preloadProviderUserModels(array $rolesAccessCodes): void
	{
		$userIds = [];

		foreach ($rolesAccessCodes as $accessCodes)
		{
			foreach ($accessCodes as $accessCode => $value)
			{
				$accessCodeObject = new AccessCode($accessCode);
				if ($accessCodeObject->getEntityType() === AccessCode::TYPE_USER)
				{
					$userIds[] = $accessCodeObject->getEntityId();
				}
			}
		}

		if ($userIds)
		{
			User::preLoadModels([
				'=ID' => $userIds,
			]);
		}
	}
}
