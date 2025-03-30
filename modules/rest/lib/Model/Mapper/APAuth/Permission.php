<?php

declare(strict_types=1);

namespace Bitrix\Rest\Model\Mapper\APAuth;

use Bitrix\Rest;
use Bitrix\Rest\Entity;

class Permission
{
	public function mapModelToEntity(Rest\APAuth\EO_Permission $model): Entity\APAuth\Permission
	{
		return new Entity\APAuth\Permission(
			id: $model->getId(),
			permissionCode: $model->getPerm(),
			passwordId: $model->getPasswordId(),
		);
	}

	public function mapEntityToModel(Entity\APAuth\Permission $permission): Rest\APAuth\EO_Permission
	{
		$model = new Rest\APAuth\EO_Permission();
		$model->setPerm($permission->getPermissionCode());
		$model->setPasswordId($permission->getPasswordId());

		return $model;
	}
}
