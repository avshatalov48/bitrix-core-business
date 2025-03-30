<?php

declare(strict_types=1);

namespace Bitrix\Rest\Dto\APAuth;

class CreatePermissionDto
{
	public function __construct(
		private readonly string $permissionCode,
		private readonly int $passwordId,
	)
	{
	}

	public function getPermissionCode(): string
	{
		return $this->permissionCode;
	}

	public function getPasswordId(): int
	{
		return $this->passwordId;
	}
}
