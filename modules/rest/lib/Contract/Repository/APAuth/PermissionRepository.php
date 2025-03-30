<?php

declare(strict_types=1);

namespace Bitrix\Rest\Contract\Repository\APAuth;

use Bitrix\Rest\Entity;
use Bitrix\Rest\Repository\Exception\CreationFailedException;

interface PermissionRepository
{
	/**
	 * @throws CreationFailedException
	 */
	public function create(Entity\APAuth\Permission $permission): Entity\APAuth\Permission;
	public function deleteByPasswordId(int $passwordId): bool;
}
