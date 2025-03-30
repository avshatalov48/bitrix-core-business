<?php

declare(strict_types=1);

namespace Bitrix\Rest\Entity\APAuth;

use Bitrix\Main\Type\DateTime;
use Bitrix\Rest\Enum;

class Password
{
	public function __construct(
		private int $id,
		private readonly string $passwordString,
		private readonly int $userId,
		private readonly Enum\APAuth\PasswordType $type,
		private readonly string $title = '',
		private readonly string $comment = '',
		private readonly DateTime $createdAt,
	)
	{}

	public function getId(): int
	{
		return $this->id;
	}

	public function setId(int $id): void
	{
		$this->id = $id;
	}

	public function getPasswordString(): string
	{
		return $this->passwordString;
	}

	public function getUserId(): int
	{
		return $this->userId;
	}

	public function getType(): Enum\APAuth\PasswordType
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

	public function getCreatedAt(): DateTime
	{
		return $this->createdAt;
	}
}
