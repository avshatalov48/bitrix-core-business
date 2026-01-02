<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage tasks
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Main\UI\AccessRights;

use Bitrix\Main\Access\AccessCode;
use Bitrix\Main\Loader;
use Bitrix\Main\UI\AccessRights\Exception\UnknownEntityTypeException;

class DataProvider
{
	public function getEntity(string $type, int $id): Entity\AccessRightEntityInterface
	{
		$entityClass = $this->getEntityClassByType($type);
		if (!$entityClass)
		{
			throw new UnknownEntityTypeException();
		}

		$entity = new $entityClass($id);

		return $entity;
	}

	private function getEntityClassByType(string $type): ?string
	{
		return match ($type) {
			AccessCode::TYPE_OTHER => Entity\Other::class,
			AccessCode::TYPE_USER => Entity\User::class,
			AccessCode::TYPE_SOCNETGROUP
				=> Loader::includeModule('socialnetwork') ? Entity\SocnetGroup::class : null
			,
			AccessCode::TYPE_GROUP => Entity\Group::class,
			AccessCode::TYPE_DEPARTMENT => Entity\Department::class,
			AccessCode::TYPE_ACCESS_DIRECTOR => Entity\AccessDirector::class,
			AccessCode::TYPE_ACCESS_DEPUTY => Entity\AccessDeputy::class,
			AccessCode::TYPE_ACCESS_EMPLOYEE => Entity\UserAll::class,
			AccessCode::TYPE_STRUCTURE_TEAM => Entity\StructureTeam::class,
			AccessCode::TYPE_ACCESS_TEAM_DIRECTOR => Entity\AccessTeamDirector::class,
			AccessCode::TYPE_ACCESS_TEAM_DEPUTY => Entity\AccessTeamDeputy::class,
			AccessCode::TYPE_ACCESS_TEAM_EMPLOYEE => Entity\AccessTeamEmployee::class,
			AccessCode::TYPE_STRUCTURE_DEPARTMENT => Entity\StructureDepartment::class,
			default => null,
		};
	}
}
