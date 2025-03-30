<?php

declare(strict_types=1);

namespace Bitrix\Rest\Entity\APAuth;

class Permission
{
	public function __construct(
		private int $id,
		private readonly string $permissionCode,
		private readonly int $passwordId
	)
	{
	}

	public function getId(): int
	{
		return $this->id;
	}

	public function setId(int $id): void
	{
		$this->id = $id;
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
