<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Property;

use Bitrix\Main\Type\Contract\Arrayable;

class Feature implements Arrayable
{
	public function __construct(
		public readonly int $id,
		public readonly string $feature,
		public readonly bool $isActive
	)
	{
	}

	public function toArray(): array
	{
		return [$this->feature => $this->isActive];
	}
}