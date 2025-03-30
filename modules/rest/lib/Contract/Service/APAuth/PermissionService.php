<?php

declare(strict_types=1);

namespace Bitrix\Rest\Contract\Service\APAuth;

use Bitrix\Rest\Dto;
use Bitrix\Rest\Entity\APAuth\Permission;

interface PermissionService
{
	public function create(Dto\APAuth\CreatePermissionDto $createPermissionDto): ?Permission;
	public function deleteByPasswordId(int $passwordId): bool;
}
