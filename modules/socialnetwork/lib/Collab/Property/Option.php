<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Property;

use Bitrix\Main\Type\Contract\Arrayable;

class Option implements Arrayable
{
	public function __construct(
		public readonly string $name,
		public readonly mixed $value
	)
	{
	}

	public function toArray(): array
	{
		return [
			$this->name => $this->value,
		];
	}
}