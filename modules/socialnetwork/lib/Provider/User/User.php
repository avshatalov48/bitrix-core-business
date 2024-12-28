<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Provider\User;

use Bitrix\Main\Type\Contract\Arrayable;

class User implements Arrayable
{
	public function __construct(
		public readonly int $id,
		public readonly string $firstName,
		public readonly string $lastName,
		public readonly string $fullName,
	)
	{

	}

	public function toArray(): array
	{
		return [
			'ID' => $this->id,
			'FIRST_NAME' => $this->firstName,
			'LAST_NAME' => $this->lastName,
			'FULL_NAME' => $this->fullName,
		];
	}
}