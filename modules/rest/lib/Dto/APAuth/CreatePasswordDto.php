<?php

declare(strict_types=1);

namespace Bitrix\Rest\Dto\APAuth;

use Bitrix\Rest;

class CreatePasswordDto
{
	public function __construct(
		private readonly int $userId,
		private readonly Rest\Enum\APAuth\PasswordType $type,
		private readonly string $title,
		private readonly string $comment,
		private readonly array $permissions = [],
	) {}

	public function getUserId(): int
	{
		return $this->userId;
	}

	public function getType(): Rest\Enum\APAuth\PasswordType
	{
		return $this->type;
	}

	public function getTitle(): string
	{
		return $this->title;
	}

	public function getComment(): string
	{
		return $this->comment;
	}

	/**
	 * @return array<string>
	 */
	public function getPermissions(): array
	{
		return $this->permissions;
	}
}
