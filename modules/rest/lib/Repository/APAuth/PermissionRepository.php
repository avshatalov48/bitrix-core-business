<?php

declare(strict_types=1);

namespace Bitrix\Rest\Repository\APAuth;

use Bitrix\Rest\Entity;
use Bitrix\Rest;
use Bitrix\Rest\Repository;
use Bitrix\Rest\Model\Mapper;
use Bitrix\Rest\Contract;

class PermissionRepository implements Contract\Repository\APAuth\PermissionRepository
{
	public function create(Entity\APAuth\Permission $permission): Entity\APAuth\Permission
	{
		$result = $this->mapEntityToModel($permission)->save();

		if (!$result->isSuccess())
		{
			throw new Repository\Exception\CreationFailedException('Failed to create permission');
		}

		$permission->setId($result->getId());

		return $permission;
	}

	public function deleteByPasswordId(int $passwordId): bool
	{
		try
		{
			Rest\APAuth\PermissionTable::deleteByPasswordId($passwordId);

			return true;
		}
		catch (\Exception)
		{
			return false;
		}
	}

	private function mapEntityToModel(Entity\APAuth\Permission $permission): Rest\APAuth\EO_Permission
	{
		return (new Mapper\APAuth\Permission())->mapEntityToModel($permission);
	}

	private function mapModelToEntity(Rest\APAuth\EO_Permission $model): Entity\APAuth\Permission
	{
		return (new Mapper\APAuth\Permission())->mapModelToEntity($model);
	}
}
