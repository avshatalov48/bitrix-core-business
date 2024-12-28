<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Property;

use Bitrix\Main\Type\Contract\Arrayable;

class Permission implements Arrayable
{
	public function __construct(
		private readonly string $feature,
		private readonly array $permissions
	)
	{
	}

	public function toArray(): array
	{
		return [$this->feature => $this->permissions];
	}

	public function isEmpty(): bool
	{
		return empty($this->permissions);
	}
}
