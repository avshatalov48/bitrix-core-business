<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage tasks
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Main\UI\AccessRights;

use Bitrix\Main\Access\AccessCode;
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
		switch ($type)
		{
			case (AccessCode::TYPE_OTHER):
				return Entity\Other::class;
			case (AccessCode::TYPE_USER):
				return Entity\User::class;
			case (AccessCode::TYPE_SOCNETGROUP):
				return \Bitrix\Main\Loader::includeModule('socialnetwork') ? Entity\SocnetGroup::class : null;
			case (AccessCode::TYPE_GROUP):
				return Entity\Group::class;
			case (AccessCode::TYPE_DEPARTMENT):
				return Entity\Department::class;
			case (AccessCode::TYPE_ACCESS_DIRECTOR):
				return Entity\AccessDirector::class;
			case (AccessCode::TYPE_ACCESS_EMPLOYEE):
				return Entity\UserAll::class;
			default:
				return null;
		}
	}

}