<?php

declare(strict_types=1);

namespace Bitrix\Rest\Service\APAuth;

use Bitrix\Rest\Dto;
use Bitrix\Rest\Entity\APAuth\Permission;
use Bitrix\Rest\Contract;
use Bitrix\Rest\Repository\APAuth\PermissionRepository;
use Bitrix\Rest\Repository\Exception\CreationFailedException;

class PermissionService implements Contract\Service\APAuth\PermissionService
{
	public function __construct(
		private ?Contract\Repository\APAuth\PermissionRepository $permissionRepository = null
	)
	{
		$this->permissionRepository ??= new PermissionRepository();
	}

	public function create(Dto\APAuth\CreatePermissionDto $createPermissionDto): ?Permission
	{
		$permission = new Permission(
			id: 0,
			permissionCode: $createPermissionDto->getPermissionCode(),
			passwordId: $createPermissionDto->getPasswordId()
		);

		try
		{
			return $this->permissionRepository->create($permission);
		}
		catch (CreationFailedException)
		{
			return null;
		}
	}

	public function deleteByPasswordId(int $passwordId): bool
	{
		return $this->permissionRepository->deleteByPasswordId($passwordId);
	}
}
