<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control\Command\ValueObject;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Type\Contract\Arrayable;
use Bitrix\Socialnetwork\ValueObjectInterface;

class FeaturesPermissions implements ValueObjectInterface, CreateWithDefaultValueInterface, CreateObjectInterface
{
	protected array $permission = [];

	public static function create(mixed $data): static
	{
		$value = new static();

		$value->permission = $data;

		return $value;
	}

	public static function createWithDefaultValue(): static
	{
		return new static();
	}

	public function getValue(): array
	{
		return $this->permission;
	}
}